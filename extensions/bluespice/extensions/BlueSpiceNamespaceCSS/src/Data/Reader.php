<?php

namespace BlueSpice\NamespaceCSS\Data;

use BlueSpice\Services;

class Reader extends \BlueSpice\Data\DatabaseReader {

	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	public function getSchema() {
		return new Schema();
	}

	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider(
			Services::getInstance()->getLinkRenderer()
		);
	}

}
