<?php

namespace BlueSpice\WatchList\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class WidgetSortOdr extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-watchlist-pref-widgetsortodr'] = [
			'type' => 'select',
			'label-message' => 'bs-watchlist-pref-widgetsortodr',
			'section' => 'bluespice/watchlist',
			'options' => [
				wfMessage( 'bs-watchlist-pref-sort-time' )->plain() => 'time',
				wfMessage( 'bs-watchlist-pref-sort-title' )->plain() => 'pagename',
			]
		];
		return true;
	}
}
