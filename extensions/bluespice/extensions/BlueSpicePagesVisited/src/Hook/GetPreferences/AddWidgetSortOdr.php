<?php

namespace BlueSpice\PagesVisited\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

class AddWidgetSortOdr extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-pagesvisited-widgetsortodr'] = array(
			'type' => 'radio',
			'section' => 'bluespice/pagesvisited',
			'label-message' => 'bs-pagesvisited-pref-widgetsortodr',
			'options' => array(
				wfMessage( 'bs-pagesvisited-pref-sort-time' )->plain() => 'time',
				wfMessage( 'bs-pagesvisited-pref-sort-pagename' )->plain() => 'pagename'
				)
			);
		return true;
	}
}
