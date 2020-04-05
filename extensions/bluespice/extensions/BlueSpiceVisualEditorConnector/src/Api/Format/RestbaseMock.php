<?php

namespace BlueSpice\VisualEditorConnector\Api\Format;

use ApiFormatBase;

class RestbaseMock extends ApiFormatBase {

	/**
	 *
	 * @param \ApiMain $main
	 */
	public function __construct( \ApiMain $main ) {
		parent::__construct( $main, 'restbasemock' );
	}

	/**
	 *
	 */
	public function execute() {
		$data = $this->getResult()->getResultData();
		if ( isset( $data['html'] ) ) {
			$this->printText( $data['html'] );
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType() {
		return 'text/html';
	}

}