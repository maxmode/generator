<?php
namespace Maxmode\GeneratorBundle\Command\Sonata\Admin;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

use Symfony\Component\Filesystem\Filesystem;

use Maxmode\GeneratorBundle\Admin\ClassGenerator as AdminClassGenerator;
use Maxmode\GeneratorBundle\Admin\ServicesGenerator;
use Maxmode\GeneratorBundle\Entity\Select;

/**
 * Class GeneratorCommand
 *
 * @package Maxmode\GeneratorBundle\Command\Sonata\Admin
 */
class GeneratorCommand extends ContainerAwareCommand
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * Set Command parameters
     */
    protected function configure()
    {
        $this->setName('maxmode:generate:sonata-admin')
            ->setDescription('Generator is used to generate Admin class based on entity')
            ->addArgument('entity');
        //todo: write description.
        //todo first argument is entity name If not set - will be asked
    }

    /**
     * Command execution
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityList = $this->getEntitySelect()->getEntityList();
        $defaultEntity = $this->getEntitySelect()->getFirstEntity();
        $question = <<<ASK

Please, enter the entity class name
The class name must be the fully-qualified class name without a leading backslash
(as it is returned by get_class(\$obj)) or an aliased class name.
[$defaultEntity]
>
ASK;
        $entityClass = $input->getArgument('entity');
        if (!$entityClass) {
            //todo: ask and validate?
            $entityClass = $this->getDialog()->ask($output, $question, $defaultEntity, $entityList);
        }
        $success = false;
        while (!$success) {
            try {
                $success = $this->getEntitySelect()->validateClass($entityClass);
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $entityClass = $this->getDialog()->ask($output, $question, $defaultEntity, $entityList);
            }
        }

        $this->getAdminClassGenerator()->setEntityClass($entityClass);
        $this->getServicesGenerator()->setClassGenerator($this->getAdminClassGenerator());

        $this->generateClass($input, $output);

        if ($this->getDialog()->askConfirmation($output, 'Do you want to generate view functionality? [yes]', true)) {
            $this->generateList($input, $output);
        }

        if ($this->getDialog()->askConfirmation($output, 'Do you want to generate CRUD functionality? [yes]', true)) {
            $this->generateCRUD($input, $output);
        }
    }

    /**
     * Generate class and services configuration
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function generateClass(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getDialog()->askConfirmation($output, ' Do you want to generate an admin class? [yes]', true)) {
            return;
        }

        $this->getServicesGenerator()->setServicesDefinitionFile(
            $this->getDialog()->ask(
                $output,
                "Specify services.xml file location [{$this->getServicesGenerator()->getServicesDefinitionFile()}]",
                $this->getServicesGenerator()->getServicesDefinitionFile()
            )
        );

        $this->getServicesGenerator()->setGroup(
            $this->getDialog()->ask(
                $output,
                "Specify group id for dashboard [{$this->getServicesGenerator()->getGroup()}]",
                $this->getServicesGenerator()->getGroup()
            )
        );

        if ($this->getFilesystem()->exists($this->getServicesGenerator()->getServicesDefinitionFile())) {
            $this->getServicesGenerator()->setCurrentCode(
                file_get_contents($this->getServicesGenerator()->getServicesDefinitionFile())
            );
        }

        $this->getFilesystem()->dumpFile(
            $this->getServicesGenerator()->getServicesDefinitionFile(),
            $this->getServicesGenerator()->getGeneratedCode()
        );

        $this->getFilesystem()->dumpFile(
            $this->getAdminClassGenerator()->getAdminFileName(),
            $this->getAdminClassGenerator()->getGeneratedCode()
        );

        echo "Class created successfully\n";
    }

    /**
     * Generate list view
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function generateList(InputInterface $input, OutputInterface $output)
    {
        $listFields = array();
        $entityFields = $this->getEntitySelect()->getEntityFields($this->getAdminClassGenerator()->getEntityClass());
        if ($this->getDialog()->askConfirmation($output, "Do you want to see all entity's fields in the List?")) {
            $listFields = $entityFields;
        } else {
            $output->writeln("Now you will be asked about each field of entity.\n Set 'y/n' to add field to the List");
            foreach ($entityFields as $entityField) {
                if ($this->getDialog()->askConfirmation($output, $entityField . ' ')) {
                    $listFields[] = $entityField;
                }
            }
        }
        print_r($listFields);
        //todo: generate list method
        echo "List generated successfully\n";
    }

    /**
     * Generate CRUD
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function generateCRUD(InputInterface $input, OutputInterface $output)
    {
        //todo: generate CRUD method
        echo "CRUD generated successfully\n";
    }

    /**
     * @return DialogHelper
     */
    public function getDialog()
    {
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        return $dialog;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        if (!$this->_filesystem) {
            $this->_filesystem = new Filesystem();
        }

        return $this->_filesystem;
    }

    /**
     * @return AdminClassGenerator
     */
    public function getAdminClassGenerator()
    {
        //todo: make command as a service and inject all dependencies
        return $this->getContainer()->get('maxmode_generator.admin.class_generator');
    }

    /**
     * @return ServicesGenerator
     */
    public function getServicesGenerator()
    {
        return $this->getContainer()->get('maxmode_generator.admin.services_generator');
    }

    /**
     * @return Select
     */
    public function getEntitySelect()
    {
        return $this->getContainer()->get('maxmode_generator.entity.select');
    }
}
