<?php

namespace BlueSpice\PageAssignments\Assignable;

use BlueSpice\PageAssignments\Data\Assignable\Group\Store;
use BlueSpice\Services;

class Group extends \BlueSpice\PageAssignments\Assignable {

	public function getStore() {
		return new Store(
			$this->context,
			Services::getInstance()->getDBLoadBalancer()
		);
	}

	public function getAssignmentClass() {
		return "\\BlueSpice\\PageAssignments\\Assignment\\Group";
	}

	public function getTypeMessageKey() {
		return "bs-pageassignments-assignee-type-group";
	}
}