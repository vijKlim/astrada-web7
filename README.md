# astrada-web


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



# Tests
# https://symfony.com/doc/current/testing.html
* docker-compose exec php php ./vendor/bin/phpunit
* docker-compose exec php php ./vendor/bin/phpunit tests/App/Entity/AddressTest.php
* docker-compose exec php php ./vendor/bin/phpunit tests/App/Entity

# CHOWN Access files
* docker-compose exec php chown -R www-data public/media/cache
* docker-compose exec php chown -R www-data public/images
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

