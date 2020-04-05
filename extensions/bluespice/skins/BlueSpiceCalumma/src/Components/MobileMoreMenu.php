<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;
use BlueSpice\Calumma\SkinDataFieldDefinition as SDFD;

class MobileMoreMenu extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.MobileMoreMenu';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = [];

		if ( $this->getSkinTemplate()->getSkin()->getTitle()->isSpecialPage() ) {
			return [];
		}
		if ( $this->getSkinTemplate()->getSkin()->getUser()->isAnon() ) {
			return [];
		}
		$args['show'] = parent::getTemplateArgs();
		$args['show']['title'] = "";
		$args['show']['links'] = $this->getSkinTemplate()->get( SDFD::MOBILE_MORE_MENU );
		return $args;
	}
}
