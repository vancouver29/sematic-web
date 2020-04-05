<?php

namespace BlueSpice\CountThings\Tag;

class CountUsers extends \BlueSpice\Tag\Tag {

	public function needsDisabledParserCache() {
		return true;
	}

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		return new CountUsersHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	public function getTagNames() {
		return [
			'bs:countusers'
		];
	}

	public function getContainerElementName() {
		return 'span';
	}

}
