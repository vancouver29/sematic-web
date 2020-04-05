<?php

use BlueSpice\Tests\BSApiTasksTestBase;

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceGroupManager
 */
class BSApiTasksGroupManagerTest extends  BSApiTasksTestBase {

	protected function getModuleName() {
		return 'bs-groupmanager';
	}

	function getTokens() {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	public function testAddGroup() {
		global $wgAdditionalGroups;

		$aGroupsToAdd = array( 'DummyGroup', 'DummyGroup2', 'DummyGroup3' );
		foreach( $aGroupsToAdd as $sGroup ) {
			$oData = $this->addGroup( $sGroup );
		}

		$this->assertTrue( $oData->success );
		$this->assertTrue( isset( $wgAdditionalGroups['DummyGroup'] ) );
		$this->assertTrue( isset( $wgAdditionalGroups['DummyGroup2'] ) );
		$this->assertTrue( isset( $wgAdditionalGroups['DummyGroup3'] ) );

	}

	public function testEditGroup() {
		global $wgAdditionalGroups, $wgGroupPermissions;

		$wgGroupPermissions['DummyGroup'] = array();

		$oData = $this->executeTask(
			'editGroup',
			array(
				'group' => 'DummyGroup',
				'newGroup' => 'FakeGroup'
			)
		);

		$this->assertTrue( isset( $wgAdditionalGroups['FakeGroup'] ) );
		$this->assertTrue( $wgAdditionalGroups['FakeGroup'] );
		$this->assertFalse( $wgAdditionalGroups['DummyGroup'] );
}

	public function testRemoveGroup() {
		global $wgAdditionalGroups;

		$oData = $this->executeTask(
			'removeGroup',
			array(
				'group' => 'FakeGroup'
			)
		);

		$this->assertTrue( $oData->success );
		$this->assertFalse( $wgAdditionalGroups['FakeGroup'] );
	}

	public function testRemoveGroups() {
		global $wgAdditionalGroups;

		$oData = $this->executeTask(
			'removeGroups',
			array(
				'groups' => array( 'DummyGroup2', 'DummyGroup3' )
			)
		);

		$this->assertTrue( $oData->success );
		$this->assertFalse( $wgAdditionalGroups['DummyGroup2'] );
		$this->assertFalse( $wgAdditionalGroups['DummyGroup3'] );
	}

	protected function addGroup( $sName ) {
		$oData = $this->executeTask(
			'addGroup',
			array(
				'group' => $sName
			)
		);

		return $oData;
	}
}