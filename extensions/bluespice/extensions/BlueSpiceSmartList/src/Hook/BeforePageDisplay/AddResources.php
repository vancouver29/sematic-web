<?php

namespace BlueSpice\SmartList\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.smartlist' );
		return true;
	}

}
