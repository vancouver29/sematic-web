<?php

namespace BlueSpice\EchoConnector\Hook\TitleMoveComplete;

use BlueSpice\Hook\TitleMoveComplete;
use BlueSpice\EchoConnector\Notification\TitleMoveNotification;

class NotifyUsers extends TitleMoveComplete {
	protected function doProcess() {
		if ( $this->user->isAllowed( 'bot' ) ) {
			return true;
		}

		$notificationsManager = \BlueSpice\Services::getInstance()->getBSNotificationManager();

		$notifier = $notificationsManager->getNotifier();
		$notification = new TitleMoveNotification( $this->user, $this->newTitle, $this->title, $this->reason );
		$notifier->notify( $notification );

		return true;
	}
}
