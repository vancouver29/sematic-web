<?php
namespace BlueSpice\PageAssignments;

use BlueSpice\Data\RecordSet;
use BlueSpice\Services;

class Target {

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var IAssignment[]
	 */
	protected $assignments = null;

	/**
	 *
	 * @var string
	 */
	protected $title = null;

	/**
	 *
	 * @param \Config $config
	 * @param array $assignments
	 * @param \Title $title
	 */
	public function __construct( \Config $config, array $assignments, \Title $title ) {
		$this->config = $config;
		$this->assignments = $assignments;
		$this->title = $title;
	}

	/**
	 *
	 * @return \BlueSpice\PageAssignments\AssignmentFactory
	 */
	protected function getFactory() {
		return Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
	}

	/**
	 *
	 * @return IAssignment[]
	 */
	public function getAssignments() {
		return $this->assignments;
	}

	/**
	 *
	 * @return \Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 *
	 * @param \User $user
	 * @return boolean
	 */
	public function isUserAssigned( \User $user ) {
		if( $user->isAnon() ) {
			return false;
		}
		return in_array( $user->getId(), $this->getAssignedUserIDs() );
	}

	/**
	 *
	 * @return array - of user ids
	 */
	public function getAssignedUserIDs() {
		$ids = [];
		foreach( $this->getAssignments() as $assignment ) {
			$ids = array_merge_recursive( $ids, $assignment->getUserIds() );
		}
		return $ids;
	}

	/**
	 *
	 * @param \User $user
	 * @return IAssignment[]
	 */
	public function getAssignmentsForUser( \User $user ) {
		if( $user->isAnon() ) {
			return [];
		}
		return array_filter(
			$this->getAssignments(),
			function( IAssignment $e ) use( $user ) {
			return in_array( $user->getId(), $e->getUserIds() );
		});
	}

	/**
	 *
	 * @param IAssignments[] $assignments1
	 * @param IAssignments[] $assignments2
	 * @return IAssignments[]
	 */
	public function diff( array $assignments1 = [], array $assignments2 = [] ) {
		return array_filter( $assignments1,
			function( IAssignment $e ) use( $assignments2 ) {
			foreach( $assignments2 as $assignment ) {
				if( $e->getId() !== $assignment->getId() ) {
					continue;
				}
				return false;
			}
			return true;
		});
	}

	/**
	 *
	 * @param IAssignment[] $assignments
	 */
	public function save( array $assignments = [] ) {
		$status = \Status::newGood();
		$removeRecords = $writeRecords = [];
		foreach( $this->diff( $this->getAssignments(), $assignments ) as $assignment ) {
			$removeRecords[] = $assignment->getRecord();
		}
		foreach( $this->diff( $assignments, $this->getAssignments() ) as $assignment ) {
			$writeRecords[] = $assignment->getRecord();
		}
		if( !empty( $removeRecords ) ) {
			$res = $this->getFactory()->getStore()->getWriter()->remove(
				new RecordSet( $removeRecords )
			);
			foreach( $res->getRecords() as $record ) {
				if( $record->getStatus()->isOK() ) {
					continue;
				}
				$status->warning( $record->getStatus()->getMessage() );
			}
		}
		if( !empty( $writeRecords ) ) {
			$res = $this->getFactory()->getStore()->getWriter()->write(
				new RecordSet( $writeRecords )
			);
			foreach( $res->getRecords() as $record ) {
				if( $record->getStatus()->isOK() ) {
					continue;
				}
				$status->warning( $record->getStatus()->getMessage() );
			}
		}
		$this->invalidate();

		if( !$status->isOK() ) {
			return $status;
		}
		$status->setResult(
			true,
			$this->getFactory()->newFromTargetTitle( $this->getTitle() )
		);
		return $status;
	}

	/**
	 *
	 * @return boolean
	 */
	public function invalidate() {
		return $this->getFactory()->invalidate( $this );
	}
}
