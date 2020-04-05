<?php

namespace BlueSpice\Dashboards\Hook\LoadExtensionSchemaUpdates;

use BlueSpice\Hook\LoadExtensionSchemaUpdates;

class AddClearConfigPostDatabaseUpdateMaintenance extends LoadExtensionSchemaUpdates {

	protected function doProcess() {

		$this->updater->addPostDatabaseUpdateMaintenance(
			\BSDashBoardsClearConfigMaintenance::class
		);
	}

}
