<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\SkinData;
use BlueSpice\Calumma\Components\SimpleLinkListGroup;
use BlueSpice\Calumma\Components\CollapsibleGroup;

class GlobalActions extends BasePanel {

	/**
	 *
	 * @var \SkinTemplate
	 */
	protected $skintemplate = null;

	/**
	 *
	 * @param SkinTemplate $skintemplate
	 */
	public function __construct( $skintemplate ) {
		$this->skintemplate = $skintemplate;
	}

	/**
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return empty( $this->skintemplate->get( SkinData::GLOBAL_ACTIONS ) )
			&& empty( $this->skintemplate->get( SkinData::ADMIN_LINKS ) );
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$sections = [
			'bs-sitenav-globalactions-section-globalactions' =>
				$this->skintemplate->get( SkinData::GLOBAL_ACTIONS )
		];

		if ( $this->skintemplate->getSkin()->getUser()->isLoggedIn() ) {
			$sections += [
				'bs-sitenav-globalactions-section-management' =>
					$this->skintemplate->get( SkinData::ADMIN_LINKS )
			];
		}

		$html = '';

		foreach ( $sections as $section => $links ) {
			$linklistgroup = new SimpleLinkListGroup( array_values( $links ) );

			$sectionId = str_replace( ' ', '-', $section );
			$collapsibleGroup = new CollapsibleGroup( [
				'id' => $sectionId,
				'title' => wfMessage( $section ),
				'content' => $linklistgroup->getHtml()
			] );

			$html .= $collapsibleGroup->getHtml();
		}

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-globalactions';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitenav-globalactions-title' );
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerClasses() {
		return [ 'calumma-navigation-mobile-hidden' ];
	}
}
