<?php
namespace BlueSpice\PageAssignments\Renderer;

use BlueSpice\PageAssignments\IAssignment;
use MediaWiki\Linker\LinkRenderer;
use BlueSpice\Renderer\Params;
use BlueSpice\DynamicFileDispatcher\Params as DFDParams;
use BlueSpice\DynamicFileDispatcher\UserProfileImage;
use BlueSpice\Services;

class Assignment extends \BlueSpice\TemplateRenderer {
	const PARAM_ASSIGNMENT = 'assignment';

	/**
	 *
	 * @var IAssignment
	 */
	protected $assignment = null;

	/**
	 * Constructor
	 */
	public function __construct( \Config $config, Params $params, LinkRenderer $linkRenderer = null ) {
		parent::__construct( $config, $params, $linkRenderer );
		$this->assignment = $params->get(
			static::PARAM_ASSIGNMENT,
			false
		);
		$this->args['image'] = '';
		$this->args = array_merge(
			(array)$this->assignment->toStdClass(),
			$this->args
		);
	}

	protected function render_image( $val ) {
		return \Html::element( 'span', [
			'class' => "bs-icon-".$this->assignment->getType(),
		] );
	}

	/**
	 * Returns the template's name
	 * @return string
	 */
	public function getTemplateName() {
		return "BlueSpicePageAssignments.Assignment";
	}

}
