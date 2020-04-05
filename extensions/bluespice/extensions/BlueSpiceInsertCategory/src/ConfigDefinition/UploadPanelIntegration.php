<?php

namespace BlueSpice\InsertCategory\ConfigDefinition;

class UploadPanelIntegration extends \BlueSpice\ConfigDefinition\BooleanSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_CONTENT_STRUCTURING . '/BlueSpiceInsertCategory',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceInsertCategory/' . static::FEATURE_CONTENT_STRUCTURING,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceInsertCategory',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-insertcategory-pref-uploadpanelintegration';
	}

	public function isRLConfigVar() {
		return true;
	}

}
