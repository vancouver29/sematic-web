<?php

use BlueSpice\Tests\BSApiTasksTestBase;

/**
 * @group Broken
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceInterWikiLinksManager
 */
class BSApiTasksInterWikiLinksManagerTest extends BSApiTasksTestBase {
	protected function setUp() {
		parent::setUp();
		$GLOBALS['wgGroupPermissions']['user']['wikiadmin'] = true;
		$this->tablesUsed[] = 'interwiki';
	}

	protected function getModuleName( ) {
		return 'bs-interwikilinks-tasks';
	}

	public function testCreateInterWikiLink() {
		$oCreateData = $this->executeTask(
			'editInterWikiLink',
			array(
				'prefix' => 'dummylink',
				'url' => 'http://some.wiki.com/$1'
			)
		);

		$this->assertTrue(
			$oCreateData->success,
			"The interwiki link could not be created."
		);
		$this->assertTrue(
			$this->existsWithValue( 'dummylink', 'http://some.wiki.com/$1' ),
			"The new interwiki link does not exist in the database."
		);

		// Cache reset is needed here, so that MW updates the interwiki list already
		// during the test run.
		$this->clearCache();
	}

	public function testEditInterWikiLink() {
		$oEditData = $this->executeTask(
			'editInterWikiLink',
			array(
				'oldPrefix' => 'dummylink',
				'prefix' => 'fauxlink',
				'url' => 'http://some.wiki.com/wiki/$1'
			)
		);

		$this->assertTrue(
			$oEditData->success,
			"The interwiki link could not be edited."
		);
		$this->assertTrue(
			$this->isDeleted( 'dummylink' ),
			"The old interwiki link still exists in the database."
		);
		$this->assertTrue(
			$this->existsWithValue( 'fauxlink', 'http://some.wiki.com/wiki/$1' ),
			"The new interwiki link does not exist in the database."
		);

		// Cache reset is needed here, so that MW updates the interwiki list already
		// during the test run.
		$this->clearCache();
	}

	public function testRemoveInterWikiLink() {
		$oDeleteData = $this->executeTask(
			'removeInterWikiLink',
			array(
				'prefix' => 'fauxlink'
			)
		);

		$this->assertTrue(
			$oDeleteData->success,
			"The interwiki link could not be deleted"
		);
		$this->assertTrue(
			$this->isDeleted( 'fauxlink' ),
			"The interwiki link is still present"
		);
	}

	protected function isDeleted( $sValue ) {
		$res = $this->db->select(
			'interwiki',
			array( 'iw_prefix' ),
			array( 'iw_prefix' => $sValue ),
			wfGetCaller()
		);
		return ( $res->numRows() === 0 ) ? true : false;
	}

	protected function existsWithValue( $prefix, $value ) {
		$res = $this->db->select(
			'interwiki',
			array( 'iw_prefix', 'iw_url' ),
			array(
				'iw_prefix' => $prefix,
				'iw_url' => $value
			),
			wfGetCaller()
		);
		return ( $res->numRows() > 0 ) ? true : false;
	}

	protected function clearCache() {
		\MediaWiki\MediaWikiServices::getInstance()->getInterwikiLookup()->resetLocalCache();
	}
}
