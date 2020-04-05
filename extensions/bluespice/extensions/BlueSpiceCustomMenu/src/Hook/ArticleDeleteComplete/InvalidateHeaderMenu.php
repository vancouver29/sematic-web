<?php

namespace BlueSpice\CustomMenu\Hook\ArticleDeleteComplete;

class InvalidateHeaderMenu extends \BlueSpice\Hook\ArticleDeleteComplete {

	protected function skipProcessing() {
		$title = \Title::makeTitle(
			NS_MEDIAWIKI,
			"CustomMenu/Header" // 'TopBarMenu' in the past
		);
		if ( !$this->wikipage->getTitle()->equals( $title ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$menu = $this->getServices()->getService( 'BSCustomMenuFactory' )
			->getMenu( 'header' );
		if ( !$menu ) {
			return true;
		}
		$menu->invalidate();
		return true;
	}

}
