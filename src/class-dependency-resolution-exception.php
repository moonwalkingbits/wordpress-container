<?php
/**
 * Container: Dependency resolution exception class
 *
 * @package Moonwalking_Bits\Container
 * @author Martin Pettersson
 * @license GPL-2.0
 * @since 0.1.0
 */

namespace Moonwalking_Bits\Container;

use ReflectionParameter;

/**
 * An exception to be thrown when a dependency cannot be resolved.
 *
 * @since 0.1.0
 * @see \Moonwalking_Bits\Container\Container_Exception
 */
class Dependency_Resolution_Exception extends Container_Exception {

	/**
	 * Creates a new exception instance.
	 *
	 * @param \ReflectionParameter $dependency Arbitrary dependency.
	 */
	public function __construct( ReflectionParameter $dependency ) {
		$class  = $dependency->getDeclaringClass();
		$method = $dependency->getDeclaringFunction()->getName();

		if ( ! is_null( $class ) ) {
			$method = $class->getName() . '::' . $method;
		}

		parent::__construct( 'Unresolved dependency: ' . $dependency->getName() . ' in ' . $method );
	}
}
