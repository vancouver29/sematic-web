<?php

namespace BlueSpice\PageAssignments\Notifications;

use BlueSpice\PageAssignments\Notifications\PresentationModel;

class Registrator {
	public static function registerNotifications( \BlueSpice\NotificationManager $notificationsManager ) {
		$notificationsManager->registerNotificationCategory( 'bs-pageassignments-action-cat' );

		$notificationsManager->registerNotification(
			'bs-pageassignments-assignment-change-add',
			[
				'category' => 'bs-pageassignments-action-cat',
				'presentation-model' => PresentationModel\AssignmentChangeAdd::class
			]
		);

		$notificationsManager->registerNotification(
			'bs-pageassignments-assignment-change-remove',
			[
				'category' => 'bs-pageassignments-action-cat',
				'presentation-model' => PresentationModel\AssignmentChangeRemove::class
			]
		);

		$notificationsManager->registerNotification(
			'bs-pageassignments-user-group-add',
			[
				'category' => 'bs-pageassignments-action-cat',
				'presentation-model' => PresentationModel\GroupsAdd::class
			]
		);

		$notificationsManager->registerNotification(
			'bs-pageassignments-user-group-remove',
			[
				'category' => 'bs-pageassignments-action-cat',
				'presentation-model' => PresentationModel\GroupsRemove::class
			]
		);
	}

	/**
	 * Hook handler for EchoGetDefaultNotifiedUsers
	 * Should we implement separate notifications for event that are already
	 * handled in EchoConnector?
	 * That way users would get 2 mails for same event, but if not, then
	 * we depend on EchoConnector
	 *
	 * @param $event
	 * @param $users
	 */
	public static function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		switch ( $event->getType () ) {
			case 'bs-edit':
			case 'bs-move':
				foreach( self::getAssignedUsers( $event->getTitle() ) as $id => $user ) {
					$users[$id] = $user;
				}
				break;
			case 'bs-delete':
				$extra = $event->getExtra();
				if( isset( $extra['title'] ) && $extra['title'] instanceof \Title ) {
					$title = $extra['title'];
					foreach( self::getAssignedUsers( $title ) as $id => $user ) {
						$users[$id] = $user;
					}
				}
				break;
		}
	}
	/**
	 * Gets all users assigned to given title
	 *
	 * @param \Title $title
	 * @return array
	 */
	protected static function getAssignedUsers( $title ) {
		if ( $title->isTalkPage() ) {
			$title = $title->getSubjectPage();
			if ( $title instanceof \Title === false ) {
				return [];
			}
		}

		$factory = \BlueSpice\Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		if( !$target = $factory->newFromTargetTitle( $title) ) {
			return [];
		}

		$affectedUsers = [];
		foreach( $target->getAssignedUserIDs() as $id ) {
			$affectedUsers[$id] = $target->getAssignmentsForUser(
				\User::newFromId( $id )
			);
		}

		return $affectedUsers;
	}
}
