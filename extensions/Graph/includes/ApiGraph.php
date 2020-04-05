<?php
/**
 *
 * @license MIT
 * @file
 *
 * @author Yuri Astrakhan
 */

namespace Graph;

use ApiBase;
use FormatJson;
use Title;
use ParserOptions;

/**
 * This class implements action=graph api, allowing client-side graphs to get the spec,
 * regardless of how it is stored (page-props or other storage)
 * Class ApiGraph
 * @package Graph
 */
class ApiGraph extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();

		$this->requireOnlyOneParameter( $params, 'title', 'text' );

		if ( $params['title'] !== null ) {
			if ( $params['hash'] === null ) {
				$this->dieWithError( [ 'apierror-invalidparammix-mustusewith', 'title', 'hash' ],
					'missingparam' );
			}
			$graph = $this->getFromStorage( $params['title'], $params['hash'] );
		} else {
			if ( !$this->getRequest()->wasPosted() ) {
				$this->dieWithError( 'apierror-graph-mustposttext', 'mustposttext' );
			}
			if ( $params['hash'] !== null ) {
				$this->dieWithError( [ 'apierror-invalidparammix-cannotusewith', 'hash', 'text' ],
					'invalidparammix' );
			}
			$graph = $this->preprocess( $params['text'] );
		}

		$this->getMain()->setCacheMode( 'public' );
		$this->getResult()->addValue( null, $this->getModuleName(), $graph );
	}

	public function getAllowedParams() {
		return [
			'hash' => [
				ApiBase::PARAM_TYPE => 'string',
			],
			'title' => [
				ApiBase::PARAM_TYPE => 'string',
			],
			'text' => [
				ApiBase::PARAM_TYPE => 'string',
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'formatversion=2&action=graph&title=Extension%3AGraph%2FDemo' .
				'&hash=1533aaad45c733dcc7e07614b54cbae4119a6747' => 'apihelp-graph-example',
		];
	}

	/**
	 * Parse graph definition that may contain wiki markup into pure json
	 * @param string $text
	 * @return string
	 */
	private function preprocess( $text ) {
		global $wgParser;
		$title = Title::makeTitle( NS_SPECIAL, Sandbox::PAGENAME )->fixSpecialName();
		$text = $wgParser->getFreshParser()->preprocess( $text, $title, new ParserOptions() );
		$st = FormatJson::parse( $text );
		if ( !$st->isOK() ) {
			// Sometimes we get <graph ...> {...} </graph> as input. Try to strip <graph> tags
			$count = 0;
			$text = preg_replace( '/^\s*<graph[^>]*>(.*)<\/graph>\s*$/s', '$1', $text, 1, $count );
			if ( $count === 1 ) {
				$st = FormatJson::parse( $text );
			}
			if ( !$st->isOK() ) {
				$this->dieWithError( 'apierror-graph-invalid', 'invalidtext' );
			}
		}
		return $st->getValue();
	}

	/**
	 * Get graph definition with title and hash
	 * @param string $titleText
	 * @param string $hash
	 * @return string
	 */
	private function getFromStorage( $titleText, $hash ) {
		// NOTE: Very strange wgMemc feature: Even though we store the data structure into memcached
		// by JSON-encoding and gzip-ing it, when we get it out it is already in the original form.
		$graph = Store::getFromCache( $hash );
		if ( !$graph ) {
			$title = Title::newFromText( $titleText );
			if ( !$title ) {
				$this->dieWithError( [ 'apierror-invalidtitle', wfEscapeWikiText( $titleText ) ] );
			}
			if ( !$title->exists() ) {
				$this->dieWithError( 'apierror-missingtitle' );
			}
			$this->checkTitleUserPermissions( $title, 'read' );

			$ppValue = $this->getDB()->selectField( 'page_props', 'pp_value', [
				'pp_page' => $title->getArticleID(),
				'pp_propname' => 'graph_specs',
			], __METHOD__ );

			if ( $ppValue ) {
				// Copied from TemplateDataBlob.php:newFromDatabase()
				// Handle GZIP compression. \037\213 is the header for GZIP files.
				if ( substr( $ppValue, 0, 2 ) === "\037\213" ) {
					$ppValue = gzdecode( $ppValue );
				}
				$st = FormatJson::parse( $ppValue );
				if ( $st->isOK() ) {
					$allGraphs = $st->getValue();
					if ( !is_object( $allGraphs ) ) {
						wfDebugLog( 'AdHocDebug', "ApiGraph: invalid data on page '$titleText'" );
					}
					if ( is_object( $allGraphs ) && property_exists( $allGraphs, $hash ) ) {
						$graph = $allGraphs->$hash;
					}
				}
			}
		}
		if ( !$graph ) {
			$this->dieWithError( 'apierror-graph-missing', 'invalidhash' );
		}
		return $graph;
	}
}
