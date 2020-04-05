<?php

namespace BlueSpice\CountThings\Tag;

use BlueSpice\Tag\Handler;

class CountUsersHandler extends Handler {

	public function __construct( $processedInput, array $processedArgs, \Parser $parser, \PPFrame $frame ) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
	}

	public function handle() {
		$count = \CoreParserFunctions::numberofusers( $this->parser );
		return " $count ";
	}
}