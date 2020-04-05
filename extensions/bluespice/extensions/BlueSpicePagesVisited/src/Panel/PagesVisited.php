<?php

namespace BlueSpice\PagesVisited\Panel;

use BlueSpice\Calumma\IPanel;
use BlueSpice\Calumma\Panel\BasePanel;

class PagesVisited extends BasePanel implements IPanel {
	protected $params = [];

	public static function factory( $sktemplate, $params ) {
		return new self( $sktemplate, $params );
	}

	public function __construct( $skintemplate, $params ) {
		parent::__construct( $skintemplate );
		$this->params = $params;
	}

	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-pagesvisited-widget-title' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		$count = $this->getUser()->getOption( 'bs-pagesvisited-widgetlimit' );
		if( isset( $this->params['count'] ) ) {
			$count = (int) $this->params['count'];
		}

		if( isset( $this->params['namespaces'] ) ) {
			$namespaces = $this->params['namespaces'];
		} else {
			$namespaces = $this->getUser()->getOption( 'bs-pagesvisited-widgetns' );
			$namespaces = explode( '|', $namespaces );
		}

		$sortOrder = $this->getUser()->getOption( 'bs-pagesvisited-widgetsortodr' );

		if( isset( $this->params['order'] ) ) {
			$sortOrder = $this->params['order'];
		}

		//Dumb default
		$maxTitleLength = 20;
		if( isset( $this->params['maxtitlelength'] ) ) {
			$maxTitleLength = (int) $this->params['maxtitlelength'];
		}

		//Validation
		$validationICount = \BsValidator::isValid(
			'IntegerRange',
			$count,
			[
				'fullResponse' => true,
				'lowerBoundary' => 1,
				'upperBoundary' => 30
			]
		);

		if ( $validationICount->getErrorCode() ) $count = 10;

		$currentNamespaceId = $this->getTitle()->getNamespace();

		$pagesVisited = $this->getPagesVisited(
			$count,
			implode( ',', $namespaces ),
			$currentNamespaceId,
			$maxTitleLength,
			$sortOrder
		);

		if( isset( $pagesVisited['error'] ) ) {
			return "<div class='widget-error'>" . $pagesVisited['error'] . "</div>";
		}

		$links = [];
		foreach( $pagesVisited as $pageVisited ) {
			$link = [
				'href' => $pageVisited['title']->getFullURL(),
				'text' => $pageVisited['displayText'],
				'title' => $pageVisited['title']->getPrefixedText(),
				'classes' => ' bs-usersidebar-internal '
			];
			$links[] = $link;
		}

		$linkListGroup = new \BlueSpice\Calumma\Components\SimpleLinkListGroup( $links );

		return $linkListGroup->getHtml();
	}

	protected function getUser() {
		return $this->skintemplate->getSkin()->getUser();
	}

	protected function getTitle() {
		return $this->skintemplate->getSkin()->getTitle();
	}

	protected function getPagesVisited( $count = 5, $namespaces = 'all', $currentNamespaceId = 0, $maxTitleLength = 20, $sortOrder = 'time' ) {
		try {
			$namespaceIndexes = \BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $namespaces ); //Returns array of integer indexes
		} catch ( \BsInvalidNamespaceException $exception ) {
			$invalidNamespaces = $exception->getListOfInvalidNamespaces();

			$count = count( $invalidNamespaces );
			$namespaces = implode( ', ', $invalidNamespaces );
			return [
				'error' =>
				wfMessage( 'bs-pagesvisited-error-nsnotvalid', $count, $namespaces )->text()
			];
		}

		$conditions = array(
			'wo_user_id' => $this->getUser()->getId(),
			'wo_action' => 'view'
		);

		$conditions[] = 'wo_page_namespace IN ('.implode( ',', $namespaceIndexes ).')'; //Add IN clause to conditions-array
		//$conditions[] = 'wo_page_namespace != -1'; // TODO RBV (24.02.11 13:54): Filter SpecialPages because there are difficulties to list them

		$options = array(
			'GROUP BY' => 'wo_page_id, wo_page_namespace, wo_page_title',
			'ORDER BY' => 'MAX(wo_timestamp) DESC'
		);

		if ( $sortOrder == 'pagename' ) $options['ORDER BY'] = 'wo_page_title ASC';

		//If the page the extension is used on appears in the result set we have to fetch one row more than neccessary.
		if ( in_array( $currentNamespaceId, $namespaceIndexes ) ){
			$options['OFFSET'] = 1;
		}

		$fields = array( 'wo_page_id', 'wo_page_namespace', 'wo_page_title' );
		$table = 'bs_whoisonline';

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			$table,
			$fields,
			$conditions,
			__METHOD__,
			$options
		);

		$items = [];
		foreach ( $res as $row ) {
			if ( count( $items ) > $count ) {
				break;
			}
			if( (int)$row->wo_page_id < 1 ) {
				//skip special pages etc.
				continue;
			}
			if( !$title = \Title::newFromID( $row->wo_page_id ) ) {
				continue;
			}
			if ( !$title->exists() || !$title->quickUserCan( 'read' ) ) {
				continue;
			}

			$displayTitle = \BsStringHelper::shorten(
				$title->getPrefixedText(),
				array( 'max-length' => $maxTitleLength, 'position' => 'middle' )
			);

			$items[] = [
				'title' => $title,
				'displayText' => $displayTitle
			];
		}

		return $items;
	}
}
