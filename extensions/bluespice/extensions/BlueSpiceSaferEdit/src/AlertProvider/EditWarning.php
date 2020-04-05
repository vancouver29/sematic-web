<?php

namespace BlueSpice\SaferEdit\AlertProvider;

use BlueSpice\IAlertProvider;
use BlueSpice\AlertProviderBase;
use BlueSpice\SaferEdit\EditWarningBuilder;

class EditWarning extends AlertProviderBase {

	public function getHTML() {
		$editWarningBuilder = new EditWarningBuilder(
			$this->loadBalancer,
			$this->getConfig(),
			$this->getUser(),
			$this->skin->getTitle()
		);

		return $editWarningBuilder->getMessage();
	}

	public function getType() {
		return IAlertProvider::TYPE_WARNING;
	}

}