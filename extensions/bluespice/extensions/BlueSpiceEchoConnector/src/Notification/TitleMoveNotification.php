<?php

namespace BlueSpice\EchoConnector\Notification;

use BlueSpice\BaseNotification;

class TitleMoveNotification extends BaseNotification {
	/**
	 * @var \Title
	 */
	protected $oldTitle;

	/**
	 * @var string
	 */
	protected $reason;

	public function __construct( $agent, $title, $oldTitle, $reason ) {
		parent::__construct( 'bs-move', $agent, $title );

		$this->oldTitle = $oldTitle;
		$this->reason = $reason;
	}

	public function getParams() {
		return [
			'oldtitle' => $this->oldTitle,
			'realname' => $this->getUserRealName(),
			'movereason' => $this->reason
		];
	}
}
