<?php
namespace BlueSpice\Calumma\Components;

class SidebarToggle extends \Skins\Chameleon\Components\Structure {

	protected $activeState = false;

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$data = $this->getDomElement()->getAttribute( 'data-toggle' );
		$class = $this->getDomElement()->getAttribute( 'class' );

		$html = \Html::openElement( 'a', [
			'href' => '#',
			'class' => ' sidebar-toggle ' . $class,
			'data-toggle' => $data,
			'role' => 'button',
			'title' => \Message::newFromKey( "bs-calumma-navigation-toggle-tooltip" ),
		] );

		$html .= \Html::openElement( 'i', [ 'class' => $data ] );
		$html .= \Html::closeElement( 'i' );

		$html .= \Html::closeElement( 'a' );

		return $html;
	}

	/**
	 * Is this element active
	 * @return bool
	 */
	public function isActive() {
		return $this->activeState;
	}
}
