<?php

namespace BlueSpice\ArticleInfo\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\ArticleInfo\Panel\Flyout;

class AddFlyout extends SkinTemplateOutputPageBeforeExec {
	protected function skipProcessing() {
		$title = $this->skin->getSkin()->getTitle();
		if ( !$title instanceof \Title
				|| !$title->exists()
				|| ( $title->getNamespace() === NS_SPECIAL )
				|| ( $title->getNamespace() === NS_MEDIA )
			) {
			return true;
		}
		if ( $title->userCan( 'read' ) === false ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->mergeSkinDataArray(
			SkinData::PAGE_DOCUMENTS_PANEL,
			[
				'articleinfo' => [
					'position' => 5,
					'callback' => function ( $sktemplate ) {
						return new Flyout( $sktemplate );
					}
				]
			]
		);

		return true;
	}
}
