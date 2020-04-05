<?php
namespace BlueSpice\PageAssignments;

abstract class Assignable implements IAssignable {

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 *
	 * @var stirng
	 */
	protected $type = 'base';

	/**
	 *
	 * @var \IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \IContextSource
	 * @param \Config $config
	 * @param stirng $type
	 */
	public function __construct( $context, $config, $type ) {
		$this->config = $config;
		$this->type = $type;
		$this->context = $context;
	}

	public function getType() {
		return $this->type;
	}

	public function getRendererKey() {
		return "assignment";
	}
}
