<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\PanelFactory;
use BlueSpice\Calumma\Structure\TabPanelStructure;
use BlueSpice\Calumma\IActiveStateProvider;
use Skins\Chameleon\IdRegistry;
use BlueSpice\SkinData;

class ToolPaneTabs extends TabPanelStructure {

	protected $activeState = false;

	/**
	 *
	 * @return array
	 */
	protected function getSubcomponentsData() {
		$activeTabId = $this->getActiveTabId();

		$panelFactory = new PanelFactory(
			$this->getSkinTemplate()->get( SkinData::SITE_TOOLS ),
			$this->getSkinTemplate()
		);

		$panels = $panelFactory->makePanels();

		foreach ( $panels as $panel ) {
			if ( $panel instanceof IActiveStateProvider ) {
				if ( $panel->isActive() ) {
					$activeTabId = $panel->getHtmlId();
					$this->activeState = true;
				}
			}
		}

		$subComponentsData = [];
		foreach ( $panels as $panel ) {
			if ( !$panel->shouldRender( $this->getSkin()->getContext() ) ) {
				continue;
			}
			$panel instanceof \BlueSpice\Calumma\IPanel;

			$tabId = $panel->getHtmlId();
			$subComponentsData[] = [
				'id' => $tabId,
				'active' => $tabId === $activeTabId,
				'title' => $panel->getTitleMessage(),
				'body' => $panel->getBody()
			];
		}

		return $subComponentsData;
	}

	/**
	 * The HTML ID for this component
	 * @return string
	 */
	public function getHtmlId() {
		if ( $this->htmlId === null ) {
			$this->htmlId = IdRegistry::getRegistry()->getId( 'bs-toolpanetabs' );
		}
		return $this->htmlId;
	}

	/**
	 * Is this element active
	 * @return bool
	 */
	public function isActive() {
		return $this->activeState;
	}

}
