<?php

namespace BlueSpice\NamespaceCSS\Maintenance;

use BlueSpice\Services;
use BlueSpice\NamespaceCSS\Helper;

class MoveToDotCSS extends \LoggedUpdateMaintenance {

	protected function doDBUpdates() {
		$toMigrate = $this->collectTitles();

		if( count( $toMigrate ) < 1 ) {
			$this->output(
				"bs-namespace-css-move-to-dot-css -> No data to migrate\n"
			);
			return true;
		}
		$this->output(
			"...bs-namespace-css-move-to-dot-css -> migration...\n\n"
		);
		foreach( $toMigrate as $idx => $titles ) {
			$this->output(
				"$idx: {$titles['old']->getFullText()} => {$titles['new']->getFullText()}..."
			);
			try {
				$move = new \MovePage( $titles['old'], $titles['new'] );
				$status = $move->move(
					$this->getUser(),
					$this->getReason(),
					false
				);
			} catch( \Exception $e ) {
				$this->output( "... failed: {$e->getMessage()}\n" );
			}
			if( !$status->isOK() ) {
				$this->output( "... failed: {$status->getMessage()}\n" );
			}
			$this->output( "... OK\n" );
		}

		return true;
	}

	protected function collectTitles() {
		$toMigrate = [];
		foreach( \MWNamespace::getCanonicalNamespaces() as $idx => $ns ) {
			if( \MWNamespace::isTalk( $idx ) ) {
				continue;
			}
			if( !$title = $this->buildOldTitleFromNamespaceIndex( $idx ) ) {
				continue;
			}
			if( !$title->exists() ) {
				continue;
			}
			if( !$newTitle = Helper::buildTitleFromNamespaceIndex( $idx ) ) {
				continue;
			}
			$toMigrate[$idx] = [
				'old' => $title,
				'new' => $newTitle,
			];
		}
		return $toMigrate;
	}

	protected function getReason() {
		return 'Moved by Updater ' . __CLASS__;
	}

	protected function getUser() {
		return Services::getInstance()->getBSUtilityFactory()
			->getMaintenanceUser()->getUser();
	}

	protected function buildOldTitleFromNamespaceIndex( $idx ) {
		if( !$text = Helper::buildNamespaceNameFromNamespaceIndex( $idx ) ) {
			return false;
		}
		return \Title::newFromText( $text . "_css", NS_MEDIAWIKI );
	}

	protected function getUpdateKey() {
		return 'bs-namespace-css-move-to-dot-css3';
	}

}