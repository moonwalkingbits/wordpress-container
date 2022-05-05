<?php
/**
 * Container: Container interface
 *
 * @package Moonwalking_Bits\Container
 * @author Martin Pettersson
 * @license GPL-2.0
 * @since 0.1.0
 */

namespace Moonwalking_Bits\Container;

/**
 * Represents a dependency injection container.
 *
 * @since 0.1.0
 */
interface Container_Interface {

	/**
	 * Binds a class to the given identifier.
	 *
	 * This allows an instance of the class to later be resolved with the identifier.
	 *
	 * @since 0.1.0
	 * @param string $identifier Arbitrary identifier.
	 * @param string $class_name Name of the class to bind to the identifier.
	 */
	public function bind_class( string $identifier, string $class_name ): void;

	/**
	 * Binds an arbitrary value to the given identifier.
	 *
	 * @since 0.1.0
	 * @param string $identifier Arbitrary identifier.
	 * @param mixed  $instance Arbitrary value.
	 */
	public function bind_instance( string $identifier, $instance ): void;

	/**
	 * Binds a factory to the given identifier.
	 *
	 * This allows the factory to later be invoked with the identifier.
	 *
	 * @since 0.1.0
	 * @param string   $identifier Arbitrary identifier.
	 * @param callable $factory Arbitrary value factory.
	 */
	public function bind_factory( string $identifier, callable $factory ): void;

	/**
	 * Resolves an arbitrary value from the container matching the given identifier.
	 *
	 * @since 0.1.0
	 * @param string $identifier Arbitrary identifier.
	 * @param mixed  ...$parameters Arbitrary set of parameters to pass to the construction of the value.
	 * @return mixed Arbitrary value matching the given identifier.
	 */
	public function resolve( string $identifier, ...$parameters );

	/**
	 * Invokes the given callable resolving any required dependencies.
	 *
	 * @since 0.1.0
	 * @param callable $callable Arbitrary callable.
	 * @param mixed    ...$parameters Arbitrary set of parameters to pass to the invokation.
	 * @return mixed Callable invokation return value.
	 */
	public function invoke( callable $callable, ...$parameters );

	/**
	 * Adds an identifier alias.
	 *
	 * @since 0.4.0
	 * @param string $identifier Arbitrary identifier.
	 * @param string $alias Arbitrary identifier alias.
	 */
	public function alias( string $identifier, string $alias ): void;
}
