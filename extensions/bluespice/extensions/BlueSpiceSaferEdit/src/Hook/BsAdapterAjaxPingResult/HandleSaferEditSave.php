<?php

namespace BlueSpice\SaferEdit\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Hook\BsAdapterAjaxPingResult;
use Title;

class HandleSaferEditSave extends BsAdapterAjaxPingResult {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	protected function skipProcessing() {
		$this->title = Title::newFromText( $this->titleText );
		if( $this->title === null ) {
			return true;
		}

		return $this->reference !== 'SaferEditSave';
	}

	protected function doProcess() {

		if( !isset($this->params[0]['bUnsavedChanges']) ) {
					return true;
				}
				if( $this->params[0]['bUnsavedChanges'] !== true ) {
					return true;
				}

				$section = empty( $this->params[0]['section'] )
					? -1
					: $this->params[0]['section'];

				$this->singleResults['success'] = \SaferEdit::saveUserEditing(
					$this->getContext()->getUser()->getName(),
					$this->title,
					$section
				);

		return true;
	}

}