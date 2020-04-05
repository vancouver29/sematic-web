<?php

namespace BlueSpice\EchoConnector;

interface IParamParser {

	public function __construct( \EchoEvent $event, $distributionType );
	/**
	 * Receives param name and determines value
	 * for given param based on Event data and
	 * sets the value to \Message object
	 */
	public function parseParam( \Message $message, $param );
}
