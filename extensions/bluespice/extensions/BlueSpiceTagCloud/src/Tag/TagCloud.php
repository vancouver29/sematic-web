<?php

namespace BlueSpice\TagCloud\Tag;

use BlueSpice\Tag\MarkerType\NoWiki;

class TagCloud extends \BlueSpice\Tag\Tag {

	public function needsDisabledParserCache() {
		return true;
	}

	public function getContainerElementName() {
		return 'div';
	}

	public function needsParsedInput() {
		return false;
	}

	public function needsParseArgs() {
		return true;
	}

	public function getMarkerType() {
		return new NoWiki();
	}

	public function getInputDefinition() {
		return null;
	}

	public function getArgsDefinitions() {
		return [];
	}

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		return new TagCloudHandler(
			$processedInput,
			$processedArgs,
			$parser,
			$frame
		);
	}

	public function getTagNames() {
		return [
			'bs:tagcloud',
		];
	}

}
