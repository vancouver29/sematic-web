<?php

namespace BlueSpice\QrCode\Panel;

use Endroid\QrCode\QrCode;
use BlueSpice\Calumma\Panel\BasePanel;

class Panel extends BasePanel {
	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return \Message::newFromKey( 'bs-qr-code-title' );;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$url = $this->skintemplate->getSkin()->getTitle()->getFullURL();

		$qrCode = new QrCode( $url );
		$qrCode->setSize( 120 );

		$src = 'data:image/png;base64,' . base64_encode( $qrCode->writeString() );

		$msg = \Message::newFromKey( 'bs-qr-code-text' );

		$span = \Html::element( 'p', [], $msg->plain() );

		return \Html::rawElement( 'div', [ 'class' => 'scanQrCode' ], $span )
			. \Html::element( 'img', [ 'class' => 'qrCodeImage', 'src' => $src ] );
	}
}
