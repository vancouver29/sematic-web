<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\SkinData;

class Edit extends StandardSkinDataLinkList {

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return new \Message( 'bs-sitetools-edit' );
	}

	/**
	 *
	 * @param string $linkKey
	 * @return bool
	 */
	protected function skipLink( $linkKey ) {
		$blacklist = $this->skintemplate->data[SkinData::EDIT_MENU_BLACKLIST];
		return in_array( $linkKey, $blacklist );
	}

	/**
	 *
	 * @return array
	 */
	protected function getStandardSkinDataLinkListDefinition() {
		$contentNavigation = $this->skintemplate->get( 'content_navigation' );
		$editmenu = $this->skintemplate->get( SkinData::EDIT_MENU );

		$combinedDefs = $contentNavigation['actions'] + $editmenu;
		return $combinedDefs;
	}

}
