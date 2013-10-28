<?php
namespace Maxmode\GeneratorBundle\Tests\Unit\Doctrine\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Maxmode\GeneratorBundle\Doctrine\Entity\Item;

/**
 * Class ItemTest
 *
 * @group unit
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item
     */
    protected $item;

    /**
     * @var EntityManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var ClassMetadata | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMetadata;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->item = new Item();

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassMetadata'))
            ->getMock();
        $this->classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->setMethods(array('getFieldNames', 'getTypeOfField'))
            ->getMock();
        $this->item->setEntityManager($this->entityManager);
    }

    /**
     * Test for Item::getEntityFields()
     */
    public function testGetEntityFields()
    {
        $className = 'TestVendor\TestBundle\Entity\Carrot';
        $fields = array('id', 'name', 'length');
        $this->item->setItemClassName($className);

        $this->entityManager->expects($this->any())->method('getClassMetadata')->with($className)
            ->will($this->returnValue($this->classMetadata));
        $this->classMetadata->expects($this->once())->method('getFieldNames')
            ->will($this->returnValue($fields));

        $this->assertEquals($fields, $this->item->getEntityFields());
    }

    /**
     * Test for Item::getFieldType()
     */
    public function testGetFieldType()
    {
        $className = 'TestVendor\TestBundle\Entity\Carrot';
        $fieldName = 'length';
        $fieldType = 'string';
        $this->item->setItemClassName($className);

        $this->entityManager->expects($this->any())->method('getClassMetadata')->with($className)
            ->will($this->returnValue($this->classMetadata));
        $this->classMetadata->expects($this->once())->method('getTypeOfField')->with($fieldName)
            ->will($this->returnValue($fieldType));

        $this->assertEquals($fieldType, $this->item->getFieldType($fieldName));
    }
}
