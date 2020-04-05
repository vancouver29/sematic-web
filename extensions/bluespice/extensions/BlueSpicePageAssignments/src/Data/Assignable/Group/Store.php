<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Group;

class Store extends \BlueSpice\PageAssignments\Data\Assignable\Store {

	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

}
