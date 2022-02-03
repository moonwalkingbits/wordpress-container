<?php

namespace Moonwalking_Bits\Container\Fixtures;

class C {

	public B $b;

	public function __construct( B $b ) {
		$this->b = $b;
	}
}
