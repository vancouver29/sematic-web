<?php

namespace BlueSpice\VisualEditorConnector\Api;

use ApiVisualEditor;
use ConfigFactory;
use ApiBase;

class TransformToHtml extends ApiVisualEditor {

	/**
	 *
	 * @param \ApiMain $main
	 * @param string $name
	 */
	public function __construct( \ApiMain $main, $name ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'visualeditor' );
		parent::__construct( $main, $name, $config );
	}

	/**
	 *
	 */
	public function execute() {
		$this->serviceClient->mount( '/restbase/', $this->getVRSObject() );
		$wikitext = $this->getParameter( 'wikitext' );
		$html = $this->requestRestbase(
			'POST',
			'transform/wikitext/to/html/',
			[
				'wikitext' => $wikitext
			]
		);
		$result = $this->getResult();
		$result->addValue( null, 'html', $html );
	}

	/**
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'wikitext' => [
				ApiBase::PARAM_REQUIRED => true,
			]
		];
	}

	/**
	 *
	 * @return string
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 *
	 * @return bool
	 */
	public function mustBePosted() {
		return true;
	}

}
