<?php

namespace BlueSpice\NamespaceCSS\Data;

class Store implements \BlueSpice\Data\IStore {

	/**
	 * @var \IContextSource
	 */
	protected $context = null;

	public function __construct( $context, $loadBalancer ) {
		$this->loadBalancer = $loadBalancer;
		$this->context = $context;
	}

	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

	public function getWriter() {
		throw new Exception( 'This store does not support writing!' );
	}

}
