<?php

namespace BlueSpice\SaferEdit\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use Action;

class AddModules extends BeforePageDisplay {

	protected $currentAction = 'view';

	protected $validActions = [ 'edit', 'submit', 'view' ];

	protected function skipProcessing() {
		$this->currentAction = Action::getActionName( $this->getContext() );
		return in_array( $this->currentAction, $this->validActions ) === false;
	}

	protected function doProcess() {
		$this->out->addModules( 'ext.bluespice.saferedit.general' );

		if( $this->isEditBodeAndUserCanEdit() ) {
			$this->out->addModules( 'ext.bluespice.saferedit.editmode' );
		}

		return true;
	}

	private function isEditBodeAndUserCanEdit() {
		//By definition of `$this->validActions` it must be 'edit' or 'submit'
		return $this->currentAction !== 'view'
				&& $this->skin->getTitle()->userCan( 'edit' );
	}

}