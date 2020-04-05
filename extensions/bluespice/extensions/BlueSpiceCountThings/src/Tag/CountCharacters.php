<?php

namespace BlueSpice\CountThings\Tag;

class CountCharacters extends \BlueSpice\Tag\Tag {

	public function needsDisabledParserCache() {
		return true;
	}

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		return new CountCharactersHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	public function getTagNames() {
		return [
			'bs:countcharacters'
		];
	}
}
