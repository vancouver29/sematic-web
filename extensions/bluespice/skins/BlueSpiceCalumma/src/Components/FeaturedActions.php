<?php

namespace BlueSpice\Calumma\Components;

use Skins\Chameleon\Components\Component;
use BlueSpice\Calumma\Controls\SplitButtonDropdown;
use BlueSpice\SkinData;

class FeaturedActions extends Component {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$data = $this->getSkinTemplate()->get( SkinData::FEATURED_ACTIONS );
		$items = [];

		if ( array_key_exists( 'edit', $data ) ) {
			$elements = $this->sortFeaturedActions( $data['edit'] );
			$items += [
				'edit' => $elements
			];
		}

		if ( array_key_exists( 'new', $data ) ) {
			$elements = $this->sortFeaturedActions( $data['new'] );
			$items += [
				'new' => $elements
			];
		}

		$html = '<div class="bs-featured-actions">';

		if ( empty( $items ) ) {
			$html .= \Html::closeElement( 'div' );
			return $html;
		}

		foreach ( $items as $itemId => $itemDefinition ) {
			$splitButton = new SplitButtonDropdown(
				$this->getSkinTemplate(),
				$this->makeSplitButtonDropDownData( $itemId, $itemDefinition )
			);
			$html .= $splitButton->getHtml();
		}
		$html .= \Html::closeElement( 'div' );

		return $html;
	}

	/**
	 *
	 * @param string $itemId
	 * @param array $itemDefinition
	 * @return bool
	 */
	protected function makeSplitButtonDropDownData( $itemId, $itemDefinition ) {
		$firstEntry = array_shift( $itemDefinition );

		$splitButtonData = $firstEntry;
		if ( !isset( $splitButtonData['classes'] ) ) {
			$splitButtonData['classes'] = [ 'btn-primary', "bs-fa-$itemId" ];
		} else {
			$splitButtonData['classes'] = array_merge(
					$splitButtonData['classes'],
					[ 'btn-primary', "bs-fa-$itemId" ]
				);
		}

		if ( !isset( $splitButtonData['href'] ) ) {
			$splitButtonData['classes'][] = 'disabled';
		}

		$canEdit = $this->getSkin()->getTitle()->userCan( 'edit' );
		if ( !$canEdit && ( $firstEntry['id'] === 'ca-view' ) ) {
			return [
				'classes' => [
					0 => "btn-primary disabled bs-fa-$itemId"
				]
			];
		}

		$splitButtonData['hasItems'] = true;
		if ( count( $itemDefinition ) == 0 ) {
			$splitButtonData['hasItems'] = false;
			return $splitButtonData;
		}

		$splitButtonData['items'] = [];

		if ( isset( $firstEntry['id'] ) ) {
			unset( $firstEntry['id'] );
		}

		$splitButtonData['items'][] = [ 'link' => $firstEntry ];

		foreach ( $itemDefinition as $linkId => $linkDefinition ) {
			if ( isset( $linkDefinition['separator'] ) ) {
				$splitButtonData['items'][] = [ 'separator' => true ];
			} else {
				$splitButtonData['items'][] = [ 'link' => $linkDefinition ];
			}
		}

		return $splitButtonData;
	}

	private function sortFeaturedActions( $faArray ) {
		usort( $faArray, function ( $a, $b ) {
			if ( !isset( $a['position'] ) ) {
				return false;
			}
			if ( !isset( $b['position'] ) ) {
				return true;
			}
			return $a['position'] > $b['position'];
		} );

		return $faArray;
	}
}
