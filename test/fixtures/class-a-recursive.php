<?php

namespace Moonwalking_Bits\Container\Fixtures;

class A_Recursive {

	public B_Recursive $b;

	public function __construct( B_Recursive $b ) {
		$this->b = $b;
	}
}
