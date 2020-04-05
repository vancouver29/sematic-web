<?php

namespace MediaWiki\Extension\LDAPUserInfo\UserInfoModifier;

use \Status;

class Email extends Base {

	/**
	 *
	 * @param \User $user
	 * @param string $rawValue
	 * @return Status
	 */
	public function modifyUserInfo( $user, $rawValue ) {
		$user->setEmail( $rawValue );
		return Status::newGood();
	}

}
