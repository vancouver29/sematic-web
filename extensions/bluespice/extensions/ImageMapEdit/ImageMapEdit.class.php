<?php
class ImageMapEdit{

	public static function onOutputPageBeforeHTML( &$oParserOutput, &$sText ) {
		$oCurrentTitle = $oParserOutput->getTitle();
		if ( is_null( $oCurrentTitle ) || $oCurrentTitle->getNamespace() != NS_FILE || $oParserOutput->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}
		$oCurrentFile = wfFindFile( $oCurrentTitle );
		if ( $oCurrentFile && !$oCurrentFile->canRender() ) {
			return true;
		}
		return true;
	}

	public static function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if ( $oOutputPage->getTitle()->getNamespace() != NS_FILE || $oOutputPage->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}
		$oOutputPage->addModules( 'ext.imagemapedit' );
		return true;
	}
}
