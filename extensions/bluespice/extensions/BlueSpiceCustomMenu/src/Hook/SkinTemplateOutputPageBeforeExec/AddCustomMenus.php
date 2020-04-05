<?php

namespace BlueSpice\CustomMenu\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;

class AddCustomMenus extends SkinTemplateOutputPageBeforeExec {

	protected function doProcess() {
		$factory = $this->getServices()->getService( 'BSCustomMenuFactory' );
		$menus = [];
		foreach ( $factory->getAllMenus() as $menu ) {
			$menus[$menu->getKey()] = $menu->getRenderer()->render();
		}

		$this->mergeSkinDataArray(
			\BlueSpice\SkinData::CUSTOM_MENU,
			$menus
		);

		return true;
	}

}
