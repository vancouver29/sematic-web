<?php

namespace BlueSpice\PageAssignments\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\PageAssignments\Panel\Flyout;

class AddFlyout extends SkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		if( $this->skin->getTitle()->exists() === false ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {

		$this->mergeSkinDataArray(
			SkinData::PAGE_DOCUMENTS_PANEL,
			[
				'pageassignments' => [
					'position' => 20,
					'callback' => function( $sktemplate ) {
						return new Flyout( $sktemplate );
					}
				]
			]
		);

		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'pageassignments' );

		return true;
	}
}
