<?php

namespace BlueSpice\Calumma\Structure;

abstract class TemplateStructure extends \Skins\Chameleon\Components\Structure {

	/**
	 *
	 * @var \TemplateParser
	 */
	protected $templateParser = null;

	/**
	 *
	 * @var string
	 */
	protected $renderedTemplate = '';

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$this->initTemplateParser();
		$this->renderTemplate();

		return $this->renderedTemplate;
	}

	/**
	 * Initializes the internal \TemplateParser object
	 */
	protected function initTemplateParser() {
		$this->templateParser = new \TemplateParser(
			$this->getTemplatePath()
		);
	}

	/**
	 *
	 */
	protected function renderTemplate() {
		$this->renderedTemplate = $this->templateParser->processTemplate(
			$this->getTemplateName(),
			$this->getTemplateArgs()
		);
	}

	/**
	 * @return string
	 */
	abstract protected function getTemplatePathName();

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$domElement = $this->getDomElement();
		$attributes = [];

		if ( $domElement instanceof \DOMElement === false ) {
			return $attributes;
		}

		foreach ( $domElement->attributes as $domAttr ) {
			if ( $domAttr instanceof \DOMAttr === false ) {
				continue;
			}
			$attributes[$domAttr->name] = $domAttr->value;
		}

		$this->reformatDataAttributs( $attributes );

		$attributes[$this->getSubcomponentsArgsKey()] =
			$this->getSubcomponentsData();

		return $attributes;
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
	 *
	 * @return array
	 */
	protected function getSubcomponentsData() {
		$data = [];
		foreach ( $this->getSubcomponents() as $component ) {
			$data[] = $this->getSubcomponentArgs( $component );
		}
		return $data;
	}

	/**
	 * @param \Skins\Chameleon\Components\Component $component
	 * @return array
	 */
	abstract protected function getSubcomponentArgs( $component );

	/**
	 *
	 * @return string
	 */
	protected function getSubcomponentsArgsKey() {
		return 'subcomponents';
	}

	/**
	 *
	 * @param array &$attributes
	 */
	protected function reformatDataAttributs( &$attributes ) {
		$newData = [];
		foreach ( $attributes as $name => $value ) {
			if ( strpos( $name, 'data-' ) === 0 ) {
				$newData[] = [
					'key' => substr( $name, 5 ),
					'value' => $value
				];
			}
			unset( $attributes[$name] );
		}
		$attributes['data'] = $newData;
	}

}
