# Delete old test data
rm -rf Tests/src
cp Tests/app/AppKernel.php.dist Tests/app/AppKernel.php
rm -rf Tests/app/cache
cp Tests/app/config/parameters.yml.dist Tests/app/config/parameters.yml
cp Tests/app/config/routing.yml.dist Tests/app/config/routing.yml
cp Tests/app/phpunit.xml.dist Tests/app/phpunit.xml

# Generate bundle
php Tests/app/console generate:bundle --namespace="TestVendor\TestBundle" --format=annotation --dir=Tests/src -n -q

# Generate entities
php Tests/app/console doctrine:generate:entity --entity="TestVendorTestBundle:Carrot" --format=annotation --fields="length:int color:string(255)" -n -q

# Generate CRUD
php Tests/app/console maxmode:generate:sonata-admin "TestVendor\TestBundle\Entity\Carrot" -n -q
