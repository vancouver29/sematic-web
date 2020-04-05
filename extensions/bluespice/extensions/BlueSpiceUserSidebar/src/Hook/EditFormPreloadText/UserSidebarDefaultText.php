<?php

namespace BlueSpice\UserSidebar\Hook\EditFormPreloadText;

use BlueSpice\Hook\EditFormPreloadText;

class UserSidebarDefaultText extends EditFormPreloadText {

	protected function skipProcessing() {
		$user = $this->getContext()->getUser();
		$userPage =\Title::makeTitle( NS_USER, $user->getName() . "/Sidebar" );
		if ( $this->title->equals( $userPage ) ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		$this->text = implode( "\n", $this->getWidgetLinks() );
		return true;
	}

	protected function getWidgetLinks() {
		$widgetRegistry = \ExtensionRegistry::getInstance()->getAttribute( 'BlueSpiceUserSidebarWidgets' );
		$widgets = [];
		foreach( $widgetRegistry as $key => $config ) {
			if( !$config['default'] ) {
				continue;
			}
			$widgets[] = "* $key";
		}
		return $widgets;
	}
}