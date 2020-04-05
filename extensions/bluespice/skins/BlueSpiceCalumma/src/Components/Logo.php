<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;

use BlueSpice\Calumma\SkinDataFieldDefinition as SDFD;

class Logo extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.Logo';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$logo = $this->getSkinTemplate()->get( SDFD::LOGO );

		$args = parent::getTemplateArgs();

		foreach ( $logo as $key => $value ) {
		$args['items'][] = $value;
		}

		return $args;
	}
}
