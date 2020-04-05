<?php
namespace BlueSpice\Calumma\Components;

use BlueSpice\Calumma\TemplateComponent;

class SearchForm extends TemplateComponent {

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Components.SearchForm';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$args['id'] = $this->getSkinTemplate()->get( 'bs_search_id' );
		$args['searchInput'] = $this->getSkinTemplate()->get( 'bs_search_input' );
		$args['hiddenFields'] = $this->getSkinTemplate()->get( 'bs_search_hidden_fields' );
		$args['action'] = $this->getSkinTemplate()->get( 'bs_search_action' );
		$args['method'] = $this->getSkinTemplate()->get( 'bs_search_method' );
		return $args;
	}
}
