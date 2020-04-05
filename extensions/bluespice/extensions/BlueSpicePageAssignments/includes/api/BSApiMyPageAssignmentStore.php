<?php

use BlueSpice\Services;
use BlueSpice\Data\ReaderParams;
use BlueSpice\PageAssignments\Data\Record;

class BSApiMyPageAssignmentStore extends BSApiExtJSStoreBase {

	protected function makeData($sQuery = '') {

		$assignmentsPerPage = $this->getPageAssignments();

		$aResult = $assignedBy = [];
		foreach( $assignmentsPerPage as $pageId => $pageAssignments ) {
			if( !\Title::newFromID( $pageId ) ) {
				continue;
			}
			foreach( $pageAssignments as $assignment ) {
				$assigned = in_array(
					$this->getUser()->getId(),
					$assignment->getUserIds()
				);
				if( !$assigned ) {
					continue;
				}
				$assignedBy[ $pageId ][] = $assignment;
			}
		}
		foreach( $assignedBy as $pageId => $relatedAssignments ) {
			$title = \Title::newFromID( $pageId );
			$link = Services::getInstance()->getLinkRenderer()->makeLink(
				$title
			);
			$oDataSet = (object) [
				'page_id' => $title->getArticleID(),
				'page_prefixedtext' => $title->getPrefixedText(),
				'page_link' => $link,
				'assigned_by' => [],
				'assignment' => [],
			];
			foreach( $relatedAssignments as $assignment ) {
				$oDataSet->assigned_by[] = $assignment->getType();
				$oDataSet->assignment[] = $assignment->toStdClass();
			}
			$aResult[] = $oDataSet;
		}
		return $aResult;
	}

	public function filterString($oFilter, $aDataSet) {
		if( $oFilter->field !== 'assigned_by') {
			return parent::filterString($oFilter, $aDataSet);
		}

		$sFieldValue = '';
		foreach( $aDataSet->assigned_by as $oAsignee ) {
			$sFieldValue .= $oAsignee->text;
		}

		return BsStringHelper::filter( $oFilter->comparison, $sFieldValue, $oFilter->value );
	}

	/**
	 *
	 * @return \BlueSpice\PageAssignments\IAssignment[]
	 */
	protected function getPageAssignments() {
		$assignmentFactory = Services::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);
		$recordSet = $assignmentFactory->getStore()->getReader()->read(
			new ReaderParams( [] )
		);
		$assignments = [];
		foreach( $recordSet->getRecords() as $record ) {
			$id = $record->get( Record::PAGE_ID );
			$assignments[$id][] = $assignmentFactory->factory(
				$record->get( Record::ASSIGNEE_TYPE ),
				$record->get( Record::ASSIGNEE_KEY ),
				\Title::newFromID( $id )
			);
		}
		return $assignments;
	}
}
