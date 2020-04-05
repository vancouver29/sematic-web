<?php

namespace BlueSpice\Dashboards\Hook\PersonalUrls;

use BlueSpice\Hook\PersonalUrls;

class AddDashboardUrls extends PersonalUrls {

	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		return !$user->isLoggedIn();
	}

	protected function doProcess() {
		$user = $this->getContext()->getUser();
		$this->personal_urls['userdashboard'] = [
			'href' => \SpecialPage::getTitleFor( 'UserDashboard' )->getLocalURL(),
			'text' => \SpecialPageFactory::getPage( 'UserDashboard' )->getDescription()
		];

		if ( in_array( 'sysop', $user->getGroups() ) ) {

			$this->personal_urls['admindashboard'] = [
				'href' => \SpecialPage::getTitleFor( 'AdminDashboard' )->getLocalURL(),
				'text' => \SpecialPageFactory::getPage( 'AdminDashboard' )->getDescription()
			];
		}
		return true;
	}

}
