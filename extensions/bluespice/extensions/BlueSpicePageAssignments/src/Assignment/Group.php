<?php
namespace BlueSpice\PageAssignments\Assignment;

use BlueSpice\Services;

class Group extends \BlueSpice\PageAssignments\Assignment {

	protected static $userIdCache = [];

	protected function makeAnchor() {
		return $this->linkRenderer->makeLink(
			\Title::makeTitle( NS_PROJECT, $this->getText() ),
			new \HtmlArmor( $this->getText() )
		);
	}

	public function getText() {
		return \Message::newFromKey( "group-{$this->getKey()}" )->exists()
			? \Message::newFromKey( "group-{$this->getKey()}" )->plain()
			: $this->getKey();
	}

	public function getUserIds() {
		if( isset( static::$userIdCache[$this->getKey()] ) ) {
			return static::$userIdCache[$this->getKey()];
		}
		static::$userIdCache[$this->getKey()] = [];

		$loadBalancer = Services::getInstance()->getDBLoadBalancer();
		$res = $loadBalancer->getConnection( DB_REPLICA )->select(
			'user_groups',
			'ug_user',
			[
				'ug_group' => $this->getKey()
			]
		);
		foreach ( $res as $row ) {
			$allowed = $this->getTitle()->userCan(
				'pageassignable',
				\User::newFromId( (int)$row->ug_user )
			);
			if( !$allowed ) {
				continue;
			}
			static::$userIdCache[$this->getKey()][] = (int)$row->ug_user;
		}

		return static::$userIdCache[$this->getKey()];
	}

}