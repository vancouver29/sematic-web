<?php

namespace BlueSpice\SmartList\Panel;

use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Panel\BasePanel;

class YourEdits extends BasePanel implements IPanel {
	protected $params = [];

	public static function factory( $sktemplate, $params ) {
		return new self( $sktemplate, $params );
	}

	public function __construct( $skintemplate, $params ) {
		parent::__construct( $skintemplate );
		$this->params = $params;
	}

	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-smartlist-lastedits' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$count = 5;
		if( isset( $this->params['count'] ) ) {
			$count = (int) $this->params['count'];
		}

		$edits = \SmartList::getYourEditsTitles( $this->getUser(), $count );

		$links = [];
		foreach( $edits as $edit ) {
			$link = [
				'href' => $edit['title']->getFullURL(),
				'text' => $edit['displayText'],
				'title' => $edit['displayText'],
				'classes' => ' bs-usersidebar-internal '
			];
			$links[] = $link;
		}

		$linkListGroup = new \BlueSpice\Calumma\Components\SimpleLinkListGroup( $links );

		return $linkListGroup->getHtml();
	}

	protected function getUser() {
		return $this->skintemplate->getSkin()->getUser();
	}

	protected function getTitle() {
		return $this->skintemplate->getSkin()->getTitle();
	}
}
