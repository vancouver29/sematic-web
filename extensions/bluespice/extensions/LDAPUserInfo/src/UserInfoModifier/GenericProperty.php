<?php

namespace MediaWiki\Extension\LDAPUserInfo\UserInfoModifier;

use \Status;
use MediaWiki\Extension\LDAPUserInfo\Config;

class GenericProperty extends Base {

	/**
	 *
	 * @var string
	 */
	protected $propertyName = '';

	/**
	 *
	 * @param \Config $domainConfig
	 * @param string $propertyName
	 */
	public function __construct( $domainConfig, $propertyName ) {
		parent::__construct( $domainConfig );
		$this->propertyName = $propertyName;
	}

	/**
	 *
	 * @param string $mappingKey e.g. "property.gender"
	 * @param \Config $domainConfig
	 * @return MediaWiki\Extension\LDAPUserInfo\IUserInfoModifier
	 */
	public static function factory( $mappingKey, $domainConfig ) {
		$mappingKeyParts = explode( '.', $mappingKey, 2 );
		return new static( $domainConfig, $mappingKeyParts[1] );
	}

	/**
	 *
	 * @param \User $user
	 * @param string $rawValue
	 * @return Status
	 */
	public function modifyUserInfo( $user, $rawValue ) {
		$normalizationCallbacks = $this->domainConfig->get(
			Config::GENERIC_PROPERTY_NORMALIZATION_CALLBACKS
		);
		$value = $rawValue;
		if ( isset( $normalizationCallbacks[$this->propertyName] ) ) {
			$normalizationCallback = $normalizationCallbacks[$this->propertyName];
			if ( is_callable( $normalizationCallback ) ) {
				$value = call_user_func( $normalizationCallback, [ $rawValue ] );
			}
		}

		$user->setOption( $this->propertyName,  $value );
		return Status::newGood();
	}

}
