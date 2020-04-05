<?php

namespace BlueSpice\EchoConnector\PresentationModel;

use BlueSpice\EchoConnector\EchoEventPresentationModel;

class MovePresentationModel extends EchoEventPresentationModel {
	/**
	 * Gets appropriate messages keys and params
	 * for header message
	 *
	 * @return array
	 */
	public function getHeaderMessageContent() {
		$bundleKey = '';
		$bundleParams = [];

		$headerKey = 'bs-notifications-move';
		$headerParams = [ 'oldtitle' ];

		if ( $this->distributionType == 'email' ) {
			$headerKey = 'bs-notifications-email-move-subject';
			$headerParams = [ 'oldtitle', 'agent', 'title', 'realname' ];
		}

		return [
			'key' => $headerKey,
			'params' => $headerParams,
			'bundle-key' => $bundleKey,
			'bundle-params' => $bundleParams
		];
	}

	/**
	 * Gets appropriate message key and params for
	 * web notification message
	 *
	 * @return array
	 */
	public function getBodyMessageContent() {
		$bodyKey = 'bs-notifications-web-move-body';
		$bodyParams = [ 'oldtitle', 'agent', 'title', 'realname' ];

		if ( $this->distributionType == 'email' ) {
			$bodyKey = 'bs-notifications-email-move-body';
			$bodyParams = [ 'oldtitle', 'agent', 'title', 'realname', 'movereason' ];
		}

		return [
			'key' => $bodyKey,
			'params' => $bodyParams
		];
	}

	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			// For the bundle, we don't need secondary actions
			return [];
		}

		return [ $this->getAgentLink() ];
	}

	public function getIcon() {
		return 'edit';
	}
}
