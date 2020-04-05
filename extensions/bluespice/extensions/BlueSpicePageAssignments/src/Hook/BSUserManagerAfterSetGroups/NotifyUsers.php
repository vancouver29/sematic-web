<?php

namespace BlueSpice\PageAssignments\Hook\BSUserManagerAfterSetGroups;

use BlueSpice\PageAssignments\Notifications\GroupsAdd;
use BlueSpice\PageAssignments\Notifications\GroupsRemove;
use BlueSpice\UserManager\Hook\BSUserManagerAfterSetGroups;

class NotifyUsers extends BSUserManagerAfterSetGroups {
	protected function skipProcessing() {
		if( empty( $this->removeGroups ) && empty( $this->addGroups ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$notificationsManager = \BlueSpice\Services::getInstance()->getBSNotificationManager();
		$notifier = $notificationsManager->getNotifier();

		$agent = $this->getContext()->getUser();

		if( !empty( $this->removeGroups ) ) {
			$notification = new GroupsRemove( $agent, $this->user, $this->removeGroups );
		}
		if( !empty( $this->addGroups ) ) {
			$notification = new GroupsAdd( $agent, $this->user, $this->addGroups );
		}

		$notifier->notify( $notification );

		return true;
	}
}
