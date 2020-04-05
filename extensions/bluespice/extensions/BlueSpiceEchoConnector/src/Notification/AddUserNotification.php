<?php

namespace BlueSpice\EchoConnector\Notification;

use BlueSpice\BaseNotification;

class AddUserNotification extends BaseNotification {
	/**
	 * @var \User
	 */
	protected $createdUser;

	public function __construct( $agent, $createdUser ) {
		parent::__construct( 'bs-adduser', $agent, $createdUser->getUserPage() );
		$this->createdUser = $createdUser;
	}

	public function getParams() {
		return [
			'realname' => $this->getRealNameText(),
			'user' => $this->createdUser
		];
	}

	public function getSecondaryLinks() {
		return [
			'performer' => [
				'url' => $this->agent->getUserPage()->getFullURL(),
				'label-params' => [ $this->getUserRealName() ]
			]
		];
	}

	protected function getRealNameText() {
		$realname = $this->getUserRealName( $this->createdUser );

		if ( $realname !== $this->createdUser->getName() ) {
			$realname = wfMessage(
				'bs-notifications-param-realname-with-username',
				$realname,
				$this->createdUser->getName()
			)->plain();
		}
		return $realname;
	}
}
