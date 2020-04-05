<?php

namespace BlueSpice\PagesVisited\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;
use BlueSpice\Html\FormField\NamespaceMultiselect;

class AddWidgetNS extends GetPreferences {
	protected function doProcess() {
		$this->preferences['bs-pagesvisited-widgetns'] = [
			'class' => NamespaceMultiselect::class,
			'label-message' => 'bs-pagesvisited-pref-widgetns',
			'section' => 'bluespice/pagesvisited',
			NamespaceMultiselect::OPTION_BLACKLIST => [
				NS_MEDIAWIKI, NS_MEDIAWIKI_TALK
			]
		];
		return true;
	}
}
