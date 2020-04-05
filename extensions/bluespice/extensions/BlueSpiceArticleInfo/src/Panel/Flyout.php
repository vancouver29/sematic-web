<?php

namespace BlueSpice\ArticleInfo\Panel;

use BlueSpice\Calumma\Panel\BasePanel;
use BlueSpice\Calumma\IFlyout;

class Flyout extends BasePanel implements IFlyout {
	/**
	 * RL modules that are added by other extensions
	 * wanting to show their info in this flyout
	 *
	 * @var array
	 */
	protected $modulesToLoad = [ 'ext.bluespice.articleinfo.flyout' ];

	/**
	 * Callback function to allow other extesions to
	 * insert content
	 *
	 * @var array
	 */
	protected $makeItemsCallbacks = [];

	/**
	 * @var \Title
	 */
	protected $title;

	/**
	 * @var \MediaWiki\Storage\RevisionStore
	 */
	protected $revisionStore;

	/**
	 * @var \Article
	 */
	protected $article;

	/**
	 *
	 * @param \SkinTemplate $skintemplate
	 */
	public function __construct( $skintemplate ) {
		parent::__construct( $skintemplate );
		$this->setForeignModules();
		$this->title = $skintemplate->getSkin()->getTitle();
	}

	/**
	 * @return \Message
	 */
	public function getFlyoutTitleMessage() {
		return wfMessage( 'bs-articleinfo-flyout-title' );
	}

	/**
	 * @return \Message
	 */
	public function getFlyoutIntroMessage() {
		return wfMessage( 'bs-articleinfo-flyout-intro' );
	}

	/**
	 * @return \Message
	 */
	public function getTitleMessage() {
		return wfMessage( 'bs-articleinfo-nav-link-title' );
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return '';
	}

	/**
	 *
	 * @return array
	 */
	public function getContainerData() {
		$data = [
			'make-items-callbacks' => \FormatJson::encode( $this->makeItemsCallbacks )
		];

		$lastEditedTime = $this->getLastEditedTime();
		if ( $lastEditedTime ) {
			$data['last-edited-time'] = $lastEditedTime;
		}

		$lastEditor = $this->getLastEditedUser();
		if ( $lastEditor ) {
			$data['last-edited-user'] = $lastEditor;
		}

		$categoryLinks = $this->getCategoryLinks();
		if ( $categoryLinks ) {
			$data['category-links'] = $categoryLinks;
		}

		$templateLinks = $this->getTemplateLinks();
		if ( $templateLinks ) {
			$data['template-links'] = $templateLinks;
		}

		$data['has-subpages'] =
			$this->skintemplate->getSkin()->getTitle()->hasSubpages();

		$data['user-can-edit'] =
			$this->skintemplate->getSkin()->getTitle()->userCan( 'edit' );

		return $data;
	}

	/**
	 *
	 * @return string
	 */
	public function getTriggerCallbackFunctionName() {
		return 'bs.articleinfo.flyoutCallback';
	}

	/**
	 *
	 * @return array
	 */
	public function getTriggerRLDependencies() {
		return $this->modulesToLoad;
	}

	/**
	 *
	 */
	protected function setForeignModules() {
		$flyoutModuleRegistry = \ExtensionRegistry::getInstance()
			->getAttribute( 'BlueSpiceArticleInfoFlyoutModules' );

		foreach ( $flyoutModuleRegistry as $key => $module ) {
			if ( isset( $module['skip-callback'] ) && is_callable( $module['skip-callback'] ) ) {
				$result = call_user_func_array(
					$module['skip-callback'],
					[ $this->skintemplate->getSkin()->getContext() ]
				);

				if ( !$result ) {
					continue;
				}
			}

			$this->modulesToLoad[] = $module['module'];
			$this->makeItemsCallbacks[] = $module['make-items-callback'];
		}
	}

	/**
	 *
	 * @return \MediaWiki\Storage\RevisionStore
	 */
	protected function getRevisionStore() {
		if ( $this->revisionStore === null ) {
			$this->revisionStore = \MediaWiki\MediaWikiServices::getInstance()->getRevisionStore();
		}
		return $this->revisionStore;
	}

	/**
	 *
	 * @return \Article
	 */
	protected function getArticle() {
		if ( $this->article === null ) {
			$this->article = \Article::newFromID( $this->title->getArticleID() );
		}
		return $this->article;
	}

	/**
	 * Gets info on last time page was edited
	 *
	 * @return string|false if cannot be retrieved
	 */
	protected function getLastEditedTime() {
		$article = $this->getArticle();
		$oldId = $this->skintemplate->getSkin()->getRequest()->getInt( 'oldid', 0 );

		if ( $article instanceof \Article == false ) {
			return false;
		}

		if ( $oldId != 0 ) {
			$this->getRevisionStore();
			$timestamp = $this->revisionStore->getTimestampFromId( $article->getTitle(), $oldId );
		} else {
			$timestamp = $article->getTimestamp();
		}

		$formattedTimestamp = \BsFormatConverter::mwTimestampToAgeString( $timestamp, true );
		$articleHistoryLinkURL = $article->getTitle()->getLinkURL(
			[
				'diff'   => 0,
				'oldid' => $oldId
			]
		);

		return \FormatJson::encode( [
			'timestamp' => $formattedTimestamp,
			'url' => $articleHistoryLinkURL
		] );
	}

	/**
	 * Gets info on last editor of the page
	 *
	 * @return string|false if cannot be retrieved
	 */
	protected function getLastEditedUser() {
		$article = $this->getArticle();
		if ( $article instanceof \Article === false || $article->getUserText() == '' ) {
			return false;
		}

		$lastEditor = \User::newFromName( $article->getUserText() );
		if ( $lastEditor instanceof \User === false ) {
			return false;
		}

		$userHelper = \BlueSpice\Services::getInstance()->getBSUtilityFactory()
			->getUserHelper( $lastEditor );

		return \FormatJson::encode( [
			'userText' => $userHelper->getDisplayName(),
			'url' => $lastEditor->getUserPage()->getFullURL()
		] );
	}

	/**
	 * Gets all categories of the page
	 * @return string
	 */
	protected function getCategoryLinks() {
		$allPageCategoryLinks = $this->skintemplate->getSkin()->getOutput()->getCategoryLinks();

		$pageCategoryLinks = [];
		if ( isset( $allPageCategoryLinks['normal'] ) ) {
			$pageCategoryLinks = $allPageCategoryLinks['normal'];
		}

		if ( $this->skintemplate->getSkin()->getUser()->getBoolOption( 'showhiddencats' ) ) {
			if ( isset( $allPageCategoryLinks['hidden'] ) ) {
				$pageCategoryLinks = array_merge(
					$pageCategoryLinks,
					$allPageCategoryLinks['hidden']
				);
			}
		}

		// This is just so that the ExtJS store can handle it more easily
		$keyedCategoryLinks = [];
		foreach ( $pageCategoryLinks as $categoryLink ) {
			$keyedCategoryLinks[] = [
				'category_anchor' => $categoryLink,
				'class' => 'pill'
			];
		}

		return \FormatJson::encode( $keyedCategoryLinks );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplateLinks() {
		$linkRenderer = \MediaWiki\MediaWikiServices::getInstance()->getLinkRenderer();
		$templateTitles = $this->title->getTemplateLinksFrom();

		$templateLinks = [];
		foreach ( $templateTitles as $title ) {
			$templateLinks[] = [
				'template_anchor' => $linkRenderer->makeLink( $title, $title->getText() )
			];
		}

		return \FormatJson::encode( $templateLinks );
	}
}
