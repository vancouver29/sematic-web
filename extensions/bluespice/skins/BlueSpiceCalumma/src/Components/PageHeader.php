<?php

namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;
use BlueSpice\Calumma\Renderer\BreadCrumbRenderer;
use BlueSpice\Services;

class PageHeader extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.PageHeader';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args = parent::getTemplateArgs();
		$args += [
			'sitenotice' => $this->getSiteNotice(),
			'indicators' => $this->getIndicators(),
			'firstheading' => $this->getFirstHeading(),
			'lang' => $this->getPageLanguageCode(),
			'headerlinks' => $this->getHeaderLinks(),
			'breadcrumbs' => $this->getBreadCrumbs(),
			'tools' => $this->getTools()
		];
		return $args;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSiteNotice() {
		return $this->getSkinTemplate()->get( 'sitenotice' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getIndicators() {
		$out = '';
		$indicators = $this->getSkinTemplate()->get( 'indicators' );
		// Logic from `BaseTemplate`
		foreach ( $indicators as $id => $content ) {
			$out .= \Html::rawElement(
				'div',
				[
					'id' => \Sanitizer::escapeIdForAttribute( "mw-indicator-$id" ),
					'class' => 'mw-indicator',
				],
				$content
			) . "\n";
		}
		return $out;
	}

	/**
	 *
	 * @return string
	 */
	protected function getFirstHeading() {
		$titleText = $this->getSkinTemplate()->get( 'title' );

		$currentTitle = $this->getSkin()->getTitle();
		$title = \Title::newFromText( $titleText );
		// Only shorten if not already overwirtten by another extension or `{{DISPLAYTITLE:...}}`
		if ( $title && $title->equals( $currentTitle ) ) {
			$titleText = $currentTitle->getSubpageText();
		}

		return $titleText;
	}

	/**
	 *
	 * @return string
	 */
	protected function getHeaderLinks() {
		return $this->getSkinTemplate()->get( 'headerlinks' );
	}

	/**
	 *
	 * @return string
	 */
	protected function getPageLanguageCode() {
		return $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();
	}

	/**
	 *
	 * @return string
	 */
	protected function getBreadCrumbs() {
		return BreadCrumbRenderer::doRender( $this->getSkin()->getTitle(), $this->getSkinTemplate() );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTools() {
		$html = '';
		$pageToolsFactory = Services::getInstance()->getBSPageToolFactory();
		foreach ( $pageToolsFactory->getAll() as $tool ) {
			$requiredPermissions = $tool->getPermissions();
			$shouldShow = true;
			foreach ( $requiredPermissions as $requiredPermission ) {
				if ( !$this->getSkin()->getTitle()->userCan( $requiredPermission ) ) {
					$shouldShow = false;
				}
			}

			if ( $shouldShow ) {
				$html .= $tool->getHtml();
			}
		}
		return $html;
	}

}
