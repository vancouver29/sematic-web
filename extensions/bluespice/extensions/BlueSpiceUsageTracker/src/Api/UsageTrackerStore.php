<?php
namespace BS\UsageTracker\Api;
class UsageTrackerStore extends \BSApiExtJSStoreBase {

	/**
	 * @param string $sQuery
	 * @return array
	 */
	protected function makeData( $sQuery = '' ) {
		$aData = [];
		$extension = \BlueSpice\Services::getInstance()
			->getBSExtensionFactory()
			->getExtension( 'BlueSpiceUsageTracker' );
		$aRes = $extension->getUsageDataFromDB();
		foreach( $aRes as $oCollectorResult ) {
			$aData[] = $this->makeDataRow( $oCollectorResult );
		}
		return $aData;
	}

	protected function makeDataRow( \BS\UsageTracker\CollectorResult $oCollectorResult ) {
		return (object) array_merge(
			(array) $oCollectorResult,
			[
				'description' => $oCollectorResult->getDescription(),
				'updateDate' => $this->getLanguage()->timeanddate(
					$oCollectorResult->getUpdateDate(),
					true
				),
			]
		);
	}

}