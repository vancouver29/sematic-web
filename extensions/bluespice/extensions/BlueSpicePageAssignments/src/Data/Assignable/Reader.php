<?php

namespace BlueSpice\PageAssignments\Data\Assignable;

class Reader extends \BlueSpice\Data\DatabaseReader {

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	public function getSchema() {
		return new \BlueSpice\PageAssignments\Data\Schema();
	}

	public function makeSecondaryDataProvider() {
		return null;
	}

}
