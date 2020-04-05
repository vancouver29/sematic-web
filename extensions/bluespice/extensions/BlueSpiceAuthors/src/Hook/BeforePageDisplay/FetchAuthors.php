<?php

namespace BlueSpice\Authors\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class FetchAuthors extends BeforePageDisplay {

	protected function skipProcessing() {
		$config = $this->getConfig();
		if ( $config->get( 'AuthorsShow' ) === false ) {
			return true;
		}
		$title = $this->out->getTitle();
		if ( !$title->exists() || !$title->userCan( 'read' ) ) {
			return true;
		}

		if ( $this->out->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}

		if ( in_array( $title->getNamespace(), [ NS_SPECIAL, NS_CATEGORY, NS_FILE ] ) ) {
			return true;
		}

		// Do not display if __NOAUTHORS__ keyword is found
		$noAuthors = \BsArticleHelper::getInstance( $title )->getPageProp( 'bs_noauthors' );
		if ( $noAuthors === '' ) {
			return true;
		}
	}

	protected function doProcess() {
		$list = new \BlueSpice\Authors\AuthorsList(
			$this->out->getTitle(),
			$this->getConfig()->get( 'AuthorsBlacklist' )
		);

		$revision = $this->out->getTitle()->getFirstRevision();
		$originator = $list->getOriginator(
			$revision
		);

		$editors = $list->getEditors();

		$authors = [ 'authors' => [] ];

		if ( $originator !== '' ) {
			$user = \User::newFromName( $originator );
			if ( $user instanceof \User ) {
				$authors['authors'][] = [
					'user_image_html' => $this->makeImage( $user ),
					'user_name' => $user->getName(),
					'author_type' => 'originator'
				];
			}
		}

		foreach ( $editors as $editor ) {
			$user = \User::newFromName( $editor );
			if ( $user instanceof \User === false ) {
				continue;
			}
			$authors['authors'][] = [
				'user_image_html' => $this->makeImage( $user ),
				'user_name' => $user->getName(),
				'author_type' => 'editor'
			];
		}

		$this->out->addJsConfigVars( [
			'bsgPageAuthors' => $authors
		] );

		return true;
	}

	/**
	 *
	 * @param \User $user
	 * @return string
	 */
	protected function makeImage( $user ) {
		$factory = \BlueSpice\Services::getInstance()->getBSRendererFactory();
		$image = $factory->get( 'userimage', new \BlueSpice\Renderer\Params( [
			'user' => $user,
			'width' => "48",
			'height' => "48"
		] ) );

		return $image->render();
	}
}
