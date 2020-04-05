<?php

namespace BlueSpice\CustomMenu\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.custommenu' );
		$this->out->addModuleStyles( 'ext.bluespice.custommenu.styles' );
	}

}
