<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\SkinData;

class Export extends BasePanel {

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitetools-panel-section-export-title' );
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$linkDefs = $this->skintemplate->get( SkinData::EXPORT_MENU );
		$this->maybeAddPrint( $linkDefs );

		$links = new LinkList( 'dummy-id', [
			'content' => $linkDefs
		] );
		return $links->getBody();
	}

	/**
	 *
	 * @param type &$linkDefs
	 * @return null
	 */
	protected function maybeAddPrint( &$linkDefs ) {
		foreach ( $linkDefs as $linkDef ) {
			// Allow overriding
			if ( $linkDef['id'] === 'bs-em-print' ) {
				return;
			}
		}

		$linkDefs[] = [
			'id' => 'bs-em-print',
			'href' => $this->skintemplate->getSkin()
				->getTitle()->getLocalURL( [ 'printable' => 'yes' ] ),
			'title' => wfMessage( 'bs-export-menu-print-title' )->text(),
			'text' => wfMessage( 'bs-export-menu-print-text' )->text(),
			'class' => 'bs-ue-export-link',
			'iconClass' => 'icon-print'
		];
	}

}
