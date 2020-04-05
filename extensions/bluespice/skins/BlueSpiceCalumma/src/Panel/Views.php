<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\SkinData;

class Views extends StandardSkinDataLinkList {

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return new \Message( 'bs-sitetools-views' );
	}

	/**
	 *
	 * @param string $linkKey
	 * @return bool
	 */
	protected function skipLink( $linkKey ) {
		$blacklist = $this->skintemplate->data[SkinData::VIEW_MENU_BLACKLIST];
		return in_array( $linkKey, $blacklist );
	}

	/**
	 *
	 * @return array
	 */
	protected function getStandardSkinDataLinkListDefinition() {
		$contentNavigation = $this->skintemplate->get( 'content_navigation' );
		return $contentNavigation['namespaces'] + $contentNavigation['views'];
	}

}
