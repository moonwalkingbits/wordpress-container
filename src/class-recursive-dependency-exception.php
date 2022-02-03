<?php
/**
 * Container: Recursive dependency exception class
 *
 * @package Moonwalking_Bits\Container
 * @author Martin Pettersson
 * @license GPL-2.0
 * @since 0.1.0
 */

namespace Moonwalking_Bits\Container;

/**
 * An exception to be thrown when a dependency is recursive.
 *
 * @since 0.1.0
 * @see \Moonwalking_Bits\Container\Container_Exception
 */
class Recursive_Dependency_Exception extends Container_Exception {

	/**
	 * Creates a new exception instance.
	 *
	 * @param string $identifier Arbitrary identifier.
	 */
	public function __construct( string $identifier ) {
		parent::__construct( 'Identifier is being resolved recursively: ' . $identifier );
	}
}
