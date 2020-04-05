<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Group;

class Reader extends \BlueSpice\PageAssignments\Data\Assignable\Reader {

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	public function makeSecondaryDataProvider() {
		return null;
	}
}
