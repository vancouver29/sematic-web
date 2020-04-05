<?php

// InstantAvatar v1.0
// (c) 2008 - Dominic Szablewski - www.phoboslab.org

class InstantAvatar {
	protected $fontFace, $fontSize;
	protected $width, $height, $chars;
	protected $avatar = null, $overlay = null;
	protected $numBackgroundStyles = 4;

	// One color scheme per row: bg1, bg2, text
	protected static $colorSchemes = [
		[ 0xcff09e, 0xa8dba8, 0x3b8686 ],
		[ 0x9e0838, 0xcc2227, 0xbad14f ],
		[ 0x314763, 0xb81f69, 0xe3d8b8 ],
		[ 0x69d2e7, 0xa7dbd8, 0xf38630 ],
		[ 0x8c2b56, 0xc33d3c, 0x97ad3e ],
		[ 0xc6cca5, 0x8ab8a8, 0x615145 ],
		[ 0xab526b, 0xbca297, 0xf0e2a4 ],
		[ 0x46294a, 0xad4c6b, 0xd4e067 ],
		[ 0xb7915a, 0x9c7b3e, 0x6b051b ],
		[ 0x755f99, 0x7b77b4, 0xb8c6d8 ],
		[ 0xedebe6, 0xd6e1c7, 0x403b33 ],
		[ 0x3d3a3b, 0x2d2a2b, 0xf60069 ],
		[ 0xcce6a5, 0xade0a6, 0x78b3a6 ],
		[ 0x6a1864, 0x850b91, 0xda6dfe ],
		[ 0x18246a, 0x0b3491, 0x6db0fe ],
		[ 0x186a53, 0x0b9156, 0x6dfea7 ],
		[ 0x236a18, 0x32910b, 0xaefe6d ],
		[ 0x566a18, 0x86910b, 0xfef26d ],
		[ 0x6a5c18, 0x91640b, 0xfeb56d ],
		[ 0x6a3818, 0x912a0b, 0xfe776d ],
		[ 0x460905, 0x550905, 0xc5160c ],
	];

	public function __construct( $fontFace, $fontSize = 18, $width = 40, $height = 40,
		$chars = 2, $overlayPNG = null
 ) {
		$this->width = $width;
		$this->height = $height;
		$this->fontFace = $fontFace;
		$this->fontSize = $fontSize;
		$this->chars = $chars;
		if ( $overlayPNG ) {
			$this->overlay = imageCreateFromPNG( $overlayPNG );
		}
	}

	public function __destruct() {
		if ( $this->avatar ) {
			imageDestroy( $this->avatar );
		}
		if ( $this->overlay ) {
			imageDestroy( $this->overlay );
		}
	}

	public function generate( $name, $colorScheme, $backgroundStyle ) {
		list( $bgColor1, $bgColor2, $textColor ) = self::$colorSchemes[$colorScheme];

		$this->avatar = imageCreateTrueColor( $this->width, $this->height );
		imageFill( $this->avatar, 0, 0, $bgColor1 );

		// Draw some random chars into the background. Unlike the other GD drawing functions
		// (imageFilledArc, imageFilledPolygon etc.) imageTTFText is anti-aliased.
		$sizeFactor = $this->width / 40;
		switch ( $backgroundStyle ) {
			case 0:
				imageTTFText(
					$this->avatar, 190 * $sizeFactor, 10,
					0, 35 * $sizeFactor,
					$bgColor2, $this->fontFace, 'O'
 );
				break;
			case 1:
				imageTTFText(
					$this->avatar, 90 * $sizeFactor, 0,
					-30 * $sizeFactor, 45 * $sizeFactor,
					$bgColor2, $this->fontFace, 'o'
 );
				break;
			case 2:
				imageTTFText(
					$this->avatar, 90 * $sizeFactor, 0,
					-30 * $sizeFactor, 30 * $sizeFactor,
					$bgColor2, $this->fontFace, '>'
 );
				break;
			case 3:
				imageTTFText(
					$this->avatar, 90 * $sizeFactor, 0,
					-30 * $sizeFactor, 45 * $sizeFactor,
					$bgColor2, $this->fontFace, '//'
 );
				break;
		}

		// Draw the first few chars of the name
		imageTTFText( $this->avatar, $this->fontSize, 0,
			4, $this->height - $this->fontSize / 2,
			$textColor, $this->fontFace,
			# HW: made this unicode safe
			mb_substr( $name, 0, $this->chars )
		);

		// Copy the overlay on top
		if ( $this->overlay ) {
			imageCopy( $this->avatar, $this->overlay, 0, 0, 0, 0,
				imageSX( $this->overlay ), imageSY( $this->overlay )
 );
		}
	}

	public function generateRandom( $name ) {
		$this->generate( $name,
			rand( 0, count( self::$colorSchemes ) - 1 ),
			rand( 0, $this->numBackgroundStyles - 1 )
 );
	}

	public function writePNG( $path ) {
		if ( $this->avatar ) {
			imagePNG( $this->avatar, $path );
			return true;
		}
		return false;
	}

	public function passThru() {
		if ( $this->avatar ) {
			imagePNG( $this->avatar );
			return true;
		}
		return false;
	}

	public function getRawPNG() {
		if ( $this->avatar ) {
			ob_start();
			imagePNG( $this->avatar );
			$raw = ob_get_contents();
			ob_end_clean();
			return $raw;
		}
		return false;
	}
}
