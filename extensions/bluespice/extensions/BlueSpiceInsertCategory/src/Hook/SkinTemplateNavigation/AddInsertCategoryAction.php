<?php

namespace BlueSpice\InsertCategory\Hook\SkinTemplateNavigation;

class AddInsertCategoryAction extends \BlueSpice\Hook\SkinTemplateNavigation {

	protected function skipProcessing() {
		if ( $this->getContext()->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}
		if ( !$this->sktemplate->getTitle()->userCan( 'edit' ) ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$msg = \Message::newFromKey( 'bs-insertcategory-insertcat' );
		$this->links['actions']['insert_category'] = [
			'text' => $msg->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-insertcategory',
			'bs-group' => 'hidden'
		];
	}

}
