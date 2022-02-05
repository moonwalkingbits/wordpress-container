<?php
/**
 * Container: Container implementation
 *
 * @package Moonwalking_Bits\Container
 * @author Martin Pettersson
 * @license GPL-2.0
 * @since 0.1.0
 */

namespace Moonwalking_Bits\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * A dependency injection container implementation.
 *
 * @since 0.1.0
 */
class Container implements Container_Interface {

	/**
	 * Registered bindings.
	 *
	 * @var array<string, callable>
	 */
	private array $bindings = array();

	/**
	 * Registered instances.
	 *
	 * @var array<string, mixed>
	 */
	private array $instances = array();

	/**
	 * Registered identifier aliases.
	 *
	 * @var array<string, string>
	 */
	private array $aliases = array();

	/**
	 * Stack keeping track of dependencies to prevent circular dependencies.
	 *
	 * @var string[]
	 */
	private array $dependency_stack = array();

	/**
	 * Binds a class to the given identifier.
	 *
	 * This allows an instance of the class to later be resolved with the identifier.
	 *
	 * @since 0.1.0
	 * @param string $identifier Arbitrary identifier.
	 * @param string $class_name Name of the class to bind to the identifier.
	 */
	public function bind_class( string $identifier, string $class_name ): void {
		$this->bindings[ $identifier ] = fn(
			...$parameters
		) => $this->resolve( $class_name, ...$parameters );
	}

	/**
	 * Binds an arbitrary value to the given identifier.
	 *
	 * @since 0.1.0
	 * @param string $identifier Arbitrary identifier.
	 * @param mixed  $instance Arbitrary value.
	 */
	public function bind_instance( string $identifier, $instance ): void {
		$this->instances[ $identifier ] = $instance;
	}

	/**
	 * Binds a factory to the given identifier.
	 *
	 * This allows the factory to later be invoked with the identifier.
	 *
	 * @since 0.1.0
	 * @param string   $identifier Arbitrary identifier.
	 * @param callable $factory Arbitrary value factory.
	 */
	public function bind_factory( string $identifier, callable $factory ): void {
		$this->bindings[ $identifier ] = $factory;
	}

	/**
	 * Resolves an arbitrary value from the container matching the given identifier.
	 *
	 * @since 0.1.0
	 * @param string $identifier Arbitrary identifier.
	 * @param mixed  ...$parameters Arbitrary set of parameters to pass to the construction of the value.
	 * @return mixed Arbitrary value matching the given identifier.
	 * @throws \Moonwalking_Bits\Container\Recursive_Dependency_Exception If any dependencies are recursive.
	 */
	public function resolve( string $identifier, ...$parameters ) {
		$resolved_identifier = $this->resolve_identifier( $identifier );

		if ( in_array( $resolved_identifier, $this->dependency_stack, true ) ) {
			throw new Recursive_Dependency_Exception( $resolved_identifier );
		}

		$this->dependency_stack[] = $resolved_identifier;

		if ( array_key_exists( $resolved_identifier, $this->instances ) ) {
			array_pop( $this->dependency_stack );

			return $this->instances[ $resolved_identifier ];
		}

		$instance = array_key_exists( $resolved_identifier, $this->bindings ) ?
			// @phan-suppress-next-line PhanParamTooMany
			$this->bindings[ $resolved_identifier ]( $this, ...$parameters ) :
			$this->create_instance( $resolved_identifier, ...$parameters );

		array_pop( $this->dependency_stack );

		return $instance;
	}

	/**
	 * Invokes the given callable resolving any required dependencies.
	 *
	 * @since 0.1.0
	 * @param callable $callable Arbitrary callable.
	 * @param mixed    ...$parameters Arbitrary set of parameters to pass to the invokation.
	 * @return mixed Callable invokation return value.
	 */
	public function invoke( callable $callable, ...$parameters ) {
		$reflection = new ReflectionFunction( Closure::fromCallable( $callable ) );

		if ( $reflection->getNumberOfParameters() === 0 ) {
			return $reflection->invoke();
		}

		return $reflection->invokeArgs( $this->resolve_method_dependencies( $reflection, $parameters ) );
	}

	/**
	 * Adds an identifier alias.
	 *
	 * @param string $identifier Arbitrary identifier.
	 * @param string $alias Arbitrary identifier alias.
	 */
	public function alias( string $identifier, string $alias ): void {
		while ( array_key_exists( $identifier, $this->aliases ) ) {
			$identifier = $this->aliases[ $identifier ];
		}

		$this->aliases[ $alias ] = $identifier;
	}

	/**
	 * Resolves the origin identifier behind a possible set of aliases.
	 *
	 * The set of aliases is flattened when adding an alias so all keys of the
	 * alias array points to origin identifiers.
	 *
	 * @param string $identifier Arbitrary identifier.
	 * @return string Origin identifier.
	 */
	private function resolve_identifier( string $identifier ): string {
		return $this->aliases[ $identifier ] ?? $identifier;
	}

	/**
	 * Creates an instance of the given class name.
	 *
	 * @param string $class_name Arbitrary class name.
	 * @param mixed  ...$parameters Arbitrary set of parameters to pass to the constructor.
	 * @return mixed Class instance.
	 * @throws \Moonwalking_Bits\Container\Class_Not_Found_Exception If class cannot be loaded.
	 * @throws \Moonwalking_Bits\Container\Not_Instantiable_Exception If class is not instantiable.
	 */
	private function create_instance( string $class_name, ...$parameters ) {
		try {
			$reflection = new ReflectionClass( $class_name );
		} catch ( ReflectionException $_ ) {
			throw new Class_Not_Found_Exception( $class_name );
		}

		if ( ! $reflection->isInstantiable() ) {
			throw new Not_Instantiable_Exception( $class_name );
		}

		$constructor = $reflection->getConstructor();

		if ( is_null( $constructor ) ) {
			return $reflection->newInstance();
		}

		return $reflection->newInstanceArgs( $this->resolve_method_dependencies( $constructor, $parameters ) );
	}

	/**
	 * Resolves any unmet dependencies of the given method.
	 *
	 * @param \ReflectionFunctionAbstract $method Arbitrary method instance.
	 * @param array                       $parameters Arbitrary parameters.
	 * @return array Set of resolved method dependencies.
	 */
	private function resolve_method_dependencies( ReflectionFunctionAbstract $method, array $parameters ): array {
		if ( $method->getNumberOfParameters() === count( $parameters ) ) {
			return $parameters;
		}

		return array_map(
			fn( $parameter ) => array_key_exists( $parameter->getPosition(), $parameters ) ?
				$parameters[ $parameter->getPosition() ] :
				$this->resolve_method_parameter( $parameter ),
			$method->getParameters()
		);
	}

	/**
	 * Resolves a value for the given parameter.
	 *
	 * @param \ReflectionParameter $parameter Arbitrary method parameter.
	 * @return mixed Arbitrary resolved value.
	 * @throws \Moonwalking_Bits\Container\Dependency_Resolution_Exception When a method dependency cannot be resolved.
	 */
	private function resolve_method_parameter( ReflectionParameter $parameter ) {
		if ( $parameter->isOptional() ) {
			return $parameter->getDefaultValue();
		}

		$type = $parameter->getType();

		if ( is_null( $type ) || ( $type instanceof ReflectionNamedType && $type->isBuiltin() ) ) {
			throw new Dependency_Resolution_Exception( $parameter );
		}

		return $this->resolve( $type instanceof ReflectionNamedType ? $type->getName() : (string) $type );
	}
}
