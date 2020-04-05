<?php

namespace BlueSpice\CountThings\Renderer;

use BlueSpice\TemplateRenderer;
use BlueSpice\Renderer\Params;

class CountCharacters extends TemplateRenderer {

	const TITLE = 'title';
	const TITLELINK = 'titlelink';

	public function __construct( \Config $config, Params $params, LinkRenderer $linkRenderer = null ) {
		parent::__construct( $config, $params, $linkRenderer );

		$this->args[static::TITLE] = $params->get( static::TITLE, '' );
		$this->args[static::TITLELINK] = $params->get( static::TITLELINK, '' );
	}

	public function getTemplateName() {
		return 'BlueSpiceCountThings.CountCharacters';
	}

}
