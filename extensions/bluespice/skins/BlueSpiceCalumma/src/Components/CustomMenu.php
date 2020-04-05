<?php
namespace BlueSpice\Calumma\Components;

class CustomMenu extends \Skins\Chameleon\Components\Structure {

	/**
	 *
	 * @return string
	 */
	public function getHtml() {
		$menu = $this->getDomElement()->getAttribute( 'data-menu' );

		if ( !$this->getCutomMenu( $menu ) ) {
			$customMenu = '';
			$triggerButton = '';
		} else {
			$customMenu = $this->getCutomMenu( $menu );
			$triggerButton = parent::getHtml();
		}
		$class = $this->getDomElement()->getAttribute( 'class' );
		$class .= " bs-custom-menu-$menu-container navbar navbar-fixed-top";

		$html = \Html::rawElement(
			'nav',
			[ 'class' => $class ],
			$customMenu
		);

		$html .= $triggerButton;

		return $html;
	}

	/**
	 *
	 * @param string $menu
	 * @param mixed $default
	 * @return string
	 */
	protected function getCutomMenu( $menu, $default = false ) {
		$customMenus = $this->getSkinTemplate()->get(
			\BlueSpice\SkinData::CUSTOM_MENU
		);
		if ( !isset( $customMenus[$menu] ) ) {
			return $default;
		}

		return $customMenus[$menu];
	}

}
