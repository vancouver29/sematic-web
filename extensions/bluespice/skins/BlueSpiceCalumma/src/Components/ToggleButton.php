<?php
namespace BlueSpice\Calumma\Components;

class ToggleButton extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$data = $this->getDomElement()->getAttribute( 'data-toggle' );
		$class = $this->getDomElement()->getAttribute( 'class' );

		$html = \Html::openElement( 'a', [
				'href' => '#',
				'class' => ' calumma-toggle-button ' . $class,
				'data-toggle' => $data,
				'role' => 'button'
			] );

		$html .= \Html::openElement( 'i', [ 'class' => $data ] );
		$html .= \Html::closeElement( 'i' );

		$html .= \Html::closeElement( 'a' );

		return $html;
	}
}
