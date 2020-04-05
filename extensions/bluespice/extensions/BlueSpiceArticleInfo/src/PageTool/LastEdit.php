<?php

namespace BlueSpice\ArticleInfo\PageTool;

use BlueSpice\PageTool\Base;
use MediaWiki\MediaWikiServices;
use MediaWiki\Linker\LinkRenderer;
use WikiPage;

class LastEdit extends Base {

	/**
	 * @var LinkRenderer
	 */
	protected $linkRenderer = null;

	/**
	 *
	 * @var \Revision
	 */
	protected $currentRevision = null;

	/**
	 * @return string
	 */
	protected function doGetHtml() {
		$this->linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$wikiPage = WikiPage::factory( $this->getTitle() );
		$this->currentRevision = $wikiPage->getRevision();
		if ( $this->currentRevision instanceof \Revision === false ) {
			return '';
		}

		$diffLink = $this->getDiffLink();
		$userPageLink = $this->getUserPageLink();

		return "<span class=\"page-tool-text\">$diffLink - $userPageLink</span>";
	}

	/**
	 *
	 * @return int
	 */
	public function getPosition() {
		return 50;
	}

	private function getDiffLink() {
		$rawTimestamp = $this->currentRevision->getTimestamp();
		$formattedDate = $this->context->getLanguage()->date( $rawTimestamp );

		$unixTS = wfTimestamp( TS_UNIX, $rawTimestamp );
		$period = time() - $unixTS;

		$chosenIntervals = [ 'years', 'days', 'hours', 'minutes' ];
		if ( $period < 60 ) {
			$chosenIntervals[] = 'seconds';
		}
		$formattedPeriod = $this->context->getLanguage()->formatDuration( $period, $chosenIntervals );

		return $this->linkRenderer->makeLink(
			$this->getTitle(),
			$formattedPeriod,
			[
				'title' => $formattedDate
			],
			[
				'oldid' => $this->currentRevision->getId(),
				'diff' => 'prev'
			]
		);
	}

	private function getUserPageLink() {
		$revisionUser = \User::newFromId( $this->currentRevision->getUser() );
		$link = $this->linkRenderer->makeLink(
			$revisionUser->getUserPage(),
			$revisionUser->getName()
		);

		return $link;
	}

}
