<?php

namespace TwentyFifth\Serializing\Simple;

use TwentyFifth\Serializing\Annotations\AnnotationSerializable;

/**
 * Parses the Annotation of an annotation serializable class and its super classes
 */
class SimpleAnnotationParser {
	const METHOD_REGEX = '#@method [\[\]a-zA-Z\\\\]+ ([a-zA-Z]+).*?\n#s';
	const PROPERTY_REGEX = '#@method [\[\]a-zA-Z\\\\]+ ((get|is)([a-zA-Z]+)).*?\n#s';
	const IGNORE_REGEX   = '#@noSerialize ([a-zA-Z]+)\n#s';
	const IGNORE_LIST_REGEX   = '#@noListSerialize ([a-zA-Z]+)\n#s';

	private static $methodCache = array();
	private static $propertyCache = array();
	private static $ignoreCache = array();
	private static $ignoreListCache = array();

	/**
	 * @param AnnotationSerializable $object
	 *
	 * @return string[]
	 */
	public static function getMethods(AnnotationSerializable $object)
	{
		$objectClass = get_class($object);

		if (array_key_exists($objectClass, self::$methodCache)) {
			return self::$methodCache[$objectClass];
		}

		$methods = array();

		for ($class = new \ReflectionClass($objectClass); $class != null; $class = $class->getParentClass()) {
			preg_match_all(self::METHOD_REGEX, $class->getDocComment(), $matches);
			$methods = array_merge($methods, $matches[1]);
		}

		self::$methodCache[$objectClass] = $methods;

		return $methods;
	}

	/**
	 * @param AnnotationSerializable $object
	 *
	 * @return string[]
	 */
	public static function getProperties(AnnotationSerializable $object)
	{
		$objectClass = get_class($object);

		if (array_key_exists($objectClass, self::$propertyCache)) {
			return self::$propertyCache[$objectClass];
		}

		$properties = array();

		for ($class = new \ReflectionClass($objectClass); $class != null; $class = $class->getParentClass()) {
			preg_match_all(self::PROPERTY_REGEX, $class->getDocComment(), $matches);
			foreach ($matches[3] as $index => $property) {
				$properties[lcfirst($property)] = $matches[1][$index];
			}
		}

		self::$propertyCache[$objectClass] = $properties;

		return $properties;
	}

	/**
	 * @param AnnotationSerializable $object
	 *
	 * @return string[]
	 */
	public static function getIgnores(AnnotationSerializable $object)
	{
		$objectClass = get_class($object);

		if (array_key_exists($objectClass, self::$ignoreCache)) {
			return self::$ignoreCache[$objectClass];
		}

		$ignores = array();

		for ($class = new \ReflectionClass($objectClass); $class != null; $class = $class->getParentClass()) {
			preg_match_all(self::IGNORE_REGEX, $class->getDocComment(), $matches);
			$ignores = array_merge($ignores, $matches[1]);
		}

		$ignores = array_map('lcfirst', $ignores);

		self::$ignoreCache[$objectClass] = $ignores;

		return $ignores;
	}
    
	/**
	 * @param AnnotationSerializable $object
	 *
	 * @return string[]
	 */
	public static function getListIgnores(AnnotationSerializable $object)
	{
		$objectClass = get_class($object);

		if (array_key_exists($objectClass, self::$ignoreListCache)) {
			return self::$ignoreListCache[$objectClass];
		}

		$ignores = array();

		for ($class = new \ReflectionClass($objectClass); $class != null; $class = $class->getParentClass()) {
			preg_match_all(self::IGNORE_LIST_REGEX, $class->getDocComment(), $matches);
			$ignores = array_merge($ignores, $matches[1]);
		}

		$ignores = array_map('lcfirst', $ignores);

		self::$ignoreListCache[$objectClass] = $ignores;

		return $ignores;
	}
}
