<?php

namespace BlueSpice\ArticleInfo\PageTool;

use BlueSpice\PageTool\IconBase;

class Subpages extends IconBase {

	/**
	 *
	 * @return string
	 */
	protected function getIconClass() {
		return 'icon-tree';
	}

	/**
	 *
	 * @return \Message
	 */
	protected function getToolTip() {
		return new \Message( 'bs-articleinfo-pagetool-subpages-tooltip' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getUrl() {
		return '#';
	}

	/**
	 *
	 * @return string[]
	 */
	protected function getClasses() {
		$classes = parent::getClasses();
		$classes[] = 'bs-articleinfo-pagetool-subpages';
		return $classes;
	}

	/**
	 *
	 * @return int
	 */
	public function getPosition() {
		return 50;
	}

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->getTitle()->hasSubpages() === false;
	}

}
