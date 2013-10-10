<?php

namespace Maxmode\GeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Application;
use Symfony\Bundle\FrameworkBundle\Console\Application as FrameworkApplication;

class MaxmodeGeneratorBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        /** @var $application FrameworkApplication */
        $container = $application->getKernel()->getContainer();
        $application->add($container->get('maxmode_generator.sonata_admin.generator'));
    }
}
