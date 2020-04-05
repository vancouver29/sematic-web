<?php

namespace BlueSpice\PageAssignments\Hook\UserCan;

class RemoveEditPermissionsOnSecure extends \BlueSpice\Hook\UserCan {
	protected function skipProcessing() {
		if( $this->title->getNamespace() < 0 ) {
			return true;
		}

		if( $this->title->isTalkPage() ) {
			return true;
		}

		if( !$this->title->exists() ) {
			return true;
		}

		$rightList = $this->getConfig()->get(
			'PageAssignmentsSecureRemoveRightList'
		);
		if( !in_array( $this->action, $rightList )  ) {
			return true;
		}

		$enabledNs = $this->getConfig()->get(
			'PageAssignmentsSecureEnabledNamespaces'
		);
		if( !in_array( $this->title->getNamespace(), $enabledNs ) ) {
			return true;
		}

		$factory = $this->getServices()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$factory instanceof \BlueSpice\PageAssignments\AssignmentFactory;
		if( !$target = $factory->newFromTargetTitle( $this->title ) ) {
			return true;
		}

		$userId = $this->getContext()->getUser()->getId();
		if( in_array( $userId, $target->getAssignedUserIDs() ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->result = false;
		return false;
	}

}
