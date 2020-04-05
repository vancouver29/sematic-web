<?php

namespace BlueSpice\Readers\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddContentActionToBlacklist extends SkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'readers' );
		return true;
	}
}