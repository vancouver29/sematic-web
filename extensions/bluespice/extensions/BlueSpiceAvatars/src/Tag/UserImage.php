<?php

namespace BlueSpice\Avatars\Tag;

use BlueSpice\Services;
use BlueSpice\DynamicFileDispatcher\Params;
use BlueSpice\Avatars\DynamicFileDispatcher\UserProfileImage;

class UserImage {

	/**
	 *
	 * @var string
	 */
	protected $input = '';

	/**
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 *
	 * @var \Parser
	 */
	protected $parser = null;

	/**
	 *
	 * @var \PPFrame
	 */
	protected $frame = null;

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 */
	public function __construct( $input, array $args, \Parser $parser, \PPFrame $frame ) {
		$this->input = $input;
		$this->args = $args;
		$this->parser = $parser;
		$this->frame = $frame;
	}

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param \Parser $parser
	 * @param \PPFrame $frame
	 * @return string|array
	 */
	public static function callback( $input, array $args, \Parser $parser, \PPFrame $frame ) {
		$handler = new static( $input, $args, $parser, $frame );
		return $handler->handle();
	}

	public function handle() {
		$username = isset( $this->args['name'] ) ? $this->args['name'] : '';
		$width = isset( $this->args['width'] ) ? (int)$this->args['width'] : 32;
		$height = isset( $this->args['height'] ) ? (int)$this->args['height'] : 32;

		$params = [
			Params::MODULE => UserProfileImage::MODULE_NAME,
			UserProfileImage::USERNAME => $username,
			UserProfileImage::WIDTH => $width,
			UserProfileImage::HEIGHT => $height
		];

		$dfdUrlBuilder = Services::getInstance()->getBSDynamicFileDispatcherUrlBuilder();
		$url = $dfdUrlBuilder->build( new Params( $params ) );

		$html = \Html::element( 'img', [
			'src' => $url,
			'alt' => wfMessage( 'bs-avatars-tag-userimage-img-alt', $username )->text(),
			'class' => 'bs-avatars-userimage-tag',
			'width' => $width,
			'height' => $height
		] );

		return $html;
	}
}
