<?php

namespace BlueSpice\WatchList\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class WidgetLimit extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-watchlist-pref-widgetlimit'] = array(
			'type' => 'int',
			'label-message' => 'bs-watchlist-pref-widgetlimit',
			'section' => 'bluespice/watchlist',
		);
		return true;
	}
}
