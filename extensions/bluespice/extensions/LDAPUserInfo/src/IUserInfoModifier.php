<?php

namespace MediaWiki\Extension\LDAPUserInfo;

interface IUserInfoModifier {

	/**
	 *
	 * @param \User $user
	 * @param $string $rawValue
	 * @return \Status
	 */
	public function modifyUserInfo( $user, $rawValue );
}
