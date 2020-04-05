<?php

namespace BlueSpice\PageAssignments\Api\Task;

use BlueSpice\EchoConnector\PresentationModel\AssignmentChangeAdd;
use BlueSpice\Services;
use BlueSpice\PageAssignments\IAssignment;
use BlueSpice\PageAssignments\Notifications;

class PageAssignments extends \BSApiTasksBase {

	protected $sTaskLogType = 'bs-pageassignments';

	protected $aTasks = [
		'edit' => [
			'examples' => [
				[
					'pageId' => 152,
					'pageAssignments' => [
						'user/WikiSysop',
						'group/bot'
					]
				]
			],
			'params' => [
					'pageId' => [
						'desc' => 'ID of a page assignment is created for',
						'type' => 'integer',
						'required' => true
					],
					'pageAssignments' => [
						'desc' => 'Array of strings in form of "key/value", eg. "user/WikiSysop" or "group/sysop", can be empty',
						'type' => 'array',
						'required' => true
					]
			]
		],
		'getForPage' => [
			'examples' => [
				[
					'pageId' => 152
				]
			],
			'params' => [
				'pageId' => [
					'desc' => 'ID of a page to get assignments for',
					'type' => 'integer',
					'required' => true
				]
			]
		]
	];

	protected function getRequiredTaskPermissions() {
		return [
			'edit' => [ 'pageassignments' ],
			'getForPage' => [ 'read' ],
		];
	}

	protected function task_edit( $taskData, $params ) {
		$result = $this->makeStandardReturn();

		if( empty( $taskData->pageId ) ) {
			$taskData->pageId = 0;
		}
		$status = $this->getTargetFromID( $taskData->pageId );
		if( !$status->isOK() ) {
			$result->message = $status->getMessage()->parse();
			return $result;
		}
		$target = $status->getValue();

		$assignments = [];
		foreach( $taskData->pageAssignments as $id ) {
			//'user/WikiSysop' or 'group/bureaucrats'
			list( $type, $key ) = explode( '/', $id );
			if( empty( $type ) || empty( $key ) ) {
				continue;
			}
			$assignment = $this->getFactory()->factory(
				$type,
				$key,
				$target->getTitle()
			);
			if( !$assignment ) {
				continue;
			}
			$assignments[] = $assignment;
		}
		$status = $target->save( $assignments );
		if( !$status->isOK() ) {
			$result->message = $status->getMessage()->parse();
			return $result;
		}

		$removed = $target->diff(
			$target->getAssignments(),
			$status->getValue()->getAssignments()
		);
		$added = $target->diff(
			$status->getValue()->getAssignments(),
			$target->getAssignments()
		);
		$result->success = true;
		$this->logAssignmentChange(
			$target->getTitle(),
			$added,
			$removed
		);
		$this->notifyAssignmentChange(
			$target->getTitle(),
			$added,
			$removed
		);
		$this->runUpdates( $target->getTitle() );

		return $result;
	}

	/**
	 * This is a convenience method. It could also be done by quering
	 * 'bs-pageassignment-store' with the right set of filters, but this one
	 * is much easier to access
	 * @param object $taskData
	 * @param array $params
	 * @return BSStandardAPIResponse
	 */
	protected function task_getForPage( $taskData, $params ) {
		$result = $this->makeStandardReturn();

		if( empty( $taskData->pageId ) ) {
			$taskData->pageId = 0;
		}
		$status = $this->getTargetFromID( $taskData->pageId );
		if( !$status->isOK() ) {
			$result->message = $status->getMessage();
			return $result;
		}
		$target = $status->getValue();

		$result->payload = [];
		foreach( $target->getAssignments() as $assignment ) {
			$assignment = $assignment->toStdClass();
			$assignment->assignee_image_html = $this->getAssigneeThumb( $assignment );
			$result->payload[] = $assignment;
		}
		$result->success = true;

		return $result;
	}

	/**
	 *
	 * @param \stdClass $assignment
	 * @return string
	 */
	protected function getAssigneeThumb( $assignment ) {
		$factory = \BlueSpice\Services::getInstance()->getBSRendererFactory();
		$thumbParams = [ 'width' => '32', 'height' => '32' ];

		if( $assignment->pa_assignee_type == 'group' ) {
			$image = $factory->get( 'groupimage', new \BlueSpice\Renderer\Params( [
				'group' => $assignment->pa_assignee_key
			] + $thumbParams ) );
			return $image->render();
		}

		$user = \User::newFromName( $assignment->pa_assignee_key );
		if( $user instanceof \User === false ) {
			return '';
		}

		$image = $factory->get( 'userimage', new \BlueSpice\Renderer\Params( [
			'user' => $user
		] + $thumbParams ) );

		return $image->render();
	}

	/**
	 *
	 * @param \Title $title
	 * @param IAssignment[] $addedAssignments
	 * @param IAssignment[] $removedAssignments
	 */
	public function logAssignmentChange( $title, $addedAssignments, $removedAssignments ) {
		foreach( $addedAssignments as $assignment ) {
			$this->logTaskAction(
				"add-{$assignment->getType()}",
				[ '4::editor' => $assignment->getKey() ],
				[ 'target' => $title ]
			);
		}
		foreach( $removedAssignments as $assignment ) {
			$this->logTaskAction(
				"remove-{$assignment->getType()}",
				[ '4::editor' => $assignment->getKey() ],
				[ 'target' => $title ]
			);
		}
	}

	public function notifyAssignmentChange( $title, $addedAssignments, $removedAssignments ) {
		$newUsers = [];
		$removedUsers = [];

		$notificationsManager = \BlueSpice\Services::getInstance()->getBSNotificationManager();

		$notifier = $notificationsManager->getNotifier();

		if( !$notifier ) {
			return true;
		}

		foreach( $addedAssignments as $assignment ) {
			$newUsers = array_merge(
				$newUsers,
				$assignment->getUserIds()
			);
		}

		foreach( $removedAssignments as $assignment ) {
			$removedUsers = array_merge(
				$removedUsers,
				$assignment->getUserIds()
			);
		}

		if( !empty( $newUsers ) ) {
			$notification = new Notifications\AssignmentChangeAdd( $this->getUser(), $title, $newUsers );
			$notifier->notify( $notification );
		}

		if( !empty( $removedUsers ) ) {
			$notification = new Notifications\AssignmentChangeRemove( $this->getUser(), $title, $removedUsers );
			$notifier->notify( $notification );
		}
	}

	/**
	 *
	 * @return \BlueSpice\PageAssignments\AssignmentFactory
	 */
	protected function getFactory() {
		return Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
	}

	/**
	 *
	 * @param integer $pageId
	 * @return \Status
	 */
	protected function getTargetFromID( $pageId ) {
		$title = \Title::newFromID( $pageId );
		if( !$title || !$title->exists() ) {
			return \Status::newFatal( 'bs-pageassignments-api-error-no-page' );
		}
		return $this->getTargetFromTitle( $title );
	}

	/**
	 *
	 * @param \Title $title
	 * @return \Status
	 */
	protected function getTargetFromTitle( \Title $title ) {
		if( !$target = $this->getFactory()->newFromTargetTitle( $title ) ) {
			return \Status::newFatal( 'bs-pageassignments-api-error-no-page' );
		}
		return \Status::newGood( $target );
	}

}
