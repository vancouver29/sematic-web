<?php

namespace BlueSpice\Authors\Hook\BeforePageDisplay;

class AddModules extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.authors.flyout.styles' );
		return true;
	}

}
