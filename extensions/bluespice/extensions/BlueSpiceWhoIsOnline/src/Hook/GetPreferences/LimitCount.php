<?php

namespace BlueSpice\WhoIsOnline\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class LimitCount extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-whoisonline-pref-limitcount'] = array(
			'type' => 'int',
			'label-message' => 'bs-whoisonline-pref-limitcount',
			'section' => 'bluespice/whoisonline',
		);
		return true;
	}
}
