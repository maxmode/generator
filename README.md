== About ==
Bundle supply command line tool to generate CRUD.
It generates code, used by sonata-admin bundle.
The command generates code based on Doctrine entities metadata.

== Installation ==
# Add dependency to composer.json file of your project:
<code>
    "maxmode/generator": "dev-master"
</code>
# Register bundle in AppKernel.php:
<code>
    $bundles = array(
        ...
        new Maxmode\GeneratorBundle\MaxmodeGeneratorBundle(),
    );
</code>

== Usage ==
# Create doctrine entity
# Run comand to generate CRUD for it:
<code>
    php app/console maxmode:generate:sonata-admin
</code>