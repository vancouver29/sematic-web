<?php

namespace MediaWiki\Extension\LDAPUserInfo\Hook\UserLoggedIn;

use MediaWiki\Extension\LDAPUserInfo\Config;
use MediaWiki\Extension\LDAPUserInfo\IUserInfoModifier;
use MediaWiki\Logger\LoggerFactory;

class SyncUserInfo extends \MediaWiki\Extension\LDAPProvider\Hook\UserLoggedIn {

	/**
	 *
	 * @return bool
	 * @throws \MWException
	 */
	protected function doProcess() {
		$logger = LoggerFactory::getInstance( 'LDAPUserInfo' );

		$userInfo = $this->ldapClient->getUserInfo( $this->user->getName() );
		$attributesMap = $this->domainConfig->get( Config::ATTRIBUTES_MAP );
		$modifierRegistry = $this->config->get( 'LDAPUserInfoModifierRegistry' );

		foreach ( $attributesMap as $modifierKey => $ldapAttribute ) {
			if ( !isset( $userInfo[$ldapAttribute] ) ) {
				$logger->warning( "No attribute '$ldapAttribute' set in LDAP result!", $userInfo );
				continue;
			}

			$origModifierKey = $modifierKey;
			if ( !isset( $modifierRegistry[$modifierKey] ) ) {
				// "property.gender" --> "property.*"
				$modifierKey = preg_replace( '#^(.*?)\..*?#', '$1.*', $modifierKey );
				if ( !isset( $modifierRegistry[$modifierKey] ) ) {
					throw new \MWException( "No factory callback set for '$modifierKey'!" );
				}
			}
			$factoryCallback = $modifierRegistry[$modifierKey];
			$modifier = call_user_func_array(
				$factoryCallback,
				[
					$origModifierKey,
					$this->domainConfig
				]
			);
			if ( $modifier instanceof IUserInfoModifier === false ) {
				throw new \MWException( "Object from '$origModifierKey' callback does not "
					. "implement `IUserInfoModifier`!" );
			}

			$logger->info( "Set '$origModifierKey' with raw value {$userInfo[$ldapAttribute]}" );
			$status = $modifier->modifyUserInfo( $this->user, $userInfo[$ldapAttribute] );
		}

		$this->user->saveSettings();

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getDomainConfigSection() {
		return Config::DOMAINCONFIG_SECTION;
	}
}
