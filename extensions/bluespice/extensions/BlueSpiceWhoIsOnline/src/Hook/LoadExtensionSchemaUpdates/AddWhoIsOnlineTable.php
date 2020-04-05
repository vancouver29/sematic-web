<?php

namespace BlueSpice\WhoIsOnline\Hook\LoadExtensionSchemaUpdates;

class AddWhoIsOnlineTable extends \BlueSpice\Hook\LoadExtensionSchemaUpdates {

	protected function doProcess() {
		$dir = $this->getExtensionPath();

		$this->updater->addExtensionTable(
			'bs_whoisonline',
			"$dir/maintenance/db/bs_whoisonline.sql"
		);

		$this->updater->addExtensionField(
			'bs_whoisonline',
			'wo_action',
			"$dir/maintenance/db/bs_whoisonline.patch.wo_action.sql"
		);

		$this->updater->modifyExtensionField(
			'bs_whoisonline',
			'wo_timestamp',
			"$dir/maintenance/db/bs_whoisonline.patch.wo_timestamp.sql"
		);

		$this->updater->addExtensionIndex(
			'bs_whoisonline',
			'wo_user_id',
			"$dir/maintenance/db/bs_whoisonline.patch.wo_user_id.index.sql"
		);

		$this->updater->addExtensionIndex(
			'bs_whoisonline',
			'wo_page_namespace',
			"$dir/maintenance/db/bs_whoisonline.patch.wo_page_namespace.index.sql"
		);

		$this->updater->addExtensionIndex(
			'bs_whoisonline',
			'wo_timestamp',
			"$dir/maintenance/db/bs_whoisonline.patch.wo_timestamp.index.sql"
		);
	}

	protected function getExtensionPath() {
		return dirname( dirname( dirname( __DIR__ ) ) );
	}

}
