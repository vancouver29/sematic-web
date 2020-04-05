<?php

namespace BlueSpice\WhoIsOnline\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class OrderBy extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-whoisonline-pref-orderby'] = [
			'type' => 'select',
			'label-message' => 'bs-whoisonline-pref-orderby',
			'section' => 'bluespice/whoisonline',
			'options' => [
				wfMessage( 'bs-whoisonline-pref-orderby-name' )->plain() => 'name',
				wfMessage( 'bs-whoisonline-pref-orderby-time' )->plain() => 'onlinetime',
			]
		];
		return true;
	}
}
