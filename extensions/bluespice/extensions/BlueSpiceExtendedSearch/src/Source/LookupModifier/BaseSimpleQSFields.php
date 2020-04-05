<?php

namespace BS\ExtendedSearch\Source\LookupModifier;

class BaseSimpleQSFields extends Base {

	/**
	 * Adds fields that will be searched including query-time boosting
	 */
	public function apply() {
		$simpleQS = $this->oLookup->getQueryString();
		$fields = ['basename^4', 'congregated'];
		if( isset( $simpleQS['fields'] ) && is_array( $simpleQS['fields'] ) ) {
			$simpleQS['fields'] = array_merge( $simpleQS['fields'], $fields );
		} else {
			$simpleQS['fields'] = $fields;
		}

		$this->oLookup->setQueryString( $simpleQS );
	}

	public function undo() {
		$simpleQS = $this->oLookup->getQueryString();

		if( isset( $simpleQS['fields'] ) && is_array( $simpleQS['fields'] ) ) {
			$simpleQS['fields'] = array_diff( $simpleQS['fields'], ['basename^4', 'congregated'] );
		}

		$this->oLookup->setQueryString( $simpleQS );
	}

}
