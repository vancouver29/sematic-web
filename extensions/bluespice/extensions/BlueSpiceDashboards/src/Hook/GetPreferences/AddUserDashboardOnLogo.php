<?php

namespace BlueSpice\Dashboards\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddUserDashboardOnLogo extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-dashboards-pref-userdashboardonlogo'] = array(
			'type' => 'toggle',
			'label-message' => 'bs-dashboards-pref-userdashboardonlogo',
			'section' => 'bluespice/dashboards',
		);
		return true;
	}
}
