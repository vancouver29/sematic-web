<?php

namespace BlueSpice\VisualEditorConnector\SimpleFarmer\DynamicConfiguration;

use BlueSpice\SimpleFarmer\DynamicConfigurationBase;

class VirtualRestConfig extends DynamicConfigurationBase {
	protected function doApply() {
		$fullPath = $GLOBALS['wgServer'] . '/' . $this->instanceName;
		$encFullPath = base64_encode( $fullPath );
		$this->globals['wgVirtualRestConfig']['modules']['parsoid']['domain'] = $encFullPath;
	}
}