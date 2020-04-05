<?php

namespace BlueSpice\TagCloud\Data\TagCloud\Category;

use \BlueSpice\Data\IPrimaryDataProvider;
use BlueSpice\Data\FilterFinder;
use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\StringValue;
use BlueSpice\Data\Filter\ListValue;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\TagCloud\Data\TagCloud\Schema;
use BlueSpice\TagCloud\Data\TagCloud\Record;
use BlueSpice\TagCloud\Context;

class PrimaryDataProvider implements IPrimaryDataProvider {

	/**
	 *
	 * @var Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var Context
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 */
	public function __construct( $db, Context $context ) {
		$this->db = $db;
		$this->context = $context;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 */
	public function makeData( $params ) {
		$this->data = [];

		$res = $this->db->select(
			'categorylinks',
			[ Record::NAME => 'cl_to', Record::COUNT => 'COUNT(cl_to)'],
			$this->makePreFilterConds( $params ),
			__METHOD__,
			$this->makePreOptionConds( $params )
		);
		foreach( $res as $row ) {
			if( count( $this->data) >= $params->getLimit() ) {
				break;
			}
			$this->appendRowToData( $row );
		}

		return $this->data;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( $params ) {
		$conds = [];
		$schema = new Schema();
		$fields = array_values( $schema->getFilterableFields() );
		$filterFinder = new FilterFinder( $params->getFilter() );
		foreach( $fields as $fieldName ) {
			$filter = $filterFinder->findByField( $fieldName );
			if( !$filter instanceof Filter ) {
				continue;
			}
			if( $filter instanceof ListValue ) {
				$values = implode( "','", $filter->getValue() );
				$name = $this->aliasToFieldName( $fieldName );
				if( $filter->getComparison() === ListValue::COMPARISON_CONTAINS ) {
					$conds[$name] = $fieldName;
					$filter->setApplied();
					continue;
				}
				if( $filter->getComparison() === ListValue::COMPARISON_NOT_CONTAINS ) {
					$conds[] = "$name NOT IN ('$values')";
					$filter->setApplied();
					continue;
				}
				
			}
			switch ( $filter->getComparison() ) {
				case Numeric::COMPARISON_GREATER_THAN:
					$conds[] = "$fieldName > {$filter->getValue()}";
					break;
				case Numeric::COMPARISON_LOWER_THAN:
					$conds[] = "$fieldName < {$filter->getValue()}";
					break;
				case StringValue::COMPARISON_CONTAINS:
					$conds[] = $this->db->buildLike(
						$this->db->anyString(),
						$fieldName,
						$this->db->anyString()
					);
					break;
				case StringValue::COMPARISON_NOT_EQUALS:
				case Numeric::COMPARISON_NOT_EQUALS:
					$conds[] = "$fieldName != {$filter->getValue()}";
					break;
				case StringValue::COMPARISON_EQUALS:
				case Numeric::COMPARISON_EQUALS:
				default:
					$conds[$fieldName] = $filter->getValue();
			}
			$filter->setApplied();
		}

		return $conds;
	}

	/**
	 *
	 * @param \BlueSpice\Data\ReaderParams $params
	 * @return array
	 */
	protected function makePreOptionConds( $params ) {
		$conds = [
			'GROUP BY' => 'cl_to',
			//'LIMIT' => $params->getLimit(),
			'ORDER BY' => 'COUNT(cl_to) DESC'
		];

		return $conds;
	}

	protected function appendRowToData( $row ) {
		$title = \Title::newFromText( $row->{Record::NAME}, NS_CATEGORY );
		if( !$title || !$title->userCan( 'read', $this->context->getUser() ) ) {
			return;
		}
		$this->data[] = new Record( (object) [
			Record::NAME => $title->getText(),
			Record::COUNT => (int)$row->{Record::COUNT},
			Record::LINK => '',
		] );
	}

	/**
	 * cause of mysql alias resons -.-
	 * @param type $alias
	 */
	protected function aliasToFieldName( $alias ) {
		switch ( $alias ) {
			case Record::NAME:
				$alias = 'cl_to';
				break;
			case Record::COUNT:
				$alias = 'count(cl_to)';
				break;
			default:
		}
		return $alias;
	}
}
