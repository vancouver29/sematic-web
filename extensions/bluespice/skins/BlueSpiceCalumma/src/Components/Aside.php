<?php
namespace BlueSpice\Calumma\Components;

class Aside extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$class = $this->getDomElement()->getAttribute( 'class' );
		$toggleBy = $this->getDomElement()->getAttribute( 'data-toggle-by' );

		$body = parent::getHtml();

		$activeClass = '';
		foreach ( $this->getSubcomponents() as $component ) {

			if ( $component->isActive() ) {
				$activeClass = ' active';
			}
		}

		$html = \Html::openElement( 'aside', [
			'class' => $class . $activeClass,
			'data-toggle-by' => $toggleBy
			] );
		$html .= $body;
		$html .= \Html::closeElement( 'aside' );

		return $html;
	}
}
