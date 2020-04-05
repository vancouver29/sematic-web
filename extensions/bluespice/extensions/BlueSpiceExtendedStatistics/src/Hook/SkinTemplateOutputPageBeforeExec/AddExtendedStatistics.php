<?php

namespace BlueSpice\ExtendedStatistics\Hook\SkinTemplateOutputPageBeforeExec;

use BlueSpice\Hook\SkinTemplateOutputPageBeforeExec;
use BlueSpice\SkinData;

class AddExtendedStatistics extends SkinTemplateOutputPageBeforeExec {
	protected function doProcess() {
		$oSpecialExtendedStatistic = \SpecialPageFactory::getPage( 'ExtendedStatistics' );

		if( !$oSpecialExtendedStatistic ) {
			return true;
		}

		$this->mergeSkinDataArray(
			SkinData::GLOBAL_ACTIONS,
			[
				'bs-extended-statistics' => [
					'href' => $oSpecialExtendedStatistic->getPageTitle()->getFullURL(),
					'text' => $oSpecialExtendedStatistic->getDescription(),
					'title' => $oSpecialExtendedStatistic->getPageTitle(),
					'iconClass' => ' icon-statistics ',
					'position' => 700,
					'data-permissions' => 'read'
				]
			]
		);

		return true;
	}
}