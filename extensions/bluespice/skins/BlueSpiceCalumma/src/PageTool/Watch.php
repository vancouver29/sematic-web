<?php

namespace BlueSpice\Calumma\PageTool;

use BlueSpice\PageTool\IconBase;

class Watch extends IconBase {

	/**
	 *
	 * @return string
	 */
	protected function getIconClass() {
		if ( $this->isAlreadyWatched() ) {
			return 'bs-icon-star-full';
		}
		return 'bs-icon-star-empty';
	}

	/**
	 *
	 * @return \Message
	 */
	protected function getToolTip() {
		if ( $this->isAlreadyWatched() ) {
			new \Message( 'bs-calumma-pagetool-unwatch-tooltip' );
		}
		return new \Message( 'bs-calumma-pagetool-watch-tooltip' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getUrl() {
		$action = 'watch';
		if ( $this->isAlreadyWatched() ) {
			$action = 'unwatch';
		}
		$url = $this->getTitle()->getLocalURL( [ 'action' => $action ] );
		return $url;
	}

	private function isAlreadyWatched() {
		return $this->getUser()->isWatched( $this->getTitle() );
	}

	protected function skipProcessing() {
		return $this->getTitle()->isWatchable() === false;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtml() {
		$icon = parent::getHtml();
		$id = 'ca-watch';
		if ( $this->isAlreadyWatched() ) {
			$id = 'ca-unwatch';
		}
		return "<span id=\"$id\">$icon</span>";
	}

}
