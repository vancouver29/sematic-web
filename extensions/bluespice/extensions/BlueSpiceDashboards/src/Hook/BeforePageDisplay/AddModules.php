<?php

namespace BlueSpice\Dashboards\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.dashboards' );
		$this->out->addModuleStyles( 'ext.bluespice.dashboards.styles' );
		return true;
	}

}
