<?php

namespace BlueSpice\PageAssignments\ResourceLoader;
use BlueSpice\Services;

class Messages extends \ResourceLoaderModule {
	/**
	 * Get the messages needed for this module.
	 *
	 * To get a JSON blob with messages, use MessageBlobStore::get()
	 *
	 * @return array List of message keys. Keys may occur more than once
	 */
	public function getMessages() {
		$messages = parent::getMessages();
		$factory = Services::getInstance()->getService(
			'BSPageAssignmentsAssignableFactory'
		);
		foreach( $factory->getRegisteredTypes() as $type ) {
			if( !$assignable = $factory->factory( $type ) ) {
				continue;
			}
			$messages[] = $assignable->getTypeMessageKey();
		}
		array_unique( $messages );
		return array_values( $messages );
	}

	/**
	 * Get target(s) for the module, eg ['desktop'] or ['desktop', 'mobile']
	 *
	 * @return array Array of strings
	 */
	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}

}
