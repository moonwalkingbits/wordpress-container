<?php

namespace Moonwalking_Bits\Container;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Moonwalking_Bits\Container\Container
 */
class Container_Test extends TestCase {

	private Container $container;

	/**
	 * @before
	 */
	public function set_up(): void {
		$this->container = new Container();
	}

	/**
	 * @test
	 */
	public function should_implement_container_interface(): void {
		$this->assertContains( Container_Interface::class, class_implements( $this->container::class ) );
	}

	/**
	 * @test
	 */
	public function should_resolve_bound_instance(): void {
		$value = 'value';
		$this->container->bind_instance( 'identifier', $value );

		$this->assertSame( $value, $this->container->resolve( 'identifier' ) );
	}

	/**
	 * @test
	 */
	public function should_resolve_bound_factory_value(): void {
		$value = 'value';
		$this->container->bind_factory( 'identifier', fn() => $value );

		$this->assertSame( $value, $this->container->resolve( 'identifier' ) );
	}

	/**
	 * @test
	 */
	public function should_resolve_bound_class(): void {
		$this->container->bind_class( Fixtures\A_Interface::class, Fixtures\A::class );

		$this->assertInstanceOf( Fixtures\A::class, $this->container->resolve( Fixtures\A_Interface::class ) );
	}

	/**
	 * @test
	 */
	public function should_throw_exception_if_bound_class_cannot_be_found(): void {
		$this->expectException( Class_Not_Found_Exception::class );

		$this->container->bind_class( Fixtures\A_Interface::class, 'Non_Existing_Class' );

		$this->container->resolve( Fixtures\A_Interface::class );
	}

	/**
	 * @test
	 */
	public function should_resolve_class_instance(): void {
		$this->assertInstanceOf( Fixtures\A::class, $this->container->resolve( Fixtures\A::class ) );
	}

	/**
	 * @test
	 */
	public function should_resolve_alias(): void {
		$value = 'value';
		$this->container->bind_instance( 'identifier', $value );
		$this->container->alias( 'identifier', 'alias' );

		$this->assertSame( $value, $this->container->resolve( 'alias' ) );
	}

	/**
	 * @test
	 */
	public function should_resolve_nested_alias(): void {
		$value = 'value';
		$this->container->bind_instance( 'identifier', $value );
		$this->container->alias( 'identifier', 'alias' );
		$this->container->alias( 'alias', 'another-alias' );

		$this->assertSame( $value, $this->container->resolve( 'another-alias' ) );
	}

	/**
	 * @test
	 */
	public function should_accept_constructor_dependencies(): void {
		$b = $this->container->resolve( Fixtures\B::class, new Fixtures\A() );

		$this->assertInstanceOf( Fixtures\B::class, $b );
		$this->assertInstanceOf( Fixtures\A::class, $b->a );
	}

	/**
	 * @test
	 */
	public function should_resolve_constructor_dependencies(): void {
		$this->assertInstanceOf( Fixtures\A::class, $this->container->resolve( Fixtures\B::class )->a );
	}

	/**
	 * @test
	 */
	public function should_resolve_recursive_dependencies(): void {
		$this->assertInstanceOf( Fixtures\A::class, $this->container->resolve( Fixtures\C::class )->b->a );
	}

	/**
	 * @test
	 */
	public function should_use_provided_dependencies(): void {
		$message = 'message';
		$d = $this->container->resolve( Fixtures\D::class, $message );

		$this->assertSame( $message, $d->message );
		$this->assertInstanceOf( Fixtures\A::class, $d->a );
	}

	/**
	 * @test
	 */
	public function should_use_default_value_if_available(): void {
		$message = 'message';
		$e = $this->container->resolve( Fixtures\E::class, $message );

		$this->assertSame( $message, $e->message );
		$this->assertNull( $e->a );
	}

	/**
	 * @test
	 */
	public function should_throw_exception_if_a_dependency_cannot_be_resolved(): void {
		$this->expectException( Dependency_Resolution_Exception::class );

		$this->container->resolve( Fixtures\F::class );
	}

	/**
	 * @test
	 */
	public function should_throw_exception_if_dependency_is_recursive(): void {
		$this->expectException( Recursive_Dependency_Exception::class );

		$this->container->resolve( Fixtures\A_Recursive::class );
	}

	/**
	 * @test
	 */
	public function should_throw_exception_if_non_instantiable(): void {
		$this->expectException( Not_Instantiable_Exception::class );

		$this->container->resolve( Fixtures\A_Interface::class );
	}

	/**
	 * @test
	 */
	public function should_resolve_callable(): void {
		$value = 'value';

		$this->assertSame( $value, $this->container->invoke( fn() => $value ) );
	}

	/**
	 * @test
	 */
	public function should_resolve_invokation_dependencies(): void {
		$this->assertInstanceof( Fixtures\A::class, $this->container->invoke( fn( Fixtures\B $b ) => $b->a ) );
	}

	/**
	 * @test
	 */
	public function should_throw_exception_if_invokation_dependency_cannot_be_resolved(): void {
		$this->expectException( Dependency_Resolution_Exception::class );

		$this->container->invoke( fn( string $message ) => $message );
	}
}
