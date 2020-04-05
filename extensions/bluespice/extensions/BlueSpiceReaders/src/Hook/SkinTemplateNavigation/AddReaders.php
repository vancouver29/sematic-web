<?php

namespace BlueSpice\Readers\Hook\SkinTemplateNavigation;

class AddReaders extends \BlueSpice\Hook\SkinTemplateNavigation {

	protected function skipProcessing() {
		if ( !$this->sktemplate->getTitle() || !$this->sktemplate->getTitle()->exists() ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->userCan( 'viewreaders' ) ) {
			return true;
		}
		$excludeNS = [ NS_MEDIA, NS_SPECIAL, NS_CATEGORY, NS_FILE, NS_MEDIAWIKI ];
		if ( in_array( $this->sktemplate->getTitle()->getNamespace(), $excludeNS ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$special = \SpecialPage::getTitleFor(
			'Readers',
			$this->sktemplate->getTitle()->getPrefixedText()
		);

		//Add menu entry
		$this->links['actions']['readers'] = [
			'class' => false,
			'text' => $this->sktemplate->msg( 'bs-readers-contentactions-label' ),
			'href' => $special->getLocalURL(),
			'id' => 'ca-readers',
			'bs-group' => 'hidden'
		];

		return true;
	}

}
