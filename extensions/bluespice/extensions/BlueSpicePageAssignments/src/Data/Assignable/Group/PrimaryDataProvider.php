<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Group;

use BlueSpice\Services;

class PrimaryDataProvider implements \BlueSpice\Data\IPrimaryDataProvider {

	/**
	 *
	 * @var \BlueSpice\Data\Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 * @param \IContextSource $context
	 */
	public function __construct( $db, $context ) {
		$this->context = $context;
		$this->db = $db;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 */
	public function makeData( $params ) {
		$this->params = $params;
		$this->data = [];

		$config = Services::getInstance()->getConfigFactory()->makeConfig(
			'bsg'
		);

		foreach( \BsGroupHelper::getAvailableGroups() as $groupname ) {
			if( in_array( $groupname, $config->get( 'ImplicitGroups' ) ) ) {
				continue;
			}

			$this->appendRowToData( $groupname );
		}

		return $this->data;
	}

	protected function appendRowToData( $groupname ) {
		$assignmentFactory = Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);

		$assignment = $assignmentFactory->factory(
			'group',
			$groupname,
			$this->context->getTitle()
		);
		if( !$assignment instanceof \BlueSpice\PageAssignments\IAssignment ) {
			return; //:(
		}

		if( $this->params->getQuery() !== '' ) {
			$bApply = \BsStringHelper::filter(
				\BsStringHelper::FILTER_CONTAINS,
				$assignment->getKey(),
				$this->params->getQuery()
			) || \BsStringHelper::filter(
				\BsStringHelper::FILTER_CONTAINS,
				$assignment->getText(),
				$this->params->getQuery()
			);
			if( !$bApply ) {
				return;
			}
		}

		$this->data[] = $assignment->getRecord();
	}
}

