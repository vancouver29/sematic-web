<?php

namespace BlueSpice\TagCloud;

use BlueSpice\Config;

class Context extends \BlueSpice\Context {

	/**
	 *
	 * @var \User
	 */
	protected $user = null;

	/**
	 *
	 * @param \IContextSource $context
	 * @param Config $config
	 * @param \User $user | null
	 */
	public function __construct( \IContextSource $context, Config $config, \User $user = null ) {
		parent::__construct( $context, $config );
		$this->user = $user;
	}

	public function getUser() {
		if( $this->user ) {
			return $this->user;
		}
		return parent::getUser();
	}
}
