<?php

namespace BlueSpice\VisualEditorConnector\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\ExtensionAttributeBasedRegistry;

class AddModules extends BeforePageDisplay {

	protected function doProcess() {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceVisualEditorConnectorPluginModules'
		);

		$pluginModules = [];
		foreach( $registry->getAllKeys()  as $key ) {
			$moduleName = $registry->getValue( $key );
			$pluginModules[] = $moduleName;
		}

		$this->out->addModules(
			'ext.bluespice.visualEditorConnector.overrides'
		);
		$this->out->addJsConfigVars( 'bsVECPluginModules', $pluginModules );

		$tagRegistry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceVisualEditorConnectorTagDefinitions'
		);
		$tagDefinitions = [];
		foreach( $tagRegistry->getAllKeys()  as $key ) {
			$moduleName = $tagRegistry->getValue( $key );
			$tagDefinitions[] = $moduleName;
		}

		$this->out->addModules(
			'ext.bluespice.visualEditorConnector.tags'
		);
		$this->out->addJsConfigVars( 'bsVECTagDefinitions', $tagDefinitions );
	}

}