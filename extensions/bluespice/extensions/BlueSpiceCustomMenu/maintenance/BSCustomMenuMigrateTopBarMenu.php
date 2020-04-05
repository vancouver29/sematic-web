<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once "$IP/maintenance/Maintenance.php";

class BSCustomMenuMigrateTopBarMenu extends LoggedUpdateMaintenance {

	protected function noDataToMigrate() {
		$oldTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			"TopBarMenu"
		);
		if ( !$oldTitle || !$oldTitle->exists() ) {
			return true;
		}
		$newTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			"CustomMenu/Header" // 'TopBarMenu' in the past
		);
		if ( $newTitle && $newTitle->exists() ) {
			return true;
		}
		return false;
	}

	protected function doDBUpdates() {
		if ( $this->noDataToMigrate() ) {
			$this->output( "TopBarMenu -> No data to migrate\n" );
			return true;
		}
		$this->output( "...TopBarMenu -> migration...\n" );

		$oldTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			"TopBarMenu"
		);
		$newTitle = \Title::makeTitle(
			NS_MEDIAWIKI,
			"CustomMenu/Header" // 'TopBarMenu' in the past
		);
		try{
			$move = new \MovePage( $oldTitle, $newTitle );
			$move->move(
				$this->getMaintenanceUser(),
				"TopMenuBarCustomizer => CustomMenu",
				false
			);
		} catch ( \Exception $e ) {
			$this->output( $e->getMessage() );
		}
		$this->output( "\n" );

		return true;
	}

	protected function getMaintenanceUser() {
		return \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getMaintenanceUser()->getUser();
	}

	protected function getUpdateKey() {
		return 'TopBarMenu';
	}

}
