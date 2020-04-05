<?php

namespace BlueSpice\WatchList\Panel;

use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Panel\BasePanel;

class WatchList extends BasePanel implements IPanel {
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
		return wfMessage( 'bs-watchlist-title-sidebar' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$watchlistTitles = $this->getWatchlistTitles();
		$links = [];
		foreach( $watchlistTitles as $watchlistTitle ) {
			$link = [
				'href' => $watchlistTitle['title']->getFullURL(),
				'text' => $watchlistTitle['displayText'],
				'title' => $watchlistTitle['displayText'],
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

	protected function getWatchlistTitles() {
		$watchlist = [];

		$maxLength = 30;
		if( isset( $this->params['maxtitlelength'] ) ) {
			$maxLength = (int) $this->params['maxtitlelength'];
		}

		$count = $this->getUser()->getOption( 'bs-watchlist-pref-widgetlimit' );
		if( isset( $this->params['count'] ) ) {
			$count = (int) $this->params['count'];
		}

		$options = [];
		if( isset( $this->params['order'] ) && $this->params['order'] == 'pagename' ) {
			$options['ORDER BY'] = 'wl_title';
		}
		$options['LIMIT'] = $count;

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'watchlist',
			array( 'wl_namespace', 'wl_title' ),
			array(
				'wl_user' => $this->getUser()->getId(),
				'NOT wl_notificationtimestamp' => NULL
			),
			__METHOD__,
			$options
		);

		foreach ( $res as $row ) {
			$watchedTitle = \Title::newFromText( $row->wl_title, $row->wl_namespace );
			if( $watchedTitle instanceof \Title === false
				|| $watchedTitle->exists() == false
				|| $watchedTitle->userCan('read' ) === false ) {
				continue;
			}

			$displayText = \BsStringHelper::shorten(
				$watchedTitle->getPrefixedText(),
				array( 'max-length' => $maxLength, 'position' => 'middle' )
			);

			$watchlist[] = [
				'title' => $watchedTitle,
				'displayText' => $displayText
			];
		}

		return $watchlist;
	}
}
