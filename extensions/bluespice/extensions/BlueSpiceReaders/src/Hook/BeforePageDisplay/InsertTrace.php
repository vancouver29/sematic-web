<?php

namespace BlueSpice\Readers\Hook\BeforePageDisplay;

class InsertTrace extends \BlueSpice\Hook\BeforePageDisplay {

	protected function skipProcessing() {
		if( $this->getServices()->getReadOnlyMode()->isReadOnly() ) {
			return true;
		}
		if ( !$this->out->getTitle() || !$this->out->getTitle()->exists() ) {
			return true;
		}
		if ( !$this->out->getTitle()->userCan( 'read' ) ) {
			return true;
		}
		if ( $this->out->getUser()->isAnon() ) {
			return true;
		}
		// Not sure if this is needed additionaly to isAnon...
		if ( \User::isIP( $this->out->getUser()->getName() ) ) {
			return true;
		}
		$excludeNS = [ NS_MEDIA, NS_SPECIAL, NS_CATEGORY, NS_FILE, NS_MEDIAWIKI ];
		if ( in_array( $this->out->getTitle()->getNamespace(), $excludeNS ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$extension = $this->getServices()->getBSExtensionFactory()->getExtension(
			'BlueSpiceReaders'
		);
		$extension->insertTrace( $this->out->getTitle() );
		return true;
	}

}
