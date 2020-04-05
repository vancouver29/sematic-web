<?php

namespace BlueSpice\CountThings\Tag;

use BlueSpice\Tag\Handler;
use BlueSpice\Services;
use BlueSpice\Renderer\Params;
use BlueSpice\Renderer;
use BlueSpice\RendererFactory;

class CountCharactersHandler extends Handler {

	protected $tagInput = '';
	protected $tagArgs = [ 'mode' ];

	public function __construct( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		$this->tagInput = explode( ' ', trim($processedInput) );
		$this->tagArgs = $processedArgs;
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
	}

	public function magicWordBsCountCharacters() {
		$out = [];
		if ( isset( $this->tagArgs['mode']
			) ) {
			$mode = $this->tagArgs['mode'];
			if ( strpos( $mode, 'chars' ) === false &&
				strpos( $mode, 'words' ) === false &&
				strpos( $mode, 'pages' ) === false ) {
				$mode = 'all';
			}
		} else {
			$mode = 'all';
		}
		$titles = [];
		foreach ( $this->tagInput as $title ) {
			$titleInstance = \Title::newFromText( $title );
			if ( $titleInstance ) {
				$titles[$title] = $titleInstance;
			}
		}
		foreach ( $titles as $title => $titleInstance ) {
			$out[$title] = [];

			$content = $content = preg_replace(
				"/\s+/", " ", \BsPageContentProvider::getInstance()->
					getContentFromTitle( $titleInstance )
			);
			if ( strpos( $mode, 'chars' ) !== false || strpos( $mode, 'all' ) !== false ) {
				$out[$title]['chars'] = strlen( $content );
			}
			if ( strpos( $mode, 'words' ) !== false || strpos( $mode, 'all' ) !== false ) {
				$out[$title]['words'] = count( explode( ' ', trim( $content ) ) );
			}
			if ( strpos( $mode, 'pages' ) !== false || strpos( $mode, 'all' ) !== false ) {
				$out[$title]['pages'] = ceil( strlen( $content ) / 2000 );
			}
		}

		return $out;
	}

	public function handle() {
		$return = '';
		$countAllData = $this->magicWordBsCountCharacters();
		foreach ( $countAllData as $title => $countData ) {
			$services = Services::getInstance();
			$rendererFactory = $services->getBSRendererFactory();
			$rendererFactory instanceof RendererFactory;

			$content = [];
			foreach ( $countData as $key => $value ) {
				$content[] = [
					'label' => wfMessage( 'bs-countthings-' . $key . '-label' ),
					'count' => $value
				];
			}

			$lr = \MediaWiki\MediaWikiServices::getInstance()->getLinkRenderer();

			$params = new Params( [
				'title' => $title,
				'titlelink' => $lr->makeLink( \Title::newFromText( $title ) ),
				Renderer::PARAM_CONTENT => $content
				] );
			$return .= $rendererFactory->get( 'countthings-countcharacters', $params )->render();
		}
		return $return;
	}

}
