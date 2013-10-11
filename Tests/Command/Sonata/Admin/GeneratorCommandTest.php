<?php
namespace Maxmode\GeneratorBundle\Tests\Command\Sonata\Admin;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

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
     * @var ClassGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_classGenerator;

    /**
     * @var ServicesGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_servicesGenerator;

    /**
     * @var Select | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_select;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->_command = new GeneratorCommand();
        $this->_command->setSilentMode(true);

        $this->_classGenerator = $this->getMockBuilder('Maxmode\GeneratorBundle\Admin\ClassGenerator')
            ->setMethods(array('getEntityFields', 'setEntityClass', 'setListFields', 'setEditFields', 'generate'))
            ->getMock();
        $this->_command->setClassGenerator($this->_classGenerator);

        $this->_servicesGenerator = $this->getMockBuilder('Maxmode\GeneratorBundle\Admin\ServicesGenerator')
            ->setMethods(array('generate'))
            ->getMock();
        $this->_command->setServicesGenerator($this->_servicesGenerator);

        $this->_select = $this->getMockBuilder('Maxmode\GeneratorBundle\Entity\Select')
            ->setMethods(array('validateClass'))
            ->getMock();
        $this->_command->setEntitySelect($this->_select);
    }

    /**
     * Test for GeneratorCommand::execute()
     */
    public function testExecute()
    {
        $entityClass = 'Maxmode\TestBundle\Entity\Category';
        $fields = array('id', 'name', 'title');

        $this->_select->expects($this->once())->method('validateClass')->with($entityClass)
            ->will($this->returnValue($entityClass));

        $this->_classGenerator->expects($this->exactly(2))
            ->method('getEntityFields')
            ->will($this->returnValue($fields));
        $this->_classGenerator->expects($this->once())->method('setEntityClass')->with($entityClass);
        $this->_classGenerator->expects($this->once())->method('setListFields')->with($fields);
        $this->_classGenerator->expects($this->once())->method('setEditFields')->with($fields);
        $this->_classGenerator->expects($this->once())->method('generate');

        $this->_servicesGenerator->expects($this->once())->method('generate');

        $application = new Application();
        $application->add($this->_command);
        $command = $application->find('maxmode:generate:sonata-admin');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'entity' => $entityClass
        ));
    }
}
