<?php

namespace BlueSpice\VisualEditorConnector\Special;

class VisualEditorConnector extends \BsSpecialPage {

	public function __construct() {
		parent::__construct( 'VisualEditorConnector', 'read', false );
	}

	/**
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );
		$this->getOutput()->addModules( 'ext.bluespice.visualEditorConnector' );
		$this->getOutput()->addHTML( '<div id="bs-visualeditorconnector-area" class="bs-vec-textarea"></div>' );
		$this->getOutput()->addHTML( '<div id="divider" class="divider">Divide and conquer</div>' );
		$this->getOutput()->addHTML( '<div id="bs-visualeditorconnector-area1" class="bs-vec-textarea"></div>' );
		$this->getOutput()->addHTML( '<div id="divider" class="divider">Divide and conquer</div>' );
		$this->getOutput()->addHTML( '<div id="bs-visualeditorconnector-widget" class="bs-vec-widget"></div>' );
	}

}
