<?php
namespace Maxmode\GeneratorBundle\Tests\Unit\Command\Sonata\Admin;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

use Maxmode\GeneratorBundle\Generator\AdminClass as ClassGenerator;
use Maxmode\GeneratorBundle\Generator\Services as ServicesGenerator;
use Maxmode\GeneratorBundle\Doctrine\Entity\Select;
use Maxmode\GeneratorBundle\Command\Sonata\Admin\GeneratorCommand;
use Maxmode\GeneratorBundle\Doctrine\Entity\Item;

/**
 * Functional test for  GeneratorCommand
 *
 * @package Maxmode\GeneratorBundle\Tests\Command\Sonata\Admin
 *
 * @group unit
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
     * @var Item | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityItem;

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

        $this->_classGenerator = $this->getMockBuilder('Maxmode\GeneratorBundle\Admin\ClassGenerator')
            ->setMethods(array('getEntityFields', 'setListFields', 'setEditFields', 'generate', 'setEntityItem'))
            ->getMock();
        $this->_command->setClassGenerator($this->_classGenerator);

        $this->_servicesGenerator = $this->getMockBuilder('Maxmode\GeneratorBundle\Admin\ServicesGenerator')
            ->setMethods(array('generate', 'setEntityItem', 'setClassGenerator'))
            ->getMock();
        $this->_command->setServicesGenerator($this->_servicesGenerator);

        $this->_select = $this->getMockBuilder('Maxmode\GeneratorBundle\Doctrine\Entity\Select')
            ->setMethods(array('validateClass'))
            ->getMock();
        $this->_command->setEntitySelect($this->_select);

        $this->_entityItem = $this->getMockBuilder('Maxmode\GeneratorBundle\Doctrine\Entity\Item')
            ->setMethods(array('getEntityFields'))
            ->getMock();
        $this->_command->setEntityItem($this->_entityItem);
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

        $this->_entityItem->expects($this->exactly(2))
            ->method('getEntityFields')
            ->will($this->returnValue($fields));
        $this->_classGenerator->expects($this->once())->method('setListFields')->with($fields);
        $this->_classGenerator->expects($this->once())->method('setEditFields')->with($fields);
        $this->_classGenerator->expects($this->once())->method('generate');
        $this->_classGenerator->expects($this->once())->method('setEntityItem')->with($this->_entityItem);

        $this->_servicesGenerator->expects($this->once())->method('generate');
        $this->_servicesGenerator->expects($this->once())->method('setEntityItem')->with($this->_entityItem);
        $this->_servicesGenerator->expects($this->once())->method('setClassGenerator')->with($this->_classGenerator);

        $application = new Application();
        $application->add($this->_command);
        $command = $application->find('maxmode:generate:sonata-admin');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'entity' => $entityClass,
            '-n' => true
        ));
    }
}
