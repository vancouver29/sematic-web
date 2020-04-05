<?php

namespace BlueSpice\Calumma\Components;

use Skins\Chameleon\Components\Component;

class LanguageSelector extends Component {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$currentLanguage = $this->getSkin()->getLanguage()->getCode();
		$otherLanguages = $this->getSkinTemplate()->get( 'language_urls' );

		$html = '<div class="bs-language-selector">';

		if ( empty( $otherLanguages ) ) {
			$html .= \Html::closeElement( 'div' );
			return $html;
		}

		$lang = explode( '-', $currentLanguage );
		$class = ' bs-language-selector-icon-' . $lang[0] . ' bs-language-selector-icon ';

		$html .= \Html::openElement( 'div', [ 'class' => 'bs-language-selector-current-language' ] );
		$html .= \Html::element(
				'i',
				[
					'class' => $class
				]
			);
		$html .= \Html::closeElement( 'div' );

		$html .= \Html::openElement( 'div', [ 'class' => 'dropdown' ] );
		$html .= \Html::element(
				'a',
				[
					'class' => 'btn dropdown-toggle',
					'type' => 'button',
					'data-toggle' => 'dropdown',
					'aria-haspopup' => 'true',
					'aria-expanded' => 'false',
				],
				''
			);

		$html .= \Html::openElement( 'ul', [ 'class' => 'dropdown-menu' ] );

		foreach ( $otherLanguages as $language ) {
			$html .= \Html::openElement( 'li' );

			$class = $language['class'] . ' ' . $language['link-class'];

			$html .= \Html::openElement(
				'a',
				[
					'class' => $class,
					'title' => $language['title'],
					'lang' => $language['lang'],
					'href' => $language['href'],
					'hreflang' => $language['hreflang'],
				]
			);

			$lang = explode( '-', $language['lang'] );
			$class = ' bs-language-selector-icon-' . $lang[0] . ' bs-language-selector-icon ';

			$html .= \Html::element(
					'i',
					[
						'class' => $class
					]
				);
			$html .= \Html::element(
					'span',
					[
						'class' => 'bs-language-selector-text'
					],
					$language['href']
				);
			$html .= \Html::closeElement( 'a' );
			$html .= \Html::closeElement( 'li' );
		}

		$html .= \Html::closeElement( 'ul' );
		$html .= \Html::closeElement( 'div' );

		$html .= '</div>';

		return $html;
	}
}
