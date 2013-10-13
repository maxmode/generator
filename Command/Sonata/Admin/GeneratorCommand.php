<?php
namespace Maxmode\GeneratorBundle\Command\Sonata\Admin;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

use Maxmode\GeneratorBundle\Admin\ClassGenerator;
use Maxmode\GeneratorBundle\Admin\ServicesGenerator;
use Maxmode\GeneratorBundle\Entity\Select;

/**
 * Class GeneratorCommand
 *
 * @package Maxmode\GeneratorBundle\Command\Sonata\Admin
 */
class GeneratorCommand extends Command
{
    /**
     * @var ClassGenerator
     */
    protected $_classGenerator;

    /**
     * @var ServicesGenerator
     */
    protected $_servicesGenerator;

    /**
     * @var Select
     */
    protected $_select;

    /**
     * @var bool
     */
    protected $_silentMode = false;

    /**
     * Set Command parameters
     */
    protected function configure()
    {
        $this->setName('maxmode:generate:sonata-admin')
            ->setDescription('Generate CRUD for entity by creating Sonata-Admin class')
            ->addArgument('entity', null, 'Entity class for witch generate CRUD; will be asked if not provided')
            ->addOption('services', null, InputOption::VALUE_OPTIONAL, 'Whether update services.xml or not', 1);
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
        $this->setSilentMode((bool) $input->getOption('no-interaction'));
        $this->resolveEntityClass($input, $output);
        $this->resolveListFields($output);
        $this->resolveEditFields($output);

        if ($this->_silentMode || $this->getDialog()->askConfirmation($output,
                "Confirm generation of admin class into file '{$this->getClassGenerator()->getAdminFileName()}' ?")) {
            $this->getClassGenerator()->generate();
            $output->writeln('Class generated successfully');

            $this->resolveDashboardGroup($output);
            if ($input->getOption('services') and $this->_silentMode || $this->getDialog()
                    ->askConfirmation($output, "Confirm automatic update of services.xml file?")) {
                $this->resolveServicesXmlFile($output);
                $this->getServicesGenerator()->generate();
                $output->writeln('Services file updated successfully');
            } else {
                $output->writeln("Please, update services manually. Services xml code: \n"
                    . $this->getServicesGenerator()->getGeneratedCode());
            }

        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function resolveEntityClass(InputInterface $input, OutputInterface $output)
    {
        $entityClass = $input->getArgument('entity');
        if ($entityClass || $this->_silentMode) {
            $this->getEntitySelect()->validateClass($entityClass);
        } else {
            $entityList = $this->getEntitySelect()->getEntityList();
            $defaultEntity = $this->getEntitySelect()->getFirstEntity();
            $question = <<<ASK

Please, enter the entity class name
The class name must be the fully-qualified class name without a leading backslash
(as it is returned by get_class(\$obj)) or an aliased class name.
[$defaultEntity]
>
ASK;
            $entityClass = $this->getDialog()->askAndValidate($output, $question,
                array($this->getEntitySelect(), 'validateClass'), false, $defaultEntity, $entityList);
        }

        $this->getClassGenerator()->setEntityClass($entityClass);
    }

    /**
     * @param OutputInterface $output
     */
    protected function resolveListFields($output)
    {
        $listFields = array();
        $entityFields = $this->getClassGenerator()->getEntityFields();
        if ($this->_silentMode || $this->getDialog()->askConfirmation($output,
                "Do you want to have all entity's fields in the List table?")) {
            $listFields = $entityFields;
        } else {
            $output->writeln("You will be asked about each field of entity.\nSet 'y/n' to add field to the List table");
            foreach ($entityFields as $entityField) {
                if ($this->getDialog()->askConfirmation($output, $entityField . ' ')) {
                    $listFields[] = $entityField;
                }
            }
        }

        $this->getClassGenerator()->setListFields($listFields);
    }

    /**
     * @param OutputInterface $output
     */
    protected function resolveEditFields($output)
    {
        $editFields = array();
        $entityFields = $this->getClassGenerator()->getEntityFields();
        if ($this->_silentMode || $this->getDialog()->askConfirmation($output,
                "Do you want to have all entity's fields in the Create/Edit form?")) {
            $editFields = $entityFields;
        } else {
            $output->writeln("You will be asked about each field of entity.\nSet 'y/n' to add field to the List table");
            foreach ($entityFields as $entityField) {
                if ($this->getDialog()->askConfirmation($output, $entityField . ' ')) {
                    $editFields[] = $entityField;
                }
            }
        }

        $this->getClassGenerator()->setEditFields($editFields);
    }

    /**
     * @param OutputInterface $output
     */
    protected function resolveDashboardGroup(OutputInterface $output)
    {
        if (!$this->_silentMode) {
            $defaultGroup = $this->getServicesGenerator()->getGroup();
            $this->getServicesGenerator()->setGroup(
                $this->getDialog()->ask($output, "Specify group id for dashboard [$defaultGroup]", $defaultGroup)
            );
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function resolveServicesXmlFile(OutputInterface $output)
    {
        if (!$this->_silentMode) {
            $defaultFile = $this->getServicesGenerator()->getServicesDefinitionFile();
            $this->getServicesGenerator()->setServicesDefinitionFile(
                $this->getDialog()->ask($output,
                    "Specify services.xml file location [$defaultFile]", $defaultFile)
            );
        }
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
     * @return ClassGenerator
     */
    public function getClassGenerator()
    {
        return $this->_classGenerator;
    }

    /**
     * @param ClassGenerator $generator
     */
    public function setClassGenerator($generator)
    {
        $this->_classGenerator = $generator;
    }

    /**
     * @return ServicesGenerator
     */
    public function getServicesGenerator()
    {
        return $this->_servicesGenerator;
    }

    /**
     * @param ServicesGenerator $generator
     */
    public function setServicesGenerator($generator)
    {
        $this->_servicesGenerator = $generator;
    }

    /**
     * @return Select
     */
    public function getEntitySelect()
    {
        return $this->_select;
    }

    /**
     * @param Select $select
     */
    public function setEntitySelect($select)
    {
        $this->_select = $select;
    }

    /**
     * @param bool $mode
     */
    public function setSilentMode($mode)
    {
        $this->_silentMode = $mode;
    }
}
