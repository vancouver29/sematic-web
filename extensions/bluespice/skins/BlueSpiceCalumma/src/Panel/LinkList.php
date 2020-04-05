<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\SkinDataPanel;
use BlueSpice\Calumma\Components\SimpleLinkListGroup;

class LinkList extends SkinDataPanel {

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$linkListGroup = new SimpleLinkListGroup( $this->definition['content'] );
		return $linkListGroup->getHtml();
	}
}
