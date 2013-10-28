<?php
namespace Maxmode\GeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;

use Maxmode\GeneratorBundle\Generator\Translation;
use Maxmode\GeneratorBundle\Doctrine\Entity\Item;

/**
 * Representation of generated admin class
 */
class AdminClass
{
    const MAX_LINE_LENGTH = 120;

    /**
     * @var Item
     */
    protected $_entityItem;

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
     * @var TimedTwigEngine
     */
    protected $_templating;

    /**
     * @var Translation
     */
    protected $_translator;

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
        return $this->getAdminClassNameByEntityName($this->getEntityItem()->getItemClassName());
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
        $r = new \ReflectionClass($this->getEntityItem()->getItemClassName());

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
            'entityClass' => $this->getEntityItem()->getItemClassName(),
            'editFields' => $this->prepareFieldList($this->_editFields),
            'listFields' => $this->prepareFieldList($this->_listFields),
            'maxLineLength' => self::MAX_LINE_LENGTH,
        ));
    }

    /**
     * @param array $fieldList
     *
     * @return array
     */
    protected function prepareFieldList($fieldList)
    {
        $list = array();
        foreach ($fieldList as $fieldName) {
            $list[] = array(
                'name' => $fieldName,
                'type' => $this->getEntityItem()->getFieldType($fieldName),
                'key' => $this->_translator->getAdminFieldKey($this->getAdminClassName(), $fieldName),
            );
        }

        return $list;
    }

    /**
     * @param Translation $translation
     */
    public function setTranslation(Translation $translation)
    {
        $this->_translator = $translation;
    }


    /**
     * @param \Maxmode\GeneratorBundle\Doctrine\Entity\Item $entityItem
     */
    public function setEntityItem($entityItem)
    {
        $this->_entityItem = $entityItem;
    }

    /**
     * @return \Maxmode\GeneratorBundle\Doctrine\Entity\Item
     */
    public function getEntityItem()
    {
        return $this->_entityItem;
    }

}
