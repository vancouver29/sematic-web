<?php

namespace MediaWiki\Extension\LDAPUserInfo\UserInfoModifier;

use \Status;

class Realname extends Base {

	/**
	 *
	 * @param \User $user
	 * @param string $rawValue
	 * @return Status
	 */
	public function modifyUserInfo( $user, $rawValue ) {
		$user->setRealName( $rawValue );
		return Status::newGood();
	}

}
