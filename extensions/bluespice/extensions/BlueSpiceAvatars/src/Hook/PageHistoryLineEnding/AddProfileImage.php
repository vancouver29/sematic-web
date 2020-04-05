<?php

namespace BlueSpice\Avatars\Hook\PageHistoryLineEnding;

use BlueSpice\Hook\PageHistoryLineEnding;
use BlueSpice\Avatars\Html\ProfileImage;

class AddProfileImage extends PageHistoryLineEnding {
	protected function doProcess() {
		$this->history->getOutput()->addModuleStyles( 'ext.bluespice.avatars.history.styles' );

		$username = $this->row->rev_user_text;
		$user = \User::newFromName( $username );
		if ( $user instanceof \User === false ) {
			return true;
		}
		$profileImage = new ProfileImage( $user, 32, 32 );

		$this->s = preg_replace(
			"#(<span class='history-user'>)#",
			'$1'.$profileImage->getHtml(),
			$this->s
		);

		return true;
	}
}
