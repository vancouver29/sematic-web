<?php

namespace BlueSpice\Calumma\Structure;

abstract class TabPanelStructure extends TemplateStructure {

	/**
	 *
	 * @var string
	 */
	protected $htmlId = null;

	/**
	 *
	 * @return array
	 */
	public function getResourceLoaderModules() {
		$modules = parent::getResourceLoaderModules();
		$modules[] = 'skin.bluespicecalumma.tab';
		return $modules;
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.TabPanel';
	}

	/**
	 *
	 * @return string
	 */
	protected function getSubcomponentsArgsKey() {
		return 'panels';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();
		$args['id'] = $this->getHtmlId();

		$this->ensureActiveTab( $args[$this->getSubcomponentsArgsKey()] );
		if ( $this->getActiveTabId() === null ) {
			$args[$this->getSubcomponentsArgsKey()][0]['active'] = true;
		}

		return $args;
	}

	/**
	 *
	 * @param \BlueSpice\Calumma\Structure\IPanel $component
	 * @return array
	 * @throws \MWException
	 */
	protected function getSubcomponentArgs( $component ) {
		$data = [];
		$activeTabId = $this->getActiveTabId();
		if ( $component instanceof IPanel ) {
			$tabId = $component->getHtmlId();
			$data = [
				'id' => $tabId,
				'title' => $component->getTitle(),
				'body' => $component->getBody(),
				'active' => $tabId === $activeTabId
			];
		} else {
			throw new \MWException( "Subcomponent must implement IPanel!" );
		}

		return $data;
	}

	/**
	 * @return string
	 */
	abstract public function getHtmlId();

	/**
	 *
	 * @return string
	 */
	protected function getActiveTabId() {
		$cookieName = $this->getCookieName();
		$activeTabId = $this->getSkin()->getRequest()->getCookie( $cookieName );
		return $activeTabId;
	}

	/**
	 *
	 * @return string
	 */
	protected function getCookieName() {
		return 'CalummaTab_'.$this->getHtmlId();
	}

	/**
	 *
	 * @param array &$tabs
	 * @return null
	 */
	protected function ensureActiveTab( &$tabs ) {
		if ( empty( $tabs ) ) {
			return;
		}

		$firstTab =& $tabs[0];
		$hasActive = false;
		foreach ( $tabs as $tab ) {
			if ( $tab['active'] === true ) {
				$hasActive = true;
				break;
			}
		}
		if ( !$hasActive ) {
			$firstTab['active'] = true;
		}
	}
}
