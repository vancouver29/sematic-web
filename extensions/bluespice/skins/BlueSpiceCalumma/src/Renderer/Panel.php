<?php

namespace BlueSpice\Calumma\Renderer;

use BlueSpice\IRenderer;
use BlueSpice\Renderer\Params;
use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\IFlyout;

class Panel implements IRenderer {

	const PARAM_INSTANCE = 'instance';

	/**
	 *
	 * @var \Config;
	 */
	protected $config = null;

	/**
	 *
	 * @var Params
	 */
	protected $params = null;

	/**
	 *
	 * @var IPanel
	 */
	protected $panelInterface = null;

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
	 *
	 * @param \Config $config
	 * @param Params $params
	 */
	public function __construct( $config, $params ) {
		$this->config = $config;
		$this->params = $params;

		$this->panelInterface = $params->get( self::PARAM_INSTANCE, null );
		if ( $this->panelInterface instanceof IPanel === false ) {
			throw new \Exception( 'No IPanel provided!' );
		}
	}

	/**
	 *
	 * @return string
	 */
	public function render() {
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
	 * @return string
	 */
	protected function getTemplatePath() {
		$pathname = $this->getTemplatePathName();
		$parts = explode( '.', $pathname );
		array_pop( $parts );
		$subPath = implode( '/', $parts );

		return __DIR__ . "/../../resources/templates/$subPath";
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
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.Panel';
	}

	/**
	 * Renders the template
	 */
	protected function renderTemplate() {
		$this->renderedTemplate = $this->templateParser->processTemplate(
			$this->getTemplateName(),
			$this->getTemplateArgs()
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = [];
		$args['id'] = $this->panelInterface->getHtmlId();

		$args['title'] = $this->panelInterface->getTitleMessage();
		$args['badge'] = $this->panelInterface->getBadge();
		$args['tool'] = $this->panelInterface->getTool();

		$args['body'] = $this->panelInterface->getBody();

		$args['classes'] = $this->panelInterface->getContainerClasses();
		$args['data'] = $this->panelInterface->getContainerData();

		$args['data'] += [
			'trigger-callback' =>
				$this->panelInterface->getTriggerCallbackFunctionName(),
			'trigger-rl-deps' => \FormatJson::encode(
				$this->panelInterface->getTriggerRLDependencies()
			)
		];
		$args['trigger-type'] = 'body';
		$args['toggle-collapse'] = true;

		if ( $this->panelInterface instanceof IFlyout ) {
			$this->addIFlyoutArgs( $args, $this->panelInterface );
		}

		$this->reformatDataAttributes( $args );
		$this->setConditionalFlags( $args );

		return $args;
	}

	protected $conditionalArgs = [ 'body', 'tools', 'badges' ];

	/**
	 *
	 * @param array &$args
	 */
	protected function setConditionalFlags( &$args ) {
		foreach ( $this->conditionalArgs as $argName ) {
			if ( !empty( $args[$argName] ) ) {
				$args["has$argName"] = true;
			}
		}
	}

	/**
	 *
	 * @param array &$args
	 * @param IFlyout $flyout
	 */
	protected function addIFlyoutArgs( &$args, IFlyout $flyout ) {
		$title = $flyout->getFlyoutTitleMessage();
		if ( $title instanceof \Message ) {
			$title = $title->text();
		}
		$intro = $flyout->getFlyoutIntroMessage();
		if ( $intro instanceof \Message ) {
			$intro = $intro->text();
		}

		$args['data'] += [
			'flyout-title' => $title,
			'flyout-intro' => $intro,
		];

		$args['trigger-type'] = 'flyout';
		$args['toggle-collapse'] = false;
	}

	/**
	 *
	 * @param array &$args
	 */
	protected function reformatDataAttributes( &$args ) {
		$newData = [];
		foreach ( $args['data'] as $key => $value ) {
			$newData[] = [
				'key' => $key,
				'value' => $value
			];
		}

		$args['data'] = $newData;
	}

}
