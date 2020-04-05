<?php

namespace MediaWiki\Extension\LDAPUserInfo\UserInfoModifier;

use MediaWiki\Extension\LDAPUserInfo\IUserInfoModifier;

abstract class Base implements IUserInfoModifier {

	/**
	 *
	 * @var \Config
	 */
	protected $domainConfig = null;

	/**
	 *
	 * @param \Config $domainConfig
	 */
	public function __construct( $domainConfig ) {
		$this->domainConfig = $domainConfig;
	}

	/**
	 *
	 * @param string $mappingKey
	 * @param \Config $domainConfig
	 * @return MediaWiki\Extension\LDAPUserInfo\IUserInfoModifier
	 */
	public static function factory( $mappingKey, $domainConfig ) {
		return new static( $domainConfig );
	}

}
