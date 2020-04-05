<?php

namespace BlueSpice\Calumma\PageTool;

use BlueSpice\PageTool\IconBase;

class View extends IconBase {

	/**
	 *
	 * @return string
	 */
	protected function getIconClass() {
		return 'bs-icon-text';
	}

	/**
	 *
	 * @return \Message
	 */
	protected function getToolTip() {
		return new \Message( 'bs-calumma-pagetool-view-tooltip' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getUrl() {
		return $this->getTitle()->getSubjectPage()->getLocalURL();
	}

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->getTitle()->isSpecialPage() ) {
			return true;
		}

		$action = $this->context->getRequest()->getVal( 'action', 'view' );
		if ( !$this->getTitle()->isTalkPage() && in_array( $action, [ 'view', 'submit' ] ) ) {
			return true;
		}

		return false;
	}

}
