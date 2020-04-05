<?php

namespace BlueSpice\CustomMenu\CustomMenu;

use BlueSpice\Data\RecordSet;
use BlueSpice\Data\Record;
use BlueSpice\Services;

class Header extends \BlueSpice\CustomMenu\CustomMenu {

	/**
	 *
	 * @return Record[]
	 */
	protected function getRecords() {
		$title = \Title::makeTitle(
			NS_MEDIAWIKI,
			"CustomMenu/Header" // 'TopBarMenu' in the past
		);

		if ( $title && $title->exists() ) {
			$menu = \MenuParser::getNavigationSites( $title );
			$records = [];
			foreach ( $menu as $entry ) {
				$records[] = $this->legacyParserItemToRecord( $entry );
			}
			return $records;
		}
		return $this->getDefaultRecords();
	}

	/**
	 * @return Menu
	 */
	public function getRenderer() {
		return Services::getInstance()->getBSRendererFactory()->get(
			'custommenuheader',
			$this->getParams()
		);
	}

	/**
	 *
	 * @param Record[] $records
	 * @return Record[]
	 */
	protected function getDefaultRecords( $records = [] ) {
		$currentTitle = \RequestContext::getMain()->getTitle();
		$mainPage = \Title::newMainPage();
		$menu = [ [
			'id' => 'nt-wiki',
			'href' => $mainPage->getFullURL(),
			'text' => $this->config->get( 'Sitename' ),
			'active' => $currentTitle->equals( $mainPage ),
			'level' => 1,
			'containsactive' => false,
			'external' => false,
			'children' => [],
		] ];
		// legacy hook
		\Hooks::run( 'BSTopMenuBarCustomizerRegisterNavigationSites', [
			&$menu
		] );

		foreach ( $menu as $entry ) {
			$records[] = $this->legacyParserItemToRecord( $entry );
		}
		return parent::getDefaultRecords( $records );
	}

	protected function legacyParserItemToRecord( $entry ) {
		if ( !empty( $entry['children'] ) ) {
			$children = [];
			foreach ( $entry['children'] as $child ) {
				$children[] = $this->legacyParserItemToRecord( $child );
			}
			$entry['children'] = new RecordSet( $children );
		}

		return new Record( (object)$entry );
	}

	public function numberOfLevels() {
		return $this->config->get( 'CustomMenuHeaderNumberOfLevels' );
	}

	public function numberOfMainEntries() {
		return $this->config->get( 'CustomMenuHeaderNumberOfMainEntries' );
	}

	public function numberOfSubEntries() {
		return $this->config->get( 'CustomMenuHeaderNumberOfSubEntries' );
	}

}
