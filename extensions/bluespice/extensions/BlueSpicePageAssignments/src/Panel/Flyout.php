<?php

namespace BlueSpice\PageAssignments\Panel;

use BlueSpice\Calumma\Panel\BasePanel;
use BlueSpice\Calumma\IFlyout;

class Flyout extends BasePanel implements IFlyout {

	/**
	 * @return \Message
	 */
	public function getFlyoutTitleMessage() {
		return wfMessage( 'bs-pageassignments-flyout-title' );
	}

	/**
	 * @return \Message
	 */
	public function getFlyoutIntroMessage() {
		return wfMessage( 'bs-pageassignments-flyout-intro' );
	}

	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-pageassignments-nav-link-title-pageassignments' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return '';
	}

	public function getTriggerCallbackFunctionName() {
		return 'bs.pageassignments.flyoutCallback';
	}

	public function getTriggerRLDependencies() {
		return [ 'ext.bluespice.pageassignments.flyout' ];
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return boolean
	 */
	public function shouldRender( $context ) {
		return $context->getUser()->isLoggedIn();
	}
}
