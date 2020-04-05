<?php

namespace BlueSpice\NamespaceCSS\Special;

class Manager extends \BlueSpice\SpecialPage {
	public function __construct( $name = '', $restriction = '', $listed = true, $function = false, $file = 'default', $includable = false ) {
		parent::__construct( 'BlueSpiceNamespaceCSSManager', 'editinterface' );
	}

	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->getOutput()->addModules( 'ext.bluespice.namespaceCSS.special' );
		$this->getOutput()->addHTML(
			\Html::element(
				'div',
				[
					'id' => 'bs-namespacecss-manager',
					'class' => 'bs-manager-container'
				]
			)
		);
	}
}
