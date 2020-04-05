<?php

namespace BlueSpice\Calumma\Renderer;

use MediaWiki\MediaWikiServices;

class BreadCrumbRenderer {

	/**
	 *
	 * @param \Title $title
	 * @param \SkinTemplate $sktemplate
	 * @return string
	 */
	public static function doRender( $title, $sktemplate ) {
		$html = '';

		$portalLink = self::makePortalLink( $title );
		$breadCrumbLinks = self::makeTitleLinks( $title );

		$html .= $portalLink . $breadCrumbLinks;
		return $html;
	}

	/**
	 *
	 * @param \Title $title
	 * @return string
	 */
	public static function makePortalLink( $title ) {
		$namespaceText = $title->getNsText();

		if ( empty( $namespaceText ) ) {
			$namespaceText = wfMessage( 'nstab-main' )->plain();
		}

		/*$portalTitle = \Title::makeTitle(
			$title->getNamespace(),
			wfMessage('mainpage')->plain()
		);*/
		$portalTitle = \SpecialPage::getTitleFor( 'Allpages' );
		$portalSpecialPage = \SpecialPageFactory::getPage( 'Allpages' );
		$href = $portalSpecialPage->getPageTitle()->getLocalURL( [
			'namespace' => $title->getNamespace()
		] );

		if ( $title->isSpecialPage() ) {
			$portalSpecialPage = \SpecialPageFactory::getPage( 'SpecialPages' );
			$href = $portalSpecialPage->getPageTitle()->getLocalURL();
		}

		$portalLink = \Html::openElement(
			'a',
			[
				'title' => $portalSpecialPage->getDescription(),
				'href' => $href
			]
		);

		$portalLink .= '<span class="bs-breadcrumbs-namespace">' . $namespaceText . '</span>';
		$portalLink .= \Html::closeElement( 'a' );

		return $portalLink;
	}

	/**
	 *
	 * @param \Title $title
	 * @return type
	 */
	public static function makeTitleLinks( $title ) {
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$titleParts = explode( '/', $title->getText() );
		array_pop( $titleParts );

		$titleLiks = [];

		$root = '';
		foreach ( $titleParts as $titlePart ) {
			$root .= $titlePart;

			$part = \Title::newFromText( $root, $title->getNamespace() );
			$titleLiks[] = $linkRenderer->makeLink( $part, $titlePart );

			$root .= '/';
		}

		$html = '';
		if ( !empty( $titleLiks ) ) {
			$html .= '<span class="bs-breadcrumbs-namespace-separator"></span> ';
			$html .= implode( ' <span class="bs-breadcrumbs-separator"></span> ', $titleLiks );
		}

		return $html;
	}

}
