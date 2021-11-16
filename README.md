# astrada-web

# Composer requires
* docker-compose exec php composer require symfony/webpack-encore-bundle
* docker-compose exec php composer require nucleos/user-bundle:1.10.x-dev
* docker-compose exec php composer require nucleos/profile-bundle:1.5.x-dev
* docker-compose exec php composer require gedmo/doctrine-extensions
* docker-compose exec php composer require lexik/jwt-authentication-bundle
* docker exec -it 7969b24ddffa composer require odolbeau/phone-number-bundle
* docker exec -it 122ebb1c168e  composer require phpstan/phpstan-webmozart-assert
* docker exec -it 60bd20cccf63 composer require api-platform/core
* docker exec -it 60bd20cccf63 composer require guzzlehttp/guzzle
* docker exec -it 60bd20cccf63 composer require php-http/guzzle7-adapter
* docker exec -it 60bd20cccf63 composer require geocoder-php/chain-provider
* docker exec -it 60bd20cccf63 composer require geocoder-php/nominatim-provider
* docker exec -it 60bd20cccf63 composer require geocoder-php/open-cage-provider
* docker exec -it 60bd20cccf63 composer require geocoder-php/photon-provider
* docker exec -it 60bd20cccf63 composer require demollc/sputnik-provider
* docker exec -it 60bd20cccf63 composer require league/geotools
* docker exec -it 60bd20cccf63 composer require symfony/messenger
* docker exec -it 3898de277028 composer require jsor/doctrine-postgis
* docker-compose exec php composer require --dev phpunit/phpunit symfony/test-pack
* docker-compose exec php composer require myclabs/php-enum
* docker-compose exec php composer require nesbot/carbon
* docker-compose exec php composer require vich/uploader-bundle
* docker exec -it 7969b24ddffa composer require sylius/resource-bundle
* docker exec -it 60bd20cccf63 composer require sylius/customer-bundle:1.9.x-dev
* docker exec -it 60bd20cccf63 composer require sylius/currency-bundle
* docker-compose exec php composer require sylius/taxation-bundle
* docker-compose exec php composer require sylius/taxonomy-bundle
* docker-compose exec php composer require sylius/attribute-bundle
* docker-compose exec php composer require sylius/review-bundle
* docker-compose exec php composer require craue/config-bundle AND!!! create table: docker-compose exec php php bin/console doctrine:migrations:diff
* docker-compose exec php composer require league/flysystem-aws-s3-v3
* docker-compose exec php composer require liip/imagine-bundle
* docker-compose exec php composer require oneup/uploader-bundle
* docker-compose exec php composer require oneup/flysystem-bundle
* docker-compose exec php composer require snc/redis-bundle --- !!! composer.json нет
* docker-compose exec php  composer require twig/intl-extra
* docker-compose exec php  composer require knplabs/knp-paginator-bundle
* docker-compose exec php   composer require laravolt/avatar
* docker-compose exec php   composer require cocur/slugify
* docker-compose exec php   composer require sylius/locale-bundle
* docker-compose exec php   composer require sylius/product-bundle
* docker-compose exec php   composer require hashids/hashids
* docker-compose exec php   composer require friendsofsymfony/jsrouting-bundle
* docker-compose exec php   composer require jaybizzle/crawler-detect
* docker-compose exec php   composer require php-http/httplug-bundle
* docker-compose exec php   composer require --dev php-http/guzzle7-adapter
* docker-compose exec php   composer require hwi/oauth-bundle
* docker-compose exec php   composer require ramsey/uuid-doctrine
* docker-compose exec php  composer require centrifugal/phpcent
* docker-compose exec php  composer require knplabs/knp-time-bundle
* docker-compose exec php  composer require spatie/opening-hours
* docker-compose exec php  composer require csa/guzzle-bundle
* docker-compose exec php  composer require emcconville/google-map-polyline-encoding-tool
* docker-compose exec php  composer require nelmio/alice
* docker-compose exec php  composer require hautelook/alice-bundle
* docker-compose exec php  composer require spatie/guzzle-rate-limiter-middleware
* docker-compose exec php  composer require geocoder-php/yandex-provider
* docker-compose exec php  composer require doctrine/data-fixtures
* docker-compose exec php  composer require symfony/lock
* docker-compose exec php  composer require twig/string-extra
* docker-compose exec php  composer require twig/cache-extra
* docker-compose exec php  composer require sonata-project/seo-bundle
* docker-compose exec php  composer require martin-georgiev/postgresql-for-doctrine
* docker-compose exec php  composer require tetranz/select2entity-bundle
* docker-compose exec php  composer require pitch/liform
* docker-compose exec php  composer require notfloran/mjml-bundle
* docker-compose exec php  composer require karser/karser-recaptcha3-bundle
* docker-compose exec php  composer require beberlei/doctrineextensions
* docker-compose exec php  composer require nyholm/psr7


# Geocoder and tricks maps
* https://habr.com/ru/hub/map_api/
* https://habr.com/ru/post/462011/
* https://nominatim.org/release-docs/develop/admin/Installation/ -own geocoder service
* https://api.visicom.ua/uk/price - visicom 

# Geocoder
https://github.com/geocoder-php/Geocoder
## Providers
* https://packagist.org/providers/geocoder-php/provider-implementation
## Local Provider
* https://packagist.org/providers/geocoder-php/provider-implementation
* demollc/sputnik-provider

#OSRM
* https://habr.com/ru/post/224731/
* http://download.geofabrik.de/
* http://download.geofabrik.de/europe.html

# Migrations
* docker-compose exec php php bin/console doctrine:migrations:diff
* docker-compose exec php php bin/console doctrine:migrations:migrate
* docker-compose exec php php bin/console doctrine:schema:update --force

* docker exec -it 3b3a37eaa955 psql -Umain -dastrada

# Commands
* docker-compose exec php php bin/console astrada:setup
* docker-compose exec php php bin/console astrada:demo:init
* docker-compose exec php php bin/console astrada:root-categories:load

# Check container services
* docker-compose exec php php bin/console debug:container sylius.factory.listing_review

#Messanger 
docker-compose exec php php bin/console messenger:consume async -vv

# Redis 
docker exec -it 98864dc72e67 redis-cli -h localhost -p 6379

#Cache
* https://symfony.com/doc/current/components/cache/cache_pools.html
* deletes the "cache_key" item from the "cache.app" pool
* php bin/console cache:pool:delete cache.app cache_key
* docker-compose exec php bin/console cache:clear

* docker-compose exec php php bin/console cache:pool:clear cache.app
* php bin/console cache:pool:clear cache.validation cache.app
* Если debug tools не работает надо вызвать эту команду чтоб увидеть ошибки которые мешают: 
* docker-compose exec php php bin/console debug:event

# Tests
# https://symfony.com/doc/current/testing.html
* docker-compose exec php php ./vendor/bin/phpunit
* docker-compose exec php php ./vendor/bin/phpunit tests/App/Entity/AddressTest.php
* docker-compose exec php php ./vendor/bin/phpunit tests/App/Entity

# CHOWN Access files
* docker-compose exec php chown -R www-data public/media/cache
# CHOWN JWT
* docker-compose exec php chown -R www-data /var/www/html/var/jwt

# Validators
* docker-compose exec php php bin/console debug:validator 'App\Entity\Address'

# Translations
* docker-compose exec php php bin/console translation:update --help
* docker-compose exec php php bin/console translation:update --force --output-format=yaml --sort=asc ua --domain=messages

# Sylius Entity Translations
* https://docs.sylius.com/en/1.2/book/architecture/translations.html 

# Sylius
## Overriding Models
* https://sylius-older.readthedocs.io/en/stable/bundles/general/overriding_models.html
* docker-compose exec php php bin/console doctrine:schema:update --force


# Api platform swagger
* http://rxz.test/api

159.69.2.71 rxz.test

# Auth Basic
test:akva2021

# Wayforpay
* https://github.com/nemishkor/WayforpayBundle
* https://github.com/itworks-soft/payum-wayforpay
* sylius doc: https://github.com/Payum/Payum/blob/master/docs/symfony/get-it-started.md
* https://docs.sylius.com/en/latest/cookbook/payments/paypal.html
* How to integrate a Payment Gateway as a Plugin?
  https://docs.sylius.com/en/latest/cookbook/payments/custom-payment-gateway.html
* https://bitbag.io/blog/advanced-sylius-payment-service-provider-integration-based-on-syliusmollieplugin-example

  
# The library utilises two PostgreSQL databases to find the best route between two points. One database is a complete set of information from OSM and is used e.g. to find streets which with trams. 
# The second one is pgRouting database which represents a city as a weighted graph and calculates the route.
* https://github.com/maciejslawik/route-planner

# Maria is a simple and flexible business rule engine that you
# can integrate easily into your Symfony applications through Bundle mechanism. 
* https://github.com/ibrahimgunduz34/maria-bundle

# Superdesk Publisher
* https://github.com/superdesk/web-publisher

# Errors issuses
* sylius_taxon SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'tree_left' cannot be null
нужно подключить: gedmo.listener.tree и gedmo.listener.sortable

# Fullcalendar react exaple
https://github.com/artgris/Calendar

# Images
https://pixabay.com/images/search/header/?pagi=2&
* header.jpg: 
https://www.shutterstock.com/ru/image-illustration/red-gps-location-marker-3d-on-1903280479
https://www.shutterstock.com/ru/image-illustration/city-map-pin-pointers-3d-rendering-1566197278
https://www.shutterstock.com/ru/image-photo/location-marking-pin-on-map-routes-1756593041
https://www.shutterstock.com/ru/image-photo/routes-red-pins-on-city-map-1682668705
https://www.shutterstock.com/ru/image-vector/gps-navigator-vector-1006116205
