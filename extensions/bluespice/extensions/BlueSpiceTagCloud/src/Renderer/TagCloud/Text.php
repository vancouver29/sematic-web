<?php

namespace BlueSpice\TagCloud\Renderer\TagCloud;

use BlueSpice\Renderer\Params;
use MediaWiki\Linker\LinkRenderer;

class Text extends \BlueSpice\TagCloud\Renderer {
	const PARAM_NO_BORDER = 'noborder';

	/**
	 * Constructor
	 * @param \Config $config
	 * @param Params $params
	 * @param LinkRenderer|null $linkRenderer
	 */
	public function __construct( \Config $config, Params $params, LinkRenderer $linkRenderer = null ) {
		parent::__construct( $config, $params, $linkRenderer );

		$this->args[static::PARAM_NO_BORDER] = $params->get(
			static::PARAM_NO_BORDER,
			false
		);

		$this->args[static::PARAM_WIDTH] = $params->get(
			static::PARAM_WIDTH,
			700
		);

		if( $this->args[static::PARAM_NO_BORDER] ) {
			$this->args[static::PARAM_CLASS] .= ' noborder';
		}
	}

	protected function render_content( $val ) {
		$val = parent::render_content( $val );
		foreach( $val as &$entry ) {
			$entry[ static::PARAM_SHOW_COUNT ] = $this->args[
				static::PARAM_SHOW_COUNT
			];
		}

		shuffle( $val );
		return array_values( $val );
	}

	/**
	 *
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpiceTagCloud.Text";
	}

}
