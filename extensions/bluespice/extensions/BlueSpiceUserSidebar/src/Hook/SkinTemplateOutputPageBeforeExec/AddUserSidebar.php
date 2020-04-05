<?php

namespace BlueSpice\UserSidebar\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;
use BlueSpice\UserSidebar\Panel\UserSidebarNav;

class AddUserSidebar extends SkinTemplateOutputPageBeforeExec {

	protected function skipProcessing() {
		return $this->getContext()->getUser()->isAnon();
	}

	protected function doProcess() {
		$this->skin->getOutput()->addModuleStyles( 'ext.blueSpiceUserSidebar.styles' );
		$this->addSiteNavTab();

		return true;
	}

	protected function addSiteNavTab() {
		$this->mergeSkinDataArray(
			SkinData::SITE_NAV,
			[
				'bs-usersidebar' => [
					'position' => 30,
					'callback' => function( $sktemplate ) {
						return new UserSidebarNav( $sktemplate );
					}
				]
			]
		);
	}

}
