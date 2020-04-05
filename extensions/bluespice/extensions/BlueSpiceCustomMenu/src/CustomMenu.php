<?php

namespace BlueSpice\CustomMenu;

use BlueSpice\Services;
use BlueSpice\Renderer\Params;
use BlueSpice\CustomMenu\Renderer\Menu;
use BlueSpice\Data\RecordSet;
use BlueSpice\Data\Record;

abstract class CustomMenu implements ICustomMenu {

	/**
	 * @var RecordSet
	 */
	protected $data = null;

	/**
	 * @var string
	 */
	protected $key = '';

	/**
	 * @var \Config
	 */
	protected $config = null;

	/**
	 * @param \Config $config
	 * @param string $key
	 */
	protected function __construct( \Config $config, $key ) {
		$this->config = $config;
		$this->key = $key;
	}

	/**
	 *
	 * @param \Config $config
	 * @param string $key
	 * @return CustomMenu
	 */
	public static function getInstance( \Config $config, $key ) {
		return new static( $config, $key );
	}

	/**
	 * @return Params
	 */
	protected function getParams() {
		return new Params( [
			Menu::PARAM_CUSTOM_MENU => $this
		] );
	}

	/**
	 * @return Menu
	 */
	public function getRenderer() {
		return Services::getInstance()->getBSRendererFactory()->get(
			'custommenu',
			$this->getParams()
		);
	}

	/**
	 * @return RecordSet
	 */
	public function getData() {
		$this->data = \BsCacheHelper::get( $this->getCacheKey() );
		if ( $this->data ) {
			return $this->data;
		}
		$this->data = new RecordSet( $this->getRecords() );
		\BsCacheHelper::set(
			$this->getCacheKey(),
			$this->data,
			60 * 1440 // max cache time 24h
		);
		return $this->data;
	}

	/**
	 * @param Record[] $records
	 * @return Record[]
	 */
	protected function getDefaultRecords( $records = [] ) {
		\Hooks::run( 'BSCustomMenuDefaultRecords', [
			$this->getKey()
			& $records
		] );
		return $records;
	}

	/**
	 * @return Record[]
	 */
	abstract protected function getRecords();

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return string
	 */
	protected function getCacheKey() {
		return \BsCacheHelper::getCacheKey(
			'BlueSpice',
			'CustomMenu',
			static::class
		);
	}

	public function invalidate() {
		\BsCacheHelper::invalidateCache( $this->getCacheKey() );
		$this->data = null;
	}

	/**
	 * @return int
	 */
	public function numberOfLevels() {
		return 1;
	}

	/**
	 * @return int
	 */
	public function numberOfMainEntries() {
		return static::NUM_ENTRIES_UNLIMITED;
	}

	/**
	 * @return int
	 */
	public function numberOfSubEntries() {
		return static::NUM_ENTRIES_UNLIMITED;
	}
}
