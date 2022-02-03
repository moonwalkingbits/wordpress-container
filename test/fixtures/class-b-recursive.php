<?php

namespace Moonwalking_Bits\Container\Fixtures;

class B_Recursive {

	public A_Recursive $a;

	public function __construct( A_Recursive $a ) {
		$this->a = $a;
	}
}
