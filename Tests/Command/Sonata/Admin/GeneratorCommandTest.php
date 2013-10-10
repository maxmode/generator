<?php
namespace Maxmode\GeneratorBundle\Tests\Command\Sonata\Admin;

use Maxmode\GeneratorBundle\Admin\ClassGenerator;
use Maxmode\GeneratorBundle\Admin\ServicesGenerator;
use Maxmode\GeneratorBundle\Entity\Select;
use Maxmode\GeneratorBundle\Command\Sonata\Admin\GeneratorCommand;

/**
 * Functional test for  GeneratorCommand
 *
 * @package Maxmode\GeneratorBundle\Tests\Command\Sonata\Admin
 */
class GeneratorCommandTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var GeneratorCommand
     */
    protected $_command;

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
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->_command = new GeneratorCommand();
        $this->_classGenerator = $this->getMockBuilder('Maxmode\GeneratorBundle\Admin\ClassGenerator')->getMock();
        $this->_servicesGenerator = $this->getMockBuilder('Maxmode\GeneratorBundle\Admin\ServicesGenerator')->getMock();
        $this->_select = $this->getMockBuilder('Maxmode\GeneratorBundle\Entity\Select')->getMock();
        $this->_command->setClassGenerator($this->_classGenerator);
        $this->_command->setServicesGenerator($this->_servicesGenerator);
        $this->_command->setEntitySelect($this->_select);
        $this->_command->setSilentMode(true);
    }

    public function testExecute()
    {
        //todo: write unit test for command with silent mode
    }
}
