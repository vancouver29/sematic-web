<?php

namespace BlueSpice\ArticleInfo\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Hook\BsAdapterAjaxPingResult;
use Title;
use Revision;

class HandleArticleInfo extends BsAdapterAjaxPingResult {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	protected function skipProcessing() {
		if ( $this->reference !== 'ArticleInfo' ) {
			return true;
		}

		$this->title = Title::newFromId( $this->articleId );
		if ( $this->title === null ) {
			return true;
		}

		if ( !$this->title->exists() ) {
			return true;
		}

		if ( !$this->title->userCan( 'read' ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->singleResults['success'] = true;

		if ( $this->params[0] !== 'checkRevision' ) {
			return true;
		}

		$this->singleResults['newRevision'] = false;
		if ( $this->providedRevisionUpToDate() ) {
			return true;
		}
		if ( $this->currentUserIsSaving() ) {
			return true;
		}
		$this->singleResults['newRevision'] = true;
		$this->singleResults['checkRevisionView'] =
			wfMessage( 'bs-articleinfo-newrevision-info-text' )->plain();

		return true;
	}

	protected function providedRevisionUpToDate() {
		return $this->revisionId === $this->title->getLatestRevID();
	}

	protected function currentUserIsSaving() {
		if ( $this->params[1] !== 'edit' ) {
			return false;
		}

		$user = $this->getContext()->getUser();
		if ( $user->isAnon() ) {
			return false;
		}

		$revision = Revision::newFromId( $this->revisionId );
		if ( $revision->getUserText() !== $user->getName() ) {
			return false;
		}

		return true;
	}

}
