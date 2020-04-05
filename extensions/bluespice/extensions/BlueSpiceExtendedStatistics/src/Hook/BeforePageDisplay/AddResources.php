<?php

namespace BlueSpice\ExtendedStatistics\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.statistics.styles' );
		// Todo: collecting resources should be done client side by dashboards
		foreach( ['AdminDashboard', 'UserDashboard', 'WikiAdmin'] as $special ) {
			if( !$this->skin->getTitle()->isSpecial( $special ) ) {
				continue;
			}
			$this->out->addModules( 'ext.bluespice.statisticsPortlets' );
			break;
		}
		return true;
	}

}
