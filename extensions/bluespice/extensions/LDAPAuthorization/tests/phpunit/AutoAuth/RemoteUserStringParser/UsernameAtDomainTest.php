<?php

namespace MediaWiki\Extension\LDAPAuthorization\Tests\AutoAuth\RemoteUserStringParser;

use MediaWiki\Extension\LDAPAuthorization\AutoAuth\RemoteUserStringParser\UsernameAtDomain;
use HashConfig;

class UsernameAtDomainTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @covers MediaWiki\Extension\LDAPAuthorization\AutoAuth\RemoteUserStringParser\DomainBackslashUsername::parse
	 */
	public function testParse() {
		$config = new HashConfig( [] );
		$parser = new UsernameAtDomain( $config );
		$desc = $parser->parse( "Some_user@ABC" );

		$this->assertInstanceOf(
			'MediaWiki\\Extension\\LDAPAuthorization\\AutoAuth\\IDomainUserDescription',
			$desc
		);

		$this->assertEquals( 'ABC', $desc->getDomain() );
		$this->assertEquals( 'Some_user', $desc->getUsername() );
	}

	/**
	 * @expectedException \MWException
	 * @covers MediaWiki\Extension\LDAPAuthorization\AutoAuth\RemoteUserStringParser\DomainBackslashUsername::parse
	 */
	public function testException() {
		$config = new HashConfig( [] );
		$parser = new UsernameAtDomain( $config );
		$desc = $parser->parse( "ABC\\Some_user" );
	}
}
