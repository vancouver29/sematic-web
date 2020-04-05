<?php

namespace BlueSpice\PagesVisited\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddWidgetLimit extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-pagesvisited-widgetlimit'] = array(
			'type' => 'int',
			'label-message' => 'bs-pagesvisited-pref-widgetlimit',
			'section' => 'bluespice/pagesvisited',
		);
		return true;
	}
}
