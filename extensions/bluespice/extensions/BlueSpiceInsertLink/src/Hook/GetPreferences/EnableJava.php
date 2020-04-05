<?php

namespace BlueSpice\InsertLink\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class EnableJava extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-insertlink-pref-enablejava'] = array(
			'type' => 'check',
			'label-message' => 'bs-insertlink-pref-enable-java',
			'section' => 'bluespice/insertlink',
		);
		return true;
	}
}
