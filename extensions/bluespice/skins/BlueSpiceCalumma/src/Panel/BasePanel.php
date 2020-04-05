<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\IPanel;
use Skins\Chameleon\IdRegistry;

abstract class BasePanel implements IPanel {

	/**
	 *
	 * @var \SkinTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @param \SkinTemplate $skintemplate
	 */
	public function __construct( $skintemplate ) {
		$this->skintemplate = $skintemplate;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return IdRegistry::getRegistry()->getId();
	}

	/**
	 *
	 * @return string
	 */
	public function getBadge() {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerClasses() {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerData() {
		return [];
	}

	/**
	 *
	 * @return string
	 */
	public function getTool() {
		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return '';
	}

	/**
	 *
	 * @return type
	 */
	public function getTriggerRLDependencies() {
		return [];
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		return true;
	}
}
