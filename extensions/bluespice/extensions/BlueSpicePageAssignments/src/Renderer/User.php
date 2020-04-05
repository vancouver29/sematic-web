<?php
namespace BlueSpice\PageAssignments\Renderer;

use MediaWiki\Linker\LinkRenderer;
use BlueSpice\Renderer\Params;
use BlueSpice\DynamicFileDispatcher\Params as DFDParams;
use BlueSpice\DynamicFileDispatcher\UserProfileImage;
use BlueSpice\Services;

class User extends Assignment {
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
	}

	protected function render_image( $val ) {
		$renderer = Services::getInstance()->getBSRendererFactory()->get(
			'userimage',
			new \BlueSpice\Renderer\Params( [
				'user' => \User::newFromName( $this->assignment->getKey() )
			])
		);
		return $renderer->render();
	}

}
