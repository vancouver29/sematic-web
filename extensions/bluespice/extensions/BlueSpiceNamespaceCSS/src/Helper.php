<?php

namespace BlueSpice\NamespaceCSS;

use BlueSpice\Services;

class Helper {

	/**
	 *
	 * @param integer $idx
	 * @return string|false
	 */
	public static function buildNamespaceNameFromNamespaceIndex( $idx ) {
		if( \MWNamespace::isTalk( $idx ) ) {
			$idx--;
		}

		$excludeNs = Services::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'NamespaceCSSExcludeNamespaces' );

		if( in_array( $idx, $excludeNs ) ) {
			return false;
		}
		if( $idx === NS_MAIN ) {
			//This method returns canonical namespace names. The canonical
			//language is english
			return wfMessage( 'bs-ns_main' )->inLanguage( 'en' )->plain();
		}
		if( !$nsName = \MWNamespace::getCanonicalName( $idx ) ) {
			return false;
		}

		return $nsName;
	}

	/**
	 *
	 * @param integer $idx
	 * @return string|false
	 */
	public static function buildTitleFromNamespaceIndex( $idx ) {
		if( !$text = static::buildNamespaceNameFromNamespaceIndex( $idx ) ) {
			return false;
		}
		if( $idx === NS_MAIN ) {
			$text = 'Main'; //Pseudo canonical name. Used for Page name
		}

		return \Title::newFromText( "$text.css", NS_MEDIAWIKI );
	}
}
