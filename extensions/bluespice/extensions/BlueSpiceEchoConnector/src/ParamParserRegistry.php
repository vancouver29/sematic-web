<?php

namespace BlueSpice\EchoConnector;

class ParamParserRegistry implements \BlueSpice\IRegistry {
	protected $paramParsers;

	public function __construct() {
		$this->paramParsers = \ExtensionRegistry::getInstance()
			->getAttribute( "BlueSpiceEchoConnectorParamParsers" );
	}

	public function getAllKeys() {
		return array_keys( $paramParsers );
	}

	public function getValue( $key, $default = '' ) {
		if ( $this->hasKey( $key ) ) {
			return $this->paramParsers[$key];
		}
	}

	public function hasKey( $key ) {
		if ( isset( $this->paramParsers[$key] ) ) {
			return true;
		}

		return false;
	}
}
