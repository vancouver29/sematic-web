<?php

namespace BlueSpice\UserSidebar\Panel;

use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Panel\BasePanel;

class CollapsibleLinks extends BasePanel implements IPanel {
	protected $section;
	protected $links = [];

	public function __construct( $skintemplate, $section, $links ) {
		parent::__construct( $skintemplate );
		$this->section = $section;
		$this->links = $links;
	}

	/**
	 * @return string
	 */
	public function getTitleMessage() {
		return $this->section;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$linkListGroup = new \BlueSpice\Calumma\Components\SimpleLinkListGroup( $this->links );

		return $linkListGroup->getHtml();
	}
}
