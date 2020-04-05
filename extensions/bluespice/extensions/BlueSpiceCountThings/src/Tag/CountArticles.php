<?php

namespace BlueSpice\CountThings\Tag;

class CountArticles extends \BlueSpice\Tag\Tag {

	public function needsDisabledParserCache() {
		return true;
	}

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {

		return new CountArticlesHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	public function getTagNames() {
		return [
			'bs:countarticles'
		];
	}

	public function getContainerElementName() {
		return 'span';
	}
}
