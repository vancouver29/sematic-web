<?php
namespace BlueSpice\PageAssignments\Api\Store;

use BlueSpice\PageAssignments\Data\Assignable\Store;
use BlueSpice\Context;

class Assignable extends \BlueSpice\StoreApiBase {

	protected function makeDataStore() {
		return new Store(
			new Context( $this->getContext(), $this->getConfig() ),
			\MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}
}