<?php

namespace BlueSpice\VisualEditorConnector\Api;

use ApiVisualEditor;
use ConfigFactory;
use ApiBase;

class TransformToWikiText extends ApiVisualEditor {

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
		$html = $this->getParameter( 'html' );
		$wikitext = $this->requestRestbase(
			'POST',
			'transform/html/to/wikitext/',
			[
				'html' => $html
			]
		);
		$result = $this->getResult();
		$result->addValue( null, 'wikitext', $wikitext );
	}

	/**
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'html' => [
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
