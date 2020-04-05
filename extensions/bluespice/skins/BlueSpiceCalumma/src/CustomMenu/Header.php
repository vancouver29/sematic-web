<?php

namespace BlueSpice\Calumma\CustomMenu;

use BlueSpice\Services;

class Header extends \BlueSpice\CustomMenu\CustomMenu\Header {

	/**
	 *
	 * @return type
	 */
	public function getRenderer() {
		return Services::getInstance()->getBSRendererFactory()->get(
			'calummacustommenu',
			$this->getParams()
		);
	}

}
