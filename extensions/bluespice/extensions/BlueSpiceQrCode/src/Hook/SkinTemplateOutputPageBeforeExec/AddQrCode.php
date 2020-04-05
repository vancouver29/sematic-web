<?php

namespace BlueSpice\QrCode\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\QrCode\Panel\Panel;

class AddQrCode extends SkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		$title = $this->skin->getTitle();
		if ( $title instanceof \Title == false || $title->exists() === false ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->mergeSkinDataArray(
			SkinData::PAGE_TOOLS_PANEL,
			[
				'qrcode' => [
					'position' => 100,
					'callback' => function( $sktemplate ) {
						return new Panel( $sktemplate );
					}
				]
			]
		);

		return true;
	}
}
