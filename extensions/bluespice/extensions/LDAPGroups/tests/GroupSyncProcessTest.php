<?php

namespace MediaWiki\Extension\Test\SyncMechanism;

use MediaWikiTestCase;
use MediaWiki\Extension\LDAPGroups\GroupSyncProcess;
use HashConfig;

class GroupSyncProcessTest extends MediaWikiTestCase {

	/**
	* @covers MediaWiki\Extension\LDAPGroups\GroupSyncProcess::__construct
	*/
	public function testInstance() {
		$user = $this->getMock( 'User' );
		$domainConfig = new HashConfig( [] );
		$builder = $this->getMockBuilder( 'MediaWiki\\Extension\\LDAPProvider\\Client' );
		$builder->disableOriginalConstructor();
		$client = $builder->getMock();
		$callbackRegistry = [];

		$groupSyncProcess = new GroupSyncProcess(
			$user,
			$domainConfig,
			$client,
			$callbackRegistry
		);

		$this->assertInstanceOf(
			'MediaWiki\\Extension\\LDAPGroups\\GroupSyncProcess',
			$groupSyncProcess
		);
	}
}
