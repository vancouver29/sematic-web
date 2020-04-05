<?php

namespace BlueSpice\Readers\Data\PageReaders;

use BlueSpice\Readers\Data\Record;

class SecondaryDataProvider extends \BlueSpice\Data\SecondaryDataProvider {
	protected function doExtend( &$dataSet ) {
		$factory = \BlueSpice\Services::getInstance()->getBSRendererFactory();
		$user = \User::newFromId( $dataSet->get( Record::USER_ID ) );
		if( $user instanceof \User == false ) {
			return;
		}

		$image = $factory->get( 'userimage', new \BlueSpice\Renderer\Params( [
			'user' => $user,
			'width' => "48",
			'height' => "48",
		]));
		$dataSet->set( Record::USER_IMAGE_HTML, $image->render() );
	}
}
