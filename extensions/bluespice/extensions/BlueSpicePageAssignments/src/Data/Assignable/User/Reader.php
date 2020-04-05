<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

class Reader extends \BlueSpice\Data\User\Reader {

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	public function makeSecondaryDataProvider() {
		return null;
	}

}
