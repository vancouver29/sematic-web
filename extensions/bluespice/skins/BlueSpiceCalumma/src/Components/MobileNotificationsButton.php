<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\SkinDataFieldDefinition as SDFD;

class MobileNotificationsButton extends \Skins\Chameleon\Components\Structure {

	/**
	 * The resulting HTML
	 * @return string
	 */
	public function getHtml() {
		$class = $this->getDomElement()->getAttribute( 'class' );
		$title = \Title::newFromText( 'Notifications', NS_SPECIAL );

		$notifications = SDFD::countNotifications( $this->getSkinTemplate() );

		$html = \Html::openElement( 'a', [
				'href' => $title->getFullURL(),
				'title' => $title->getText(),
				'class' => ' ' . $class,
				'role' => 'button'
			] );

		$iconClass = $class . ' ' . $notifications['notifications-badge-active'];

		$html .= \Html::openElement( 'i', [ 'class' => $iconClass ] );
		$html .= \Html::closeElement( 'i' );

		$html .= \Html::closeElement( 'a' );

		return $html;
	}
}
