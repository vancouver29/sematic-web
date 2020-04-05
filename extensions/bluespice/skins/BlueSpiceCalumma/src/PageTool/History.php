<?php

namespace BlueSpice\Calumma\PageTool;

use BlueSpice\PageTool\IconBase;

class History extends IconBase {

	/**
	 *
	 * @return string
	 */
	protected function getIconClass() {
		return 'bs-icon-history';
	}

	/**
	 *
	 * @return \Message
	 */
	protected function getToolTip() {
		return new \Message( 'bs-calumma-pagetool-history-tooltip' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getUrl() {
		$url = $this->getTitle()->getLocalURL( [ 'action' => 'history' ] );
		return $url;
	}

	protected function skipProcessing() {
		if ( $this->getTitle()->isSpecialPage() ) {
			return true;
		}

		$action = $this->context->getRequest()->getVal( 'action', 'view' );
		return $action === 'history';
	}

}
