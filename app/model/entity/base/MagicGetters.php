<?php

namespace App\Model\Entity;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Excerpt from Kdyby\MagicAccessors
 * @author Filip Procházka
 * @license GNU GPL2
 */
trait MagicGetters
{
    /**
     * @var array
     */
    private static $__properties = [];

    /**
     * @var array
     */
    private static $__methods = [];

    /**
     * Returns property value. Do not call directly.
     *
     * @param string $name property name
     *
     * @return mixed property value
     * @throws Exception if the property is not defined.
     */
    public function &__get(string $name)
    {
        if ($name === '') {
            throw new Exception(sprintf('Cannot read a class %s property without name.', self::class));
        }

        // property getter support
        $originalName = $name;
        $name[0] = $name[0] & "\xDF"; // case-sensitive checking, capitalize first character
        $m = 'get' . $name;

        $methods = $this->listObjectMethods();
        if (isset($methods[$m])) {
            // ampersands:
            // - uses &__get() because declaration should be forward compatible
            // - doesn't call &$_this->$m because user could bypass property setter by: $x = & $obj->property; $x = 'new value';
            $val = $this->$m();

            return $val;
        }

        $m = 'is' . $name;
        if (isset($methods[$m])) {
            $val = $this->$m();

            return $val;
        }

        // protected attribute support
        $properties = $this->listObjectProperties();
        $name = $originalName;
        if (isset($properties[$name])) {
            $val = $this->$name;

            return $val;
        }

        $type = isset($methods['set' . $name]) ? 'a write-only' : 'an undeclared';
        throw new Exception(sprintf('Cannot read %s property %s::$%s.', $type, self::class, $name));
    }

    /**
     * Should return only public or protected properties of class
     *
     * @return array
     */
    private function listObjectProperties()
    {
        $class = get_class($this);
        if (!isset(self::$__properties[$class])) {
            $refl = new ReflectionClass($class);
            $properties = array_map(function (ReflectionProperty $property) {
                return $property->getName();
            }, $refl->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED));

            self::$__properties[$class] = array_flip($properties);
        }

        return self::$__properties[$class];
    }

    /**
     * Should return all public methods of class
     *
     * @return array
     */
    private function listObjectMethods()
    {
        $class = get_class($this);
        if (!isset(self::$__methods[$class])) {
            $refl = new ReflectionClass($class);
            $methods = array_map(function (ReflectionMethod $method) {
                return $method->getName();
            }, $refl->getMethods(ReflectionMethod::IS_PUBLIC));

            self::$__methods[$class] = array_flip($methods);
        }

        return self::$__methods[$class];
    }
}
