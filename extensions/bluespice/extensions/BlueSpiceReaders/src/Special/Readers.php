<?php
namespace BlueSpice\Readers\Special;

use ViewTagErrorList;
use ViewTagError;
use BlueSpice\Services;

class Readers extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'Readers', 'viewreaders', false );
	}

	public function execute( $parameters ) {
		$this->checkPermissions();
		$requestedTitle = null;
		$out = $this->getOutput();

		if ( !empty( $parameters ) ) {
			$requestedTitle = \Title::newFromText( $parameters );

			if ( $requestedTitle->exists() && ( $requestedTitle->getNamespace() !== \NS_USER || $requestedTitle->isSubpage() === true ) ) {
				$stringOut = $this->renderReadersGrid();

				$out->addModules( 'ext.bluespice.readers.specialreaders' );
				$out->setPageTitle( wfMessage( 'readers', $requestedTitle->getFullText() )->text() );

				$out->addJsConfigVars( "bsReadersTitle", $requestedTitle->getPrefixedText() );

			} elseif ( $requestedTitle->getNamespace() === \NS_USER ) {
				$stringOut = $this->renderReaderspathGrid();

				$out->addModules( 'ext.bluespice.readers.specialreaderspath' );
				$oUser = \User::newFromName( $requestedTitle->getText() );
				$out->setPageTitle( wfMessage( 'readers-user', $oUser->getName() )->text() );

				$out->addJsConfigVars( "bsReadersUserID", $oUser->getId() );
			} else {
				$errorView = new ViewTagErrorList();
				$errorView->addItem( new ViewTagError( wfMessage( 'bs-readers-pagenotexists' )->plain() ) );
				$stringOut = $errorView->execute();
			}
		} else {
			$errorView = new ViewTagErrorList( Services::getInstance()->getBSExtensionFactory()->getExtension('BlueSpiceReaders'));
			$errorView->addItem( new ViewTagError( wfMessage( 'bs-readers-emptyinput' )->plain() ) );
			$stringOut = $errorView->execute();
		}

		if ( $requestedTitle === null ) {
			$out->setPageTitle( $out->getPageTitle() );
		}

		$out->addHTML( $stringOut );
	}

	private function renderReadersGrid() {
		return '<div id="bs-readers-grid"></div>';
	}

	private function renderReaderspathGrid() {
		return '<div id="bs-readerspath-grid"></div>';
	}

}
