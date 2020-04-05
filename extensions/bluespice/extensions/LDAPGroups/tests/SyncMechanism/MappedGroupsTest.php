<?php

namespace MediaWiki\Extension\Test\SyncMechanism;

use MediaWikiTestCase;
use MediaWiki\Extension\LDAPGroups\SyncMechanism\MappedGroups;
use MediaWiki\Extension\LDAPProvider\GroupList;
use TestUserRegistry;
use HashConfig;

class MappedGroupsTest extends MediaWikiTestCase {

	/**
	* @covers MediaWiki\Extension\LDAPGroups\SyncMechanism\MappedGroups::factory
	*/
	public function testFactory() {
		$domainConfig = new \HashConfig( [] );
		$logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
		$syncMechanism = MappedGroups::factory( $domainConfig, $logger );

		$this->assertInstanceOf(
			'MediaWiki\\Extension\\LDAPGroups\\ISyncMechanism',
			$syncMechanism
		);
	}

	/**
	 *
	 * @param string[] $mapping
	 * @param string[] $initialGroups
	 * @param string[] $fullDNs
	 * @param string[] $expectedGroups
	 * @covers MediaWiki\Extension\LDAPGroups\SyncMechanism\MappedGroups::sync
	 * @dataProvider provideTestSyncData
	 */
	public function testSync( $mapping, $initialGroups, $fullDNs, $expectedGroups ) {
		$testUser = TestUserRegistry::getMutableTestUser( 'MappedGroupsTestUser', $initialGroups );
		$user = $testUser->getUser();
		$groupList = new GroupList( $fullDNs );
		$config = new HashConfig( [
			'mapping' => $mapping
		] );
		$logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );

		$syncMechanism = new MappedGroups( $logger );
		$syncMechanism->sync( $user, $groupList, $config );

		$actualGroups = $user->getGroups();

		sort( $actualGroups );
		sort( $expectedGroups );

		$this->assertArrayEquals(
			$expectedGroups,
			$actualGroups,
			'Groups have not been set properly!'
		);
	}

	public function provideTestSyncData() {
		$initialGroups = [ 'sysop', 'some_group' ];
		// https://www.mediawiki.org/w/index.php?title=Extension:LdapGroups&oldid=2595259#Group_mapping
		$exampleMapping1 = [
			'AWSUsers' => [
				'nc=aws-production,ou=security group,o=top'
			],
			'NavAndGuidance' => [
				'cn=g001,OU=Groups,o=top',
				'cn=g002,OU=Groups,o=top',
				'cn=g003,OU=Groups,o=top',
			]
		];
		$exampleMapping2 = [
			'mathematicians' => 'ou=mathematicians,dc=example,dc=com',
			'scientists' => 'ou=scientists,dc=example,dc=com'
		];

		return [
			'set-from-ldap-and-remove-local-1' => [
				$exampleMapping1,
				$initialGroups,
				[
					'nc=aws-production,ou=security group,o=top'
				],
				[
					'AWSUsers'
				]
			],
			'set-from-ldap-and-remove-local-2' => [
				$exampleMapping2,
				$initialGroups,
				[
					'OU=SCIENTISTS,DC=EXAMPLE,DC=COM'
				],
				[
					'scientists'
				]
			]
		];
	}
}
