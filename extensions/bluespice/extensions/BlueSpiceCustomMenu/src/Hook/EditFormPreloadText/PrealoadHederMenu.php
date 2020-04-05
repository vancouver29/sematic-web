<?php

namespace BlueSpice\CustomMenu\Hook\EditFormPreloadText;

use BlueSpice\Data\RecordSet;

class PrealoadHederMenu extends \BlueSpice\Hook\EditFormPreloadText {

	protected function skipProcessing() {
		$title = \Title::makeTitle(
			NS_MEDIAWIKI,
			"CustomMenu/Header" // 'TopBarMenu' in the past
		);
		if ( !$this->title || !$this->title->equals( $title ) ) {
			return true;
		}
		if ( $title->exists() ) {
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

		$items = [];
		foreach ( $menu->getData()->getRecords() as $record ) {
			$items[] = $this->recordToLegacyParserItem( $record );
		}

		$this->text = \MenuParser::toWikiText( $items );
		return true;
	}

	protected function recordToLegacyParserItem( $record ) {
		$item = (array)$record->getData();
		if ( !isset( $item['children'] ) || !$item['children'] instanceof RecordSet ) {
			return $item;
		}
		$children = [];
		foreach ( $item['children']->getRecords() as $childRecord ) {
			$children[] = $this->recordToLegacyParserItem( $childRecord );
		}
		$item['children'] = $children;
		return $item;
	}

}
