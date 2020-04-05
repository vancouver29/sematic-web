<?php

namespace BlueSpice\ExtendedStatistics\ConfigDefinition;

class ExcludeUsers extends \BlueSpice\ConfigDefinition\ArraySetting {

	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_DATA_ANALYSIS . '/BlueSpiceExtendedStatistics',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceExtendedStatistics/' . static::FEATURE_DATA_ANALYSIS ,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/BlueSpiceExtendedStatistics',
		];
	}

	public function getLabelMessageKey() {
		return 'bs-statistics-pref-excludeusers';
	}

	public function getHtmlFormField() {
		return new \HTMLMultiSelectPlusAdd( $this->makeFormFieldParams() );
	}
}
