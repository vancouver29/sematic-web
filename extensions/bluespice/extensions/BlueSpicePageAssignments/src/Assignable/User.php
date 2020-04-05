<?php

namespace BlueSpice\PageAssignments\Assignable;

use BlueSpice\PageAssignments\Data\Assignable\User\Store;
use BlueSpice\Services;

class User extends \BlueSpice\PageAssignments\Assignable {

	public function getStore() {
		return new Store(
			$this->context,
			Services::getInstance()->getDBLoadBalancer()
		);
	}

	public function getAssignmentClass() {
		return "\\BlueSpice\\PageAssignments\\Assignment\\User";
	}

	public function getRendererKey() {
		return "assignment-user";
	}

	public function getTypeMessageKey() {
		return "bs-pageassignments-assignee-type-user";
	}

}