<?php
/**
 * Container: Class not found exception class
 *
 * @package Moonwalking_Bits\Container
 * @author Martin Pettersson
 * @license GPL-2.0
 * @since 0.1.0
 */

namespace Moonwalking_Bits\Container;

/**
 * An exception to be thrown when a class cannot be loaded.
 *
 * @since 0.1.0
 * @see \Moonwalking_Bits\Container\Container_Exception
 */
class Class_Not_Found_Exception extends Container_Exception {

	/**
	 * Creates a new exception instance.
	 *
	 * @param string $class_name Arbitrary class name.
	 */
	public function __construct( string $class_name ) {
		parent::__construct( 'Class cannot be loaded: ' . $class_name );
	}
}
