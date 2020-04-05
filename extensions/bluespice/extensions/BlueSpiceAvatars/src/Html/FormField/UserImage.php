<?php

namespace BlueSpice\Avatars\Html\FormField;

use BlueSpice\Avatars\Html\ProfileImage;

class UserImage extends \HTMLTextField {

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return wfMessage( 'bs-avatars-pref-userimage' )->parse();
	}

	/**
	 *
	 * @param string $value
	 * @return string
	 */
	public function getInputHTML( $value ) {
		$this->mParent->getOutput()->addModuleStyles( 'ext.bluespice.avatars.preferences.styles' );

		$profileImage = new ProfileImage( $this->mParent->getUser(), 128, 128 );
		$html = parent::getInputHTML( $value ) . $profileImage->getHtml();

		return $html;
	}
}
