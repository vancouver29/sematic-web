<?php

namespace BlueSpice\Readers\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function skipProcessing() {
		if ( !$this->out->getTitle() || !$this->out->getTitle()->exists() ) {
			return true;
		}
		if ( !$this->out->getTitle()->userCan( 'read' ) ) {
			return true;
		}
		if ( $this->out->getRequest()->getVal( 'action', 'view' ) !== 'view' ) {
			return true;
		}
		// TODO: config
		$excludeNS = [ NS_MEDIA, NS_SPECIAL, NS_CATEGORY, NS_FILE, NS_MEDIAWIKI ];
		if ( in_array( $this->out->getTitle()->getNamespace(), $excludeNS ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.readers.styles' );
		$this->out->addJsConfigVars(
			'bsgReadersNumOfReaders',
			$this->getConfig()->get( 'ReadersNumOfReaders' )
		);
		return true;
	}

}
