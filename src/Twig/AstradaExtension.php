<?php


namespace App\Twig;


use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Address;
use App\Entity\Booking;
use App\Entity\Model\UserAddressRequest;
use App\Twig\CacheExtension\KeyGenerator;
use Carbon\Carbon;
use Doctrine\Common\Collections\Collection;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class AstradaExtension extends AbstractExtension
{
    private $session;
    private $serializer;
    private $iriConverter;
    private $secret;
    protected $timeUnit;
    protected $timeUnitIsDay;

    public function __construct(Session $session, SerializerInterface $serializer, IriConverterInterface $iriConverter,
                                string $secret,array $parameters)
    {
        $this->session = $session;
        $this->serializer = $serializer;
        $this->iriConverter = $iriConverter;
        $this->secret = $secret;
        $this->timeUnit = $parameters["astrada_time_unit"];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getGlobals()
    {
        $listing = new ReflectionClass("App\Entity\Listing");
        $listingConstants = $listing->getConstants();

        $listingAvailability = new ReflectionClass("App\Entity\ListingAvailability");
        $listingAvailabilityConstants = $listingAvailability->getConstants();

        $listingImage = new ReflectionClass("App\Entity\ListingImage");
        $listingImageConstants = $listingImage->getConstants();


        $booking = new ReflectionClass("App\Entity\Booking");
        $bookingConstants = $booking->getConstants();


        //CSS class by status
        $bookingStatusClass = array(
            Booking::STATUS_DRAFT => 'btn-yellow',
            Booking::STATUS_NEW => 'btn-yellow',
//            Booking::STATUS_ACCEPTED => 'btn-polo-blue',
            Booking::STATUS_EXPIRED => 'btn-nomad',
            Booking::STATUS_CANCELED_ASKER => 'btn-salmon',
//            Booking::STATUS_CANCELED_OFFERER => 'btn-salmon',
        );

        $bookingBankWire = new ReflectionClass("Cocorico\CoreBundle\Entity\BookingBankWire");
        $bookingBankWireConstants = $bookingBankWire->getConstants();

        $bookingPayinRefund = new ReflectionClass("Cocorico\CoreBundle\Entity\BookingPayinRefund");
        $bookingPayinRefundConstants = $bookingPayinRefund->getConstants();

        return array(
            'ListingConstants' => $listingConstants,
            'ListingAvailabilityConstants' => $listingAvailabilityConstants,
            'ListingImageConstants' => $listingImageConstants,

            'BookingConstants' => $bookingConstants,
            'BookingBankWireConstants' => $bookingBankWireConstants,
            'BookingPayinRefundConstants' => $bookingPayinRefundConstants,
            'bookingStatusClass' => $bookingStatusClass,
            'timeUnit' => $this->timeUnit,
            'timeUnitIsDay' => $this->timeUnitIsDay,
        );
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('price_format', array(PriceFormatResolver::class, 'priceFormat')),
            new TwigFilter('local_business_type', array(LocalBusinessRuntime::class, 'type')),
            new TwigFilter('sylius_resolve_variant', array(SyliusVariantResolver::class, 'resolveVariant')),
            new TwigFilter('cache_key', array(KeyGenerator::class, 'generateKey')),
            new TwigFilter('get_iri_from_item', array($this, 'getIriFromItem')),
            new TwigFilter('astrada_star_rating', array($this, 'starRatingFilter')),
            new TwigFilter('astrada_normalize', array($this, 'normalize')),
            new TwigFilter('parse_expression', array(ExpressionLanguageRuntime::class, 'parseExpression')),
            new TwigFilter('date_calendar', array($this, 'dateCalendar'), ['needs_context' => true]),
        );
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('astrada_user_location', array($this, 'userLocationSession')),
            new TwigFunction('astrada_setting', array(SettingResolver::class, 'resolveSetting')),
            new TwigFunction('astrada_maintenance', array(MaintenanceResolver::class, 'isEnabled')),
            new TwigFunction('astrada_logo', array(AppearanceRuntime::class, 'logo')),
            new TwigFunction('astrada_has_about_us', array(AppearanceRuntime::class, 'hasAboutUs')),
            new TwigFunction('astrada_is_timeUnitIsDay', array($this, 'isTimeUnitIsDay')),
            new TwigFunction('astrada_company_logo', array(AppearanceRuntime::class, 'companyLogo')),
            new TwigFunction('astrada_asset', array(AssetsRuntime::class, 'asset')),
            new TwigFunction('astrada_asset_base64', array(AssetsRuntime::class, 'assetBase64')),
            new TwigFunction('astrada_businesses_suggestions', array(LocalBusinessRuntime::class, 'businessesSuggestions')),
            new TwigFunction('astrada_bounding_rect', array(SettingResolver::class, 'getBoundingRect')),
            new TwigFunction('local_business_path', array(UrlGeneratorRuntime::class, 'localBusinessPath')),
            new TwigFunction('listing_path', array(UrlGeneratorRuntime::class, 'listingPath')),
            new TwigFunction('astrada_businesses_suggestions', array(LocalBusinessRuntime::class, 'businessesSuggestions')),
            new TwigFunction('astrada_businesses_suggestions', array(LocalBusinessRuntime::class, 'businessesSuggestions')),
        );
    }

    public function getTests()
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceof'])
        ];
    }

    public function getIriFromItem($item)
    {
        return $this->iriConverter->getIriFromItem($item);
    }

    public function dateCalendar($context, $date)
    {
        $locale = $context['app']->getRequest()->getLocale();

        $carbon = Carbon::parse($date);

        return strtolower($carbon->locale($locale)->toDateString());
    }

    /**
     * startRatingFilter outputs the readonly starts
     *
     * @param \Twig_Environment $env
     * @param                   $rating
     *
     * @return string
     * @inheritdoc
     */
    public function starRatingFilter(\Twig_Environment $env, $rating)
    {
        return $env->render('common/star_rating.html.twig', array('rating' => $rating));
    }

    public function isTimeUnitIsDay()
    {
        return $this->timeUnitIsDay;
    }

    public function normalize($object, $resourceClass = Address::class, $groups = [], $format = 'jsonld')
    {
        if ($resourceClass === Address::class && empty($groups)) {
            $groups = ['address'];
        }

        $context = [];

        if (!empty($groups)) {
            $context['groups'] = $groups;
        }

        if ('jsonld' === $format) {
            $context = array_merge($context, [
                'resource_class' => $resourceClass,
                'operation_type' => 'item',
                'item_operation_name' => 'get',
            ]);
        }

        if ($object instanceof Collection) {

            $collection = [];
            foreach ($object as $item) {
                $collection[] =
                    $this->serializer->normalize($item, $format, $context);
            }

            return $collection;
        }

        return $this->serializer->normalize($object, $format, $context);
    }

    public function userLocationSession()
    {

        /** @var UserAddressRequest $userAddressRequest */
        $userAddressRequest = $this->session->has('user_address_request') ?
            $this->session->get('user_address_request') :
            null;
        if($userAddressRequest){
            $geo = $userAddressRequest->getAddress()->getGeo();
            return ['latitude'=>$geo->getLatitude(),'longitude'=>$geo->getLongitude()];
        }else{
            return [];
        }

    }
}