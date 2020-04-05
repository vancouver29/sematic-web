<?php
namespace BlueSpice\Calumma\DataProvider;

use BlueSpice\Calumma\SkinDataFieldDefinition;
use BlueSpice\SkinData;

class MobileMoreMenuData {

	/**
	 *
	 * @param Skin $skin
	 * @param SkinTemplate &$skintemplate
	 * @param array &$data
	 */
	public static function populate( $skin, &$skintemplate, &$data ) {
		foreach ( $data[SkinData::FEATURED_ACTIONS]['edit'] as $item ) {
			if ( array_key_exists( 'id', $item ) ) {
				$item['class'] = 'calumma-mobile-more-menu-' . $item['id'];
				unset( $item['id'] );
			}
			if ( !array_key_exists( 'text', $item ) ) {
				$item['text'] = $item['title'];
			}
			$data[SkinDataFieldDefinition::MOBILE_MORE_MENU][] = $item;
		}

		foreach ( $data[SkinData::FEATURED_ACTIONS]['new'] as $item ) {
			if ( array_key_exists( 'id', $item ) ) {
				$item['class'] = 'calumma-mobile-more-menu-' . $item['id'];
				unset( $item['id'] );
			}
			if ( !array_key_exists( 'text', $item ) ) {
				$item['text'] = $item['title'];
			}
			$data[SkinDataFieldDefinition::MOBILE_MORE_MENU][] = $item;
		}
	}
}
