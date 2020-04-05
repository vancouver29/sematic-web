<?php

namespace BlueSpice\SaferEdit\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;

class Handler implements IPrivacyHandler {
	protected $db;

	public function __construct( \Database $db ) {
		$this->db = $db;
	}

	public function anonymize( $oldUsername, $newUsername ) {
		$this->db->update(
			'bs_saferedit',
			[ 'se_user_name' => $newUsername ],
			[ 'se_user_name' => $oldUsername ]
		);

		return \Status::newGood();
	}

	public function delete( \User $userToDelete, \User $deletedUser) {
		return $this->anonymize( $userToDelete->getName(), $deletedUser->getName() );
	}

	public function exportData( array $types, $format, \User $user ) {
		// What would the information here be?
		return \Status::newGood( [] );
	}
}
