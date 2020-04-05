<?php

namespace BlueSpice\PageAssignments\Notifications;

use BlueSpice\BaseNotification;

class GroupsRemove extends BaseNotification {
	/**
	 * @var array
	 */
	protected $groupsRemoved;

	public function __construct( $agent, $user, $groupsRemoved ) {
		$pageAssignmentsSpecial = \SpecialPage::getTitleFor( 'PageAssignments' );
		parent::__construct( 'bs-pageassignments-user-group-remove', $agent, $pageAssignmentsSpecial );

		$this->addAffectedUsers( [ $user ] );
		$this->groupsRemoved = $groupsRemoved;
		if( $user->getId() == $agent->getId() ) {
			$this->setNotifyAgent( true );
		}
	}

	public function getParams() {
		return [
			'group' => implode( ', ', $this->groupsRemoved ),
			'groupcount' => count( $this->groupsRemoved )
		];
	}

	protected function setNotifyAgent( $notify ) {
		$this->extra['notifyAgent'] = $notify;
	}
}
