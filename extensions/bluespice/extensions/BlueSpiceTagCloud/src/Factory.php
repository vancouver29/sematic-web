<?php

namespace BlueSpice\TagCloud;

class Factory {

	/**
	 *
	 * @var \BlueSpice\ExtensionAttributeBasedRegistry
	 */
	protected $storeRegistry = null;

	/**
	 *
	 * @var \BlueSpice\ExtensionAttributeBasedRegistry
	 */
	protected $rendererRegistry = null;

	/**
	 *
	 * @var \BlueSpice\RendererFactory
	 */
	protected $rendererFactory = null;

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 * @param \BlueSpice\ExtensionAttributeBasedRegistry $storeRegistry
	 * @param \BlueSpice\ExtensionAttributeBasedRegistry $rendererRegistry
	 * @param \BlueSpice\RendererFactory $rendererFactory
	 * @param \Config $config
	 */
	public function __construct( $storeRegistry, $rendererRegistry, $rendererFactory, $config ) {
		$this->storeRegistry = $storeRegistry;
		$this->rendererRegistry = $rendererRegistry;
		$this->rendererFactory = $rendererFactory;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $type
	 * @param Context $context
	 * @return \BlueSpice\TagCloud\Data\TagCloud\IStore
	 * @throws \MWException
	 */
	public function getStore( $type, Context $context ) {
		//backwards compatibillity
		if( $type === 'categories' ) {
			$type = 'category';
		}
		if( !$store = $this->storeRegistry->getValue( $type, false ) ) {
			$msg = \Message::newFromKey( 'bs-tagcloud-error-tagtype' );
			throw new \MWException( $msg->params( $type )->text() );
		}

		return new $store( $context );
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultStoreType() {
		return 'category';
	}

	/**
	 *
	 * @param string $type
	 * @param array $data
	 * @return \BlueSpice\Renderer
	 * @throws \MWException
	 */
	public function getRenderer( $type, array $data = [] ) {
		if( !$renderer = $this->rendererRegistry->getValue( $type, false ) ) {
			$msg = \Message::newFromKey( 'bs-tagcloud-error-tagrenderer' );
			throw new \MWException( $msg->params( $type )->text() );
		}
		return $this->rendererFactory->get(
			$renderer,
			new \BlueSpice\Renderer\Params( $data )
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getDefaultRendererType() {
		return 'text';
	}
}
