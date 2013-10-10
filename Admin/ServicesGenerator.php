<?php
namespace Maxmode\GeneratorBundle\Admin;

use Symfony\Component\Filesystem\Filesystem;

use Maxmode\GeneratorBundle\Admin\ClassGenerator;

/**
 * ServicesGenerator is responsible for generating services definition
 *
 * @package Maxmode\GeneratorBundle\Admin
 */
class ServicesGenerator
{
    /**
     * @var ClassGenerator
     */
    protected $_classGenerator;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var string
     */
    protected $_group = 'default';

    /**
     * @var string
     */
    protected $_servicesFile;

    /**
     * @var string
     */
    protected $_currentCode = '';

    /**
     * @return ClassGenerator
     */
    public function getClassGenerator()
    {
        return $this->_classGenerator;
    }

    /**
     * @param ClassGenerator $classGenerator
     */
    public function setClassGenerator(ClassGenerator $classGenerator)
    {
        $this->_classGenerator = $classGenerator;
    }

    /**
     * @param $code
     */
    public function setCurrentCode($code)
    {
        $this->_currentCode = $code;
    }

    /**
     * @return string
     */
    public function getCurrentCode()
    {
        return $this->_currentCode;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * @param string $groupName
     */
    public function setGroup($groupName)
    {
        $this->_group = $groupName;
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
     * Performs code generation
     */
    public function generate()
    {
        if ($this->getFilesystem()->exists($this->getServicesDefinitionFile())) {
            $this->setCurrentCode(
                file_get_contents($this->getServicesDefinitionFile())
            );
        }

        $this->getFilesystem()->dumpFile(
            $this->getServicesDefinitionFile(),
            $this->getGeneratedCode()
        );
    }

    /**
     * @return string
     */
    public function getServicesDefinitionFile()
    {
        if (!$this->_servicesFile) {
            $this->_servicesFile = preg_replace(
                '#/Admin/.+#',
                '/Resources/config/services.xml',
                $this->getClassGenerator()->getAdminFileName()
            );
        }

        return $this->_servicesFile;
    }

    /**
     * @param string $fileName
     */
    public function setServicesDefinitionFile($fileName)
    {
        $this->_servicesFile = $fileName;
    }

    /**
     * @return string
     */
    public function getAdminServiceId()
    {
        return strtolower(str_replace('\\', '.', $this->getClassGenerator()->getAdminClassName()));
    }

    /**
     * @return string
     */
    public function getAdminServiceClassId()
    {
        return $this->getAdminServiceId() . '.class';
    }

    /**
     * @return string
     */
    public function getServiceTagString()
    {
        $parameter = $this->getAdminServiceClassId();
        $id = $this->getAdminServiceClassId();
        $entityClass = $this->getClassGenerator()->getEntityClass();
        $adminClass = $this->getClassGenerator()->getAdminClassName();
        $services = <<<XML

        <!-- CRUD for {$adminClass} -->
        <service id="{$id}" class="%{$parameter}%">
            <tag name="sonata.admin" manager_type="orm" group="{$this->getGroup()}" />
            <argument />
            <argument>{$entityClass}</argument>
            <argument>SonataAdminBundle:CRUD</argument>
        </service>
XML;
        return $services;
    }

    public function getParameterTagString()
    {
        $parameter = $this->getAdminServiceClassId();
        $adminClass = $this->getClassGenerator()->getAdminClassName();
        $parameters = <<<XML
    <parameter key="{$parameter}">{$adminClass}</parameter>
XML;
        return $parameters;
    }

    /**
     *
     * @return string
     */
    public function getGeneratedCode()
    {
        if ($this->getCurrentCode()) {
            $xmlElement = simplexml_load_string($this->getCurrentCode());
            if (isset($xmlElement->parameters) && isset($xmlElement->services)) {
                return $this->_appendTags($this->getCurrentCode());
            }
        }

        return $this->_generateNewFile();
    }

    /**
     * @return string
     */
    protected function _generateNewFile()
    {
        $fileCode = <<<XML
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <parameters>
    {$this->getParameterTagString()}
    </parameters>

    <services>
        {$this->getServiceTagString()}
    </services>

</container>

XML;
        return $fileCode;
    }

    /**
     * @param string $currentCode
     *
     * @return string
     */
    protected function _appendTags($currentCode)
    {
        return str_replace(
            array(
                '</parameters>',
                '</services>'
            ),
            array(
                $this->getParameterTagString() . "\n    </parameters>",
                $this->getServiceTagString() . "\n    </services>"
            ),
            $currentCode
        );
    }
}
