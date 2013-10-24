<?php
namespace Maxmode\GeneratorBundle\Generator;

/**
 * Generate keys for translation messages
 */
class Translation
{
    /**
     * Get translation key by full-qualified class name
     *
     * @param string $className
     *
     * @return string
     */
    public function getClassTranslationKey($className)
    {
        $underlinedName = strtolower(preg_replace('#([a-z])([A-Z])#', '\1_\2', $className));
        $dotDelimitedName = str_replace('\\', '.', $underlinedName);

        return $dotDelimitedName;
    }

    /**
     * Get translation key for admin class
     *
     * @param string $className
     *
     * @return string
     */
    public function getAdminClassKey($className)
    {
        return str_replace('_admin', '', $this->getClassTranslationKey($className));
    }

    /**
     * Get translation key for admin class's field
     *
     * @param string $className
     * @param string $fieldName
     *
     * @return string
     */
    public function getAdminFieldKey($className, $fieldName)
    {
        return $this->getAdminClassKey($className . '\\Fields\\' . $fieldName);
    }
}
