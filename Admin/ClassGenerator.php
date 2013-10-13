<?php
namespace Maxmode\GeneratorBundle\Admin;

use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;

/**
 * Representation of generated admin class
 *
 * @package Maxmode\GeneratorBundle\Admin
 */
class ClassGenerator
{
    const MAX_LINE_LENGTH = 120;

    /**
     * @var string
     */
    protected $_entityClass;

    /**
     * @var array
     */
    protected $_listFields;

    /**
     * @var array
     */
    protected $_editFields;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var EntityManager
     */
    protected $_entityManager;

    /**
     * @var TimedTwigEngine
     */
    protected $_templating;

    /**
     * @param TimedTwigEngine $twig
     */
    public function setTemplating(TimedTwigEngine $twig)
    {
        $this->_templating = $twig;
    }

    /**
     * @return TimedTwigEngine
     */
    public function getTemplating()
    {
        return $this->_templating;
    }

    /**
     * @param string $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->_entityClass = $entityClass;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->_entityClass;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->_filesystem;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem($filesystem)
    {
        $this->_filesystem = $filesystem;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->_entityManager = $entityManager;
    }

    /**
     * @param array $fields
     */
    public function setListFields($fields)
    {
        $this->_listFields = $fields;
    }

    /**
     * @param array $fields
     */
    public function setEditFields($fields)
    {
        $this->_editFields = $fields;
    }

    /**
     * Performs code generation
     */
    public function generate()
    {
        $this->getFilesystem()->dumpFile(
            $this->getAdminFileName(),
            $this->getGeneratedCode()
        );
    }

    /**
     * Return admin class name
     *
     * @return string
     */
    public function getAdminClassName()
    {
        return $this->getAdminClassNameByEntityName($this->getEntityClass());
    }

    /**
     * Return admin class name by entity class name
     *
     * @param string $entityClass
     *
     * @return string
     */
    public function getAdminClassNameByEntityName($entityClass)
    {
        return str_replace('\Entity\\', '\Admin\\', $entityClass) . 'Admin';
    }

    /**
     * @return string
     */
    public function getAdminFileName()
    {
        //todo: check on windows
        $r = new \ReflectionClass($this->getEntityClass());

        return str_replace(array(
            DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . $r->getShortName() . '.php'
        ), array(
            DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR . $r->getShortName() . 'Admin.php'
        ), $r->getFileName());
    }

    /**
     * Return generated code as text
     *
     * @return string
     * @throws \Exception
     */
    public function getGeneratedCode()
    {
        $adminClass = $this->getAdminClassName();
        if (class_exists($adminClass)) {
            throw new \Exception('Impossible to generate. Class ' . $adminClass . ' already exists.');
        }
        $namespaceParts = explode('\\', $adminClass);
        if (isset($namespaceParts[0]) && $namespaceParts[0] == '') {
            array_shift($namespaceParts);
        }

        return $this->getTemplating()->render('MaxmodeGeneratorBundle:Sonata:Admin/class.php.twig', array(
            'className' => array_pop($namespaceParts),
            'namespace' => implode('\\', $namespaceParts),
            'entityClass' => $this->getEntityClass(),
            'editFields' => $this->prepareFieldList($this->_editFields),
            'listFields' => $this->prepareFieldList($this->_listFields),
            'maxLineLength' => self::MAX_LINE_LENGTH,
        ));

    }

    /**
     * @param array $fieldList
     * @return array
     */
    protected function prepareFieldList($fieldList)
    {
        $list = array();
        foreach ($fieldList as $fieldName) {
            $list[] = array(
                'name' => $fieldName,
                'type' => $this->getFieldType($fieldName),
                'key' => $this->getFieldTranslationKey($fieldName),
            );
        }

        return $list;
    }

    /**
     * List of field names
     *
     * @return array
     */
    public function getEntityFields()
    {
        return $this->_entityManager->getClassMetadata($this->getEntityClass())->getFieldNames();
    }

    /**
     * Type of field
     *
     * @param string $fieldName
     * @return \Doctrine\DBAL\Types\Type|string
     */
    public function getFieldType($fieldName)
    {
        return $this->_entityManager->getClassMetadata($this->getEntityClass())->getTypeOfField($fieldName);
    }

    /**
     * Calculate translation key for a field
     *
     * @param $fieldName
     * @return string
     */
    protected function getFieldTranslationKey($fieldName)
    {
        $namespaceName = $this->getAdminClassName() . '\\' . $fieldName;
        $underlinedName = strtolower(preg_replace('#([a-z])([A-Z])#', '\1_\2', $namespaceName));
        $dotDelimitedName = str_replace('\\', '.', $underlinedName);
        $adminKey = str_replace('_admin', '', $dotDelimitedName);

        return $adminKey;
    }

}
