<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

use BlueSpice\Avatars\Generator;
use BlueSpice\DynamicFileDispatcher\UserProfileImage as UPI;
use BlueSpice\DynamicFileDispatcher\UserProfileImage\AnonImage;

class UserProfileImage extends UPI {

	/**
	 *
	 * @return Image|ImageExternal
	 */
	public function getFile() {
		$file = parent::getFile();
		if ( $file instanceof AnonImage ) {
			return $file;
		}

		$profileImage = $this->user->getOption( 'bs-avatars-profileimage' );
		if ( empty( $profileImage ) ) {
			return $this->getDefaultUserImageFile();
		}

		if ( wfParseUrl( $profileImage ) !== false ) {
			return new ImageExternal( $this, $profileImage, $this->user );
		}

		$repoFile = \RepoGroup::singleton()->findFile( $profileImage );
		if ( $repoFile === false || !$repoFile->exists() ) {
			return $this->getDefaultUserImageFile();
		}

		$width = $this->params[static::WIDTH];
		$height = $this->params[static::HEIGHT];

		$thumburl = $repoFile->createThumb( $width, $height );
		return new Image( $this, $thumburl, $this->user );
	}

	/**
	 *
	 * @return Image
	 */
	protected function getDefaultUserImageFile() {
		$generator = new Generator( $this->getConfig() );
		$file = $generator->getAvatarFile( $this->user );
		if ( !$file->exists() ) {
			$generator->generate( $this->user );
		}

		$thumburl = $file->createThumb(
			$this->params[UPI::WIDTH],
			$this->params[UPI::HEIGHT]
		);

		return new Image( $this, $thumburl, $this->user );
	}
}
