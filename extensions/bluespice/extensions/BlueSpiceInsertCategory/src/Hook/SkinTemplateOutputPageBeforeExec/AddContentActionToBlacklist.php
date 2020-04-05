<?php

namespace BlueSpice\InsertCategory\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddContentActionToBlacklist extends SkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$this->appendSkinDataArray( SkinData::EDIT_MENU_BLACKLIST, 'insert_category' );
		return true;
	}
}