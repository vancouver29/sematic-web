<?php

namespace BlueSpice\Readers\Data\PageReaders;

class Reader extends \BlueSpice\Data\DatabaseReader {

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	public function getSchema() {
		return new \BlueSpice\Readers\Data\Schema();
	}

	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider();
	}

}
