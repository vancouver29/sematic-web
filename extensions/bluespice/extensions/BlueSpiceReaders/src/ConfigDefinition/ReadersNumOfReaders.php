<?php

namespace BlueSpice\Readers\ConfigDefinition;

use BlueSpice\ConfigDefinition\IntSetting;

class ReadersNumOfReaders extends IntSetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_PERSONALISATION . '/BlueSpiceReaders',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceReaders/' . static::FEATURE_PERSONALISATION ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceReaders',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-readers-pref-numofreaders';
	}

}
