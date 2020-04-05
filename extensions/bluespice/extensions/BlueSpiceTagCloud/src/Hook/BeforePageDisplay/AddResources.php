<?php

namespace BlueSpice\TagCloud\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddResources extends BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.tagcloud.text.styles' );
		$this->out->addModules( 'ext.bluespice.tagcloud.canvas3d' );
	}
}
