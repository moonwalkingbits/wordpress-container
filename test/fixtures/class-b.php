<?php

namespace Moonwalking_Bits\Container\Fixtures;

class B {

	public A $a;

	public function __construct( A $a ) {
		$this->a = $a;
	}
}
