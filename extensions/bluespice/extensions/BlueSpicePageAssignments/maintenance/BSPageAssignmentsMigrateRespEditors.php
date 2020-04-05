<?php
$extDir = dirname( dirname( __DIR__ ) );

require_once( "$extDir/BlueSpiceFoundation/maintenance/BSMaintenance.php" );

class BSPageAssignmentsMigrateRespEditors extends LoggedUpdateMaintenance {
	protected function doDBUpdates() {
		$aRespEditors = $this->getResponsibleEditors();
		$this->output( "BSPageAssignments: Migrate Responsible Editors..." );
		if( empty($aRespEditors) ) {
			$this->output( "OK\n" );
			return;
		}

		$iRespEditors = count( $aRespEditors );
		$this->output( "($iRespEditors)..." );
		foreach( $aRespEditors as $aRespEditor ) {
			$this->insertAssignment( $aRespEditor );
		}

		$this->output( "OK\n" );
	}

	protected function getUpdateKey() {
		return 'bs-pageassignments-migrate-responsible-editors';
	}

	protected function getResponsibleEditors( $aReturn = array() ) {
		$aOptions = array(
			'LIMIT' => 99999,
		);
		$oRes = $this->getDB( DB_REPLICA )->select(
			'bs_responsible_editors',
			'*',
			array(),
			__METHOD__,
			$aOptions
		);
		foreach( $oRes as $oRow ) {
			if( !$oUser = User::newFromId( $oRow->re_user_id ) ) {
				continue;
			}
			if( !$title = \Title::newFromId( (int)$oRow->re_user_id ) ) {
				continue;
			}
			if( !$title->exists() ) {
				continue;
			}
			$assignment = [
				'pa_assignee_key' => $oUser->getName(),
				'pa_page_id' => (int) $oRow->re_page_id,
				'pa_assignee_type' => 'user',
			];
			if( $this->assignmentExists( $assignment ) !== false ) {
				continue;
			}
			$aReturn[] = $assignment;
		}
		return $aReturn;
	}

	protected function insertAssignment( $aRespEditor ) {
		return $this->getDB( DB_MASTER )->insert(
			'bs_pageassignments',
			$aRespEditor,
			__METHOD__
		);
	}

	protected function assignmentExists( $conds ) {
		return $this->getDB( DB_MASTER )->selectRow(
			'bs_pageassignments',
			'*',
			$conds,
			__METHOD__
		);
	}

}