<?php

namespace BlueSpice\Calumma;

class Setup {

	/**
	 *
	 */
	public static function onRegistration() {
		$GLOBALS['wgVisualEditorSupportedSkins'][] = 'bluespicecalumma';

		$skinFactory = \MediaWiki\MediaWikiServices::getInstance()->getSkinFactory();
		$skinFactory->register( 'bluespicecalumma', 'BlueSpiceCalumma', function () {
			return new Skin( 'bluespicecalumma' );
		} );
	}

	/**
	 *
	 */
	public static function onCallback() {
		$GLOBALS[ 'egChameleonLayoutFile' ] = dirname( __DIR__ ) . '/layouts/default.xml';
		$GLOBALS[ 'wgUseMediaWikiUIEverywhere' ] = true;
	}
}
