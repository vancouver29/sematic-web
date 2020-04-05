<?php
namespace BlueSpice\NamespaceCSS\Api\Store;

use BlueSpice\NamespaceCSS\Data\Store;
use BlueSpice\Context;
use BlueSpice\Services;

class NamespaceCSS extends \BlueSpice\StoreApiBase {

	protected function makeDataStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			Services::getInstance()->getDBLoadBalancer()
		);
	}
}