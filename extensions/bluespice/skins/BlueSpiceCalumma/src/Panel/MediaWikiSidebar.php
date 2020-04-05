<?php

namespace BlueSpice\Calumma\Panel;

use BlueSpice\Calumma\Components\SimpleLinkListGroup;
use BlueSpice\Calumma\Components\CollapsibleGroup;

class MediaWikiSidebar extends BasePanel {

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
		return empty( $this->skintemplate->get( 'sidebar' ) );
	}

	/**
	 *
	 * @return string
	 */
	public function getBody() {
		$data = $this->skintemplate->get( 'sidebar' );

		$this->extendDefaultNavigation( $data['navigation'] );

		$html = '';

		$navLinks = $data['navigation'];

		$allSpecialPages = \Title::newFromText( 'Specialpages', NS_SPECIAL );
		$navLinks[] = [
				'href' => $allSpecialPages->getFullURL(),
				'text' => wfMessage( 'specialpages' ),
				'title' => wfMessage( 'specialpages' ),
				'class' => 'calumma-desktop-hidden',
				'iconClass' => 'icon-special-specialpages'
			];

		$this->shiftBlueSpiceAboutToEnd( $navLinks );

		$navigation = new SimpleLinkListGroup( $navLinks );
		$html .= $navigation->getHtml();

		foreach ( $data as $section => $links ) {
			if ( $this->skipSection( $section ) ) {
				continue;
			}

			$message = wfMessage( $section );

			if ( $message->exists() ) {
				$section = $message->plain();
			}

			$linklistgroup = new SimpleLinkListGroup( $links );

			$sectionId = str_replace( ' ', '-', $section );

			$collapsibleGroup = new CollapsibleGroup( [
				'id' => $sectionId,
				'title' => $section,
				'content' => $linklistgroup->getHtml()
			] );

			$html .= $collapsibleGroup->getHtml();
		}

		$html .= $this->addEditLink( $this->skintemplate );
		$html .= $this->addMobileFooterLinks( $this->skintemplate );

		return $html;
	}

	/**
	 *
	 * @return string
	 */
	public function getHtmlId() {
		return 'bs-mediawiki-sidebar';
	}

	/**
	 *
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-sitenav-navigation-section-mediawikisidebar' );
	}

	protected $sectionsToSkip = [ 'navigation', 'SEARCH', 'TOOLBOX', 'LANGUAGES' ];

	/**
	 *
	 * @param string $section
	 * @return bool
	 */
	protected function skipSection( $section ) {
		if ( in_array( $section, $this->sectionsToSkip ) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param array &$data
	 */
	protected function extendDefaultNavigation( &$data ) {
		foreach ( $data as &$link ) {
			if ( !isset( $link['title'] ) ) {
				$link['title'] = $link['text'];
			}

			if ( !isset( $link['id'] ) ) {
				continue;
			}

			if ( ( $link['id'] === 'n-recentchanges' )
					|| ( $link['id'] === 'n-special-recentchanges' ) ) {

				$introText = wfMessage( 'bs-calumma-recentchanges-intro' )->plain() . '</br>';
				$introText .= wfMessage( 'bs-calumma-recentchanges-specialpage-link' )->parse();

				$link['href'] = '#';
				$link['data'] = [
					[
						'key' => 'trigger-callback',
						'value' => 'bs.calumma.recentchanges.flyoutTriggerCallback'
					],
					[
						'key' => 'trigger-rl-deps',
						'value' => 'skin.bluespicecalumma.flyout.recentchanges'
					],
					[
						'key' => 'flyout-title',
						'value' => wfMessage( 'bs-calumma-recentchanges-title' )->plain()
					],
					[
						'key' => 'flyout-intro',
						'value' => $introText
					]
				];
			}
		}
	}

	private function shiftBlueSpiceAboutToEnd( &$data ) {
		$aboutLink = [];
		$DataLinks = [];
		foreach ( $data as &$link ) {
			if ( isset( $link['id'] ) && ( $link['id'] === 'n-bluespiceabout' ) ) {
				$aboutLink = $link;
			} else {
				$DataLinks[] = $link;
			}
		}

		if ( !empty( $aboutLink ) ) {
			array_push( $DataLinks, $aboutLink );
			$data = $DataLinks;
		}
	}

	/**
	 *
	 * @param SkinTemplate $skintemplate
	 * @return string
	 */
	protected function addMobileFooterLinks( $skintemplate ) {
		$html = \Html::openElement(
					'div',
					[
						'id' => 'calumma-mobile-footer-links',
						'class' => 'panel panel-default calumma-desktop-hidden',
					]
				);

		$html .= \Html::openElement(
					'div',
					[
						'id' => 'calumma-mobile-footer-links',
						'class' => 'panel-heading',
						'role' => 'tab'
					]
				);
		$html .= '<span class="separator"></span>';
		$html .= \Html::closeElement( 'div' );

		$html .= '<div class="list-group">';

		$items[] = $this->skintemplate->getSkin()->privacyLink();
		$items[] = $this->skintemplate->getSkin()->aboutLink();
		$items[] = $this->skintemplate->getSkin()->disclaimerLink();

		foreach ( $items as $item ) {
			$html .= str_replace( '<a ', '<a class="list-group-item" ', $item );
		}

		$html .= \Html::closeElement( 'div' );
		$html .= \Html::closeElement( 'div' );

		return $html;
	}

	/**
	 *
	 * @param SkinTemplate $skintemplate
	 * @return string
	 */
	protected function addEditLink( $skintemplate ) {
		$html = '';

		if ( $skintemplate->getSkin()->getUser()->isAllowed( 'editinterface' ) ) {
			$sidebar = \Title::makeTitle( NS_MEDIAWIKI, 'Sidebar' );

			$html .= \Html::openElement(
				'a',
				[
					'href' => $sidebar->getEditURL(),
					'title' => wfMessage( 'bs-edit-mediawiki-sidebar-link-title' )->plain(),
					'target' => '_blank',
					'class' => 'bs-edit-mediawiki-sidebar-link bs-calumma-sidebar-edit-link',
					'iconClass' => ''
				]
			);

			$html .= \Html::element(
					'span',
					[
						'class' => 'label'
					],
					wfMessage( 'bs-edit-mediawiki-sidebar-link-text' )->plain()
				);

			$html .= \Html::closeElement( 'a' );
		}

		return $html;
	}

}
