<?php

namespace BlueSpice\HideTitle\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class AddModules extends BeforePageDisplay {

	protected function doProcess() {
		$title = $this->out->getTitle();
		$hideTitlePageProp = \BsArticleHelper::getInstance( $title )->getPageProp( 'bs_hidetitle' );
		if ( $hideTitlePageProp === '' ) {
			$this->out->addModuleStyles( 'ext.bluespice.hidetitle.styles' );
		}
		return true;
	}

}
