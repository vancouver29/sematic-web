<?php

namespace BlueSpice\Readers\Privacy;

use BlueSpice\Privacy\IPrivacyHandler;
use BlueSpice\Privacy\Module\Transparency;
use MediaWiki\Storage\RevisionRecord;
use MediaWiki\Storage\RevisionStore;

class Handler implements IPrivacyHandler {
	protected $db;
	protected $language;

	public function __construct( \Database $db ) {
		$this->db = $db;
		$this->language = \RequestContext::getMain()->getLanguage();
	}

	public function anonymize( $oldUsername, $newUsername ) {
		$this->db->update(
			'bs_readers',
			[ 'readers_user_name' => $newUsername ],
			[ 'readers_user_name' => $oldUsername ]
		);

		return \Status::newGood();
	}

	public function delete( \User $userToDelete, \User $deletedUser ) {
		$this->anonymize( $userToDelete->getName(), $deletedUser->getName() );
		$this->db->update(
			'bs_readers',
			[ 'readers_user_id' => $deletedUser->getId() ],
			[ 'readers_user_id' => $userToDelete->getId() ]
		);

		return \Status::newGood();
	}

	public function exportData( array $types, $format, \User $user ) {
		if ( !in_array( Transparency::DATA_TYPE_WORKING, $types ) ) {
			return \Status::newGood( [] );
		}

		$res = $this->db->select(
			'bs_readers',
			'*',
			[ 'readers_user_id' => $user->getId() ]
		);

		$data = [];
		foreach( $res as $row ) {
			$title = \Title::newFromID( $row->readers_page_id );
			if ( $title instanceof \Title === false ) {
				continue;
			}

			$timestamp = $this->language->userTimeAndDate(
				$row->readers_ts,
				$user
			);

			$data[] = wfMessage(
				'bs-readers-privacy-transparency-working-readers',
				$title->getPrefixedText(),
				$timestamp,
				$user->getName()
			);
		}

		if ( empty( $data ) ) {
			return \Status::newGood( [] );
		}

		return \Status::newGood( [
			Transparency::DATA_TYPE_WORKING => $data
		] );
	}
}
