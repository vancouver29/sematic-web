<?php

namespace BlueSpice\PageAssignments\Notifications;

use BlueSpice\BaseNotification;

class GroupsAdd extends BaseNotification {
	/**
	 * @var array
	 */
	protected $groupsAdded;

	public function __construct( $agent, $user, $groupsAdded ) {
		$pageAssignmentsSpecial = \SpecialPage::getTitleFor( 'PageAssignments' );
		parent::__construct( 'bs-pageassignments-user-group-add', $agent, $pageAssignmentsSpecial );

		$this->addAffectedUsers( [ $user ] );
		$this->groupsAdded = $groupsAdded;
		if( $user->getId() == $agent->getId() ) {
			$this->setNotifyAgent( true );
		}
	}

	public function getParams() {
		return [
			'group' => implode( ', ', $this->groupsAdded ),
			'groupcount' => count( $this->groupsAdded )
		];
	}

	protected function setNotifyAgent( $notify ) {
		$this->extra['notifyAgent'] = $notify;
	}
}
