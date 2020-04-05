<?php

namespace BlueSpice\EchoConnector\Notification;

use BlueSpice\BaseNotification;

class CreateNotification extends BaseNotification {
	/**
	 * @var string
	 */
	protected $summary;

	public function __construct( $agent, $title = null, $summary ) {
		parent::__construct( 'bs-create', $agent, $title );
		$this->summary = $summary;
	}

	public function getParams() {
		return [
			'summary' => $this->summary,
			'realname' => $this->getUserRealName()
		];
	}
}
