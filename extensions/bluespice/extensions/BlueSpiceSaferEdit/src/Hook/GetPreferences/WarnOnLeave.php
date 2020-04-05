<?php

namespace BlueSpice\SaferEdit\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class WarnOnLeave extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-saferedit-pref-warnonleave'] = [
			'type' => 'check',
			'label-message' => 'bs-saferedit-pref-warnonleave',
			'section' => 'bluespice/saferedit',
		];
		return true;
	}
}
