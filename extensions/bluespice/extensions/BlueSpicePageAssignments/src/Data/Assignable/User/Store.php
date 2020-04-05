<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

class Store extends \BlueSpice\Data\User\Store {

	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

}
