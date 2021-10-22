<?php


namespace App\Service;

use App\Utils\Settings;
use Craue\ConfigBundle\Util\Config as CraueConfig;
use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface as CraueCache;
use Doctrine\Persistence\ManagerRegistry;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class SettingsManager
{
    private $configEntityName;
    private $phoneNumberUtil;
    private $country;
    private $doctrine;

    private $mandatorySettings = [
        'brand_name',
        'administrator_email',
        'google_api_key',
        'latlng',
        'currency_code',
    ];

    private $secretSettings = [
        'google_api_key',
    ];

    private static $boolean = [
        'sms_enabled',
        'subject_to_vat',
    ];

    private $cache = [];

    public function __construct(
        CraueConfig $craueConfig,
        CraueCache $craueCache,
        string $configEntityName,
        ManagerRegistry $doctrine,
        PhoneNumberUtil $phoneNumberUtil,
        string $country
    )
    {
        $this->craueConfig = $craueConfig;
        $this->craueCache = $craueCache;
        $this->configEntityName = $configEntityName;
        $this->doctrine = $doctrine;
        $this->phoneNumberUtil = $phoneNumberUtil;
        $this->country = $country;
    }

    public function isSecret($name)
    {
        return in_array($name, $this->secretSettings);
    }

    public function get($name)
    {
        switch ($name) {
            case 'timezone':
                return ini_get('date.timezone');
        }

        if (isset($this->cache[$name])) {

            return $this->cache[$name];
        }

        try {

            $value = $this->craueConfig->get($name);

            switch ($name) {
                case 'phone_number':
                    try {
                        $value = $this->phoneNumberUtil->parse($value, strtoupper($this->country));
                    } catch (NumberParseException $e) {}
                    break;
            }

            if (in_array($name, self::$boolean)) {
                $value = (bool) $value;
            }

            $this->cache[$name] = $value;

            return $value;

        } catch (\RuntimeException $e) {}
    }

    public function getBoolean($name)
    {
        return filter_var($this->get($name), FILTER_VALIDATE_BOOLEAN);
    }

    public function canSendSms()
    {
        if (!$this->get('sms_enabled')) {

            return false;
        }

        $smsGateway = $this->get('sms_gateway');

        if (!$smsGateway || !in_array($smsGateway, ['mailjet', 'twilio'])) {

            return false;
        }

        $smsGatewayConfig = $this->get('sms_gateway_config');

        if (empty($smsGatewayConfig)) {

            return false;
        }

        $smsGatewayConfig = json_decode($smsGatewayConfig, true);

        if (empty($smsGatewayConfig)) {

            return false;
        }

        switch ($smsGateway) {
            case 'mailjet':
                return in_array($this->country, ['be', 'es', 'de', 'fr'])
                    && isset($smsGatewayConfig['api_token']);
            case 'twilio':
                return isset(
                    $smsGatewayConfig['sid'],
                    $smsGatewayConfig['auth_token'],
                    $smsGatewayConfig['from']
                );
        }

        return false;
    }

    public function set($name, $value)
    {
        try {

            $this->craueConfig->set($name, $value);

        } catch (\RuntimeException $e) {

            $className = $this->configEntityName;

            $entityManager = $this->doctrine
                ->getManagerForClass($className);

            // Create the setting if it does not exist
            $setting = new $className();
            $setting->setName($name);
            $setting->setValue($value);

            // Avoid flushing changes for all objects
            $entityManager->persist($setting);
            $entityManager->getUnitOfWork()->commit($setting);

            $this->craueConfig->set($name, $value);
        }
    }

    public function flush()
    {
        $this->doctrine->getManagerForClass($this->configEntityName)->flush();
        $this->craueCache->clear();
    }

    public function isFullyConfigured()
    {
        foreach ($this->mandatorySettings as $name) {
            try {
                $value = $this->craueConfig->get($name);
                if (null === $value) {
                    return false;
                }
            } catch (\RuntimeException $e) {
                return false;
            }
        }

        return true;
    }

    public function asEntity()
    {
        $settings = new Settings();

        $keys = array_keys(get_object_vars($settings));

        foreach ($keys as $name) {
            try {
                $value = $this->craueConfig->get($name);

                if (in_array($name, self::$boolean)) {
                    $value = (bool) $value;
                }

                $settings->$name = $value;
            } catch (\RuntimeException $e) {}
        }

        return $settings;
    }
}