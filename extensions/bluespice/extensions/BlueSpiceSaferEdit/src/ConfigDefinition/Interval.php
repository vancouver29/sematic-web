<?php

namespace BlueSpice\SaferEdit\ConfigDefinition;

class Interval extends \BlueSpice\ConfigDefinition\IntSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_QUALITY_ASSURANCE . '/BlueSpiceSaferEdit',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceSaferEdit/' . static::FEATURE_QUALITY_ASSURANCE,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceSaferEdit',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-saferedit-pref-interval';
	}

	public function isRLConfigVar() {
		return true;
	}
}
