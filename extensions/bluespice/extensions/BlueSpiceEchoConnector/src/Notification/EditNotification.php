<?php

namespace BlueSpice\EchoConnector\Notification;

use BlueSpice\BaseNotification;

class EditNotification extends BaseNotification {
	/**
	 * @var \Revision
	 */
	protected $revision;

	/**
	 * @var string
	 */
	protected $summary;

	public function __construct( $agent, $title, $revision, $summary ) {
		parent::__construct( 'bs-edit', $agent, $title );
		$this->revision = $revision;
		$this->summary = $summary;
	}

	public function getSecondaryLinks() {
		$diffParams = [];
		if ( is_object( $this->revision ) ) {
			$diffParams[ 'diff' ] = $this->revision->getId();
			if ( is_object( $this->revision->getPrevious() ) ) {
				$diffParams[ 'oldid' ] = $this->revision->getPrevious()->getId();
			}
		}

		$diffUrl = $this->title->getFullURL( [
			'type' => 'revision',
			'diff' => $diffParams['diff'],
			'oldid' => $diffParams['oldid']
		] );

		return [
			'difflink' => $diffUrl
		];
	}

	public function getParams() {
		return [
			'summary' => $this->summary,
			'titlelink' => true,
			'realname' => $this->getUserRealName()
		];
	}
}
