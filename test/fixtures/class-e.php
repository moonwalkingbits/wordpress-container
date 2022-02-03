<?php

namespace Moonwalking_Bits\Container\Fixtures;

class E {

	public string $message;
	public ?A $a;

	public function __construct( string $message = '', ?A $a = null ) {
		$this->message = $message;
		$this->a = $a;
	}
}
