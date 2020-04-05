<?php

namespace BlueSpice\CountThings\Tag;

use BlueSpice\Tag\Tag;
use BlueSpice\ParamProcessor\ParamDefinition;
use BlueSpice\ParamProcessor\ParamType;
use BlueSpice\Tag\GenericHandler;

class CountFiles extends Tag {

	const ATTR_NODUPLICATES = 'noduplicates';

	public function getHandler( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		$loadBalancer = \MediaWiki\MediaWikiServices::getInstance()->getDBLoadBalancer();
		return new CountFilesHandler(
			$loadBalancer,
			$processedArgs[ static::ATTR_NODUPLICATES ]
		);
	}

	public function getTagNames() {
		return [ 'bs:countfiles', 'countfiles' ];
	}

	public function getContainerElementName() {
		return GenericHandler::TAG_SPAN;
	}

	public function disableParserCache() {
		return true;
	}

	public function getArgsDefinitions() {
		return [
			new ParamDefinition(
				ParamType::BOOLEAN,
				static::ATTR_NODUPLICATES,
				true
			)
		];
	}
}