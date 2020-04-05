<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Renderer\Panel;
use BlueSpice\Services;
use BlueSpice\RendererFactory;
use BlueSpice\Renderer\Params;

abstract class PanelContainer extends BasePanel {

	/**
	 *
	 * @var \SkinTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @var IPanel[]
	 */
	protected $panels = [];

	/**
	 *
	 * @var RendererFactory
	 */
	protected $rendererFactory = null;

	/**
	 *
	 * @param \SkinTemplate $skintemplate
	 */
	public function __construct( $skintemplate ) {
		$this->skintemplate = $skintemplate;
		$this->rendererFactory =
			Services::getInstance()->getBSRendererFactory();
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$this->initPanels();
		$html = '';

		foreach ( $this->panels as $id => $panel ) {
			if ( !$panel->shouldRender( $this->getContext() ) ) {
				continue;
			}
			$params = new Params( [ Panel::PARAM_INSTANCE => $panel ] );
			$renderer = $this->rendererFactory->get( 'panel', $params );
			$renderer instanceof Panel;
			$html .= $renderer->render();
		}

		return $html;
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @return bool
	 */
	public function shouldRender( $context ) {
		$this->initPanels();
		return !empty( $this->panels );
	}

	/**
	 *
	 * @var bool
	 */
	protected $alreadyInitialized = false;

	/**
	 *
	 * @return null
	 */
	protected function initPanels() {
		if ( $this->alreadyInitialized ) {
			return;
		}

		$this->panels = $this->makePanels();

		$this->alreadyInitialized = true;
	}

	/**
	 * @return IPanel[]
	 */
	abstract protected function makePanels();

	/**
	 *
	 * @param array $panelArray
	 * @return array
	 */
	protected function sortPanel( $panelArray ) {
		usort( $panelArray, function ( $a, $b ) {
			$a['position'] = isset( $a['position'] ) ? $a['position'] : 0;
			$b['position'] = isset( $b['position'] ) ? $b['position'] : 0;
			return $a['position'] > $b['position'];
		} );

		return $panelArray;
	}

	/**
	 *
	 * @return \IContextSource
	 */
	protected function getContext() {
		return $this->skintemplate->getSkin()->getContext();
	}

}
