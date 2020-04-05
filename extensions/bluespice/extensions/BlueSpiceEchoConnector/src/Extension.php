<?php

namespace BlueSpice\EchoConnector;

use BlueSpice\EchoConnector\Notifier\NotificationsEchoNotifier;
use BlueSpice\Data\Watchlist\Store;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\Watchlist\Record;
use Wikimedia\Rdbms\ILoadBalancer;

class Extension {
	/**
	 *
	 * @global type $bsgNotifierClass
	 */
	public static function onRegistration() {
		global $bsgNotifierClass;
		$bsgNotifierClass = NotificationsEchoNotifier::class;
	}

	public static function registerNotifications( \BlueSpice\NotificationManager $notificationsManager ) {
		$notificationsManager->registerNotificationCategory(
			'bs-admin-cat',
			[
				'priority' => 3,
				'usergroups' => [ 'sysop' ]
			]
		);
		$notificationsManager->registerNotificationCategory( 'bs-page-actions-cat', [ 'priority' => 3 ] );

		$notificationsManager->registerNotification(
			'bs-adduser',
			[
				'category' => 'bs-admin-cat',
				'presentation-model' => PresentationModel\AddUserPresentationModel::class,
				'user-locators' => [ self::class . '::getUsersToNotify' ]
			]
		);

		$notificationsManager->registerNotification(
			'bs-edit',
			[
				'category' => 'bs-page-actions-cat',
				'presentation-model' => PresentationModel\EditPresentationModel::class,
				'bundle' => [
					'web' => true,
					'email' => true,
					'expandable' => true
				],
				'user-locators' => [ self::class . '::getUsersToNotify' ]
			]
		);

		$notificationsManager->registerNotification(
			'bs-create',
			[
				'category' => 'bs-page-actions-cat',
				'presentation-model' => PresentationModel\CreatePresentationModel::class,
				'user-locators' => [ self::class . '::getUsersToNotify' ]
			]
		);

		$notificationsManager->registerNotification(
			'bs-delete',
			[
				'category' => 'bs-page-actions-cat',
				'presentation-model' => PresentationModel\DeletePresentationModel::class,
				'extra-params' => [
					// usually only existing titles can produce notifications
					// we do not have a title after its deleted
					'forceRender' => true
				],
				'user-locators' => [ self::class . '::getUsersToNotify' ]
			]
		);

		$notificationsManager->registerNotification(
			'bs-move',
			[
				'category' => 'bs-page-actions-cat',
				'presentation-model' => PresentationModel\MovePresentationModel::class,
				'user-locators' => [ self::class . '::getUsersToNotify' ]
			]
		);
	}

	/**
	 * Get users to notify if none are set explicitly
	 *
	 * @param $event
	 * @return array
	 */
	public static function getUsersToNotify( $event ) {
		$users = [];

		switch ( $event->getType() ) {
			case 'bs-adduser':
				// Get admin users
				$users = self::getUsersFromGroups( 'sysop' );
				break;
			case 'bs-create':
				// Who should be notified if new page is created?
				$users = self::getAllSubscribed();
				break;
			case 'bs-edit':
			case 'bs-move':
				// Get all users watching the page
				$users = self::getWatchers( $event->getTitle()->getPrefixedText() );
				break;
			case 'bs-delete':
				// Get deleted Title form extra params
				$extra = $event->getExtra();
				if ( isset( $extra['title'] ) && $extra['title'] instanceof \Title ) {
					$title = $extra['title'];
					$users = self::getWatchers( $title->getPrefixedText() );
				}
				break;
		}

		return $users;
	}

	protected static function getWatchers( $prefixedTitle ) {
		$watchers = [];
		$wlStore = new Store( \RequestContext::getMain() );
		$readerParams = new ReaderParams( [
			'filter' => [ [
				'comparison' => 'eq',
				'property' => Record::PAGE_PREFIXED_TEXT,
				'value' => $prefixedTitle,
				'type' => 'string'
			] ]
		] );
		$records = $wlStore->getReader()->read( $readerParams );

		foreach ( $records->getRecords() as $record ) {
			$userId = $record->get( Record::USER_ID, false );
			if ( $userId ) {
				$user = \User::newFromId( $userId );
				$user->load();
				if ( $user->isAnon() ) {
					continue;
				}
				$watchers[$user->getId()] = $user;
			}
		}

		return $watchers;
	}

	protected static function getUsersFromGroups( $groups ) {
		if ( !is_array( $groups ) ) {
			$conds = 'ug_group = "' . $groups . '"';
		} else {
			$conds = [];
			foreach ( $groups as $group ) {
				$conds[] = 'ug_group = "' . $group . '"';
			}
			$conds = implode( ' OR ', $conds );
		}

		$users = [];
		$dbr = wfGetDB( ILoadBalancer::DB_REPLICA );
		$resSysops = $dbr->select( "user_groups", "ug_user", $conds );

		foreach ( $resSysops as $row ) {
			$user = \User::newFromId( $row->ug_user );
			$user->load();
			if ( $user->isAnon() ) {
				continue;
			}
			$users[ $user->getId() ] = $user;
		}
		return $users;
	}

	protected static function getAllSubscribed() {
		$subscribers = [];
		$dbr = wfGetDB( ILoadBalancer::DB_REPLICA );
		$resUser = $dbr->select(
			"user_properties",
			"DISTINCT up_user",
			[
				"up_property" => [
					"echo-subscriptions-web-bs-page-actions-cat",
					"echo-subscriptions-email-bs-page-actions-cat"
				],
				"up_value" => 1
			]
		);

		foreach ( $resUser as $row ) {
			$user = \User::newFromId( $row->up_user );
			$user->load();
			if ( $user->isAnon() ) {
				continue;
			}
			$subscribers[ $user->getId() ] = $user;
		}

		return $subscribers;
	}
}
