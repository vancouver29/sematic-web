<?php

namespace BlueSpice\EchoConnector\Hook;

abstract class BeforeEchoEventInsert extends \BlueSpice\Hook {
	/**
	 *
	 * @var \EchoEvent
	 */
	protected $event;

	public static function callback( $event ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$event
		);
		return $hookHandler->process();
	}

	public function __construct( $context, $config, $event ) {
		parent::__construct( $context, $config );

		$this->event = $event;
	}
}
