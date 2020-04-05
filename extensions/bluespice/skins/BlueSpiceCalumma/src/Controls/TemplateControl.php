<?php

namespace BlueSpice\Calumma\Controls;

use BlueSpice\Calumma\IControl;
use TemplateParser;

abstract class TemplateControl implements IControl {

	/**
	 *
	 * @var \SkinTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @var array
	 */
	protected $templateData = [];

	/**
	 *
	 * @param \SkinTemplate $skintemplate
	 * @param array $data
	 */
	public function __construct( $skintemplate, $data ) {
		$this->skintemplate = $skintemplate;
		$this->templateData = $data;
	}

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$templateParser = $this->getTemplateParser();
		return $templateParser->processTemplate(
			$this->getTemplateName(),
			$this->getTemplateArgs()
		);
	}

	/**
	 *
	 * @return TemplateParser
	 */
	protected function getTemplateParser() {
		return new TemplateParser(
			$this->getTemplatePath()
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePath() {
		$pathname = $this->getTemplatePathName();
		$parts = explode( '.', $pathname );
		array_pop( $parts );
		$subPath = implode( '/', $parts );

		return dirname( dirname( __DIR__ ) ) . "/resources/templates/$subPath";
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplateName() {
		$pathname = $this->getTemplatePathName();
		$parts = explode( '.', $pathname );
		return array_pop( $parts );
	}

	/**
	 * return string
	 */
	abstract protected function getTemplatePathName();

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		return $this->templateData;
	}
}
