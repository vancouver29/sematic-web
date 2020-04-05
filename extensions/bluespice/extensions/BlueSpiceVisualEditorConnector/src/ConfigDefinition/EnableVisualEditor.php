<?php

namespace BlueSpice\VisualEditorConnector\ConfigDefinition;

class EnableVisualEditor extends \BlueSpice\ConfigDefinition\BooleanSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EDITOR . '/BlueSpiceVisualEditor',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceVisualEditor/' . static::FEATURE_EDITOR ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceVisualEditor',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-visualeditorconnector-enable-visualeditor';
	}

	public function isRLConfigVar() {
		return true;
	}
}
