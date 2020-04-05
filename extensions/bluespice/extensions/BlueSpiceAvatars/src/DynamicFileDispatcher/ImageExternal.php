<?php

namespace BlueSpice\Avatars\DynamicFileDispatcher;

class ImageExternal extends Image {

	/**
	 * Sets the headers for given \WebResponse
	 * @param \WebResponse $response
	 * @return void
	 */
	public function setHeaders( \WebResponse $response ) {
		$this->dfd->getContext()->getRequest()->response()->header(
			"Location:$this->src",
			true
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType() {
		return '';
	}
}
