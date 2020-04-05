<?php

namespace BlueSpice\Dashboards\Hook;

use BlueSpice\Hook;

abstract class BSDashboardsUserDashboardPortalConfig extends Hook {

	/**
	 *
	 * @var boolean
	 */
	protected $isDefault;

	/**
	 *
	 * @var array
	 */
	protected $portalConfig;

	/**
	 *
	 * @var \BlueSpice\SpecialPage $caller
	 */
	protected $caller;

	/**
	 * Fired in SpecialUserDashboard::execute
	 *
	 * @param \BlueSpice\SpecialPage $caller
	 * @param array &$portalConfig reference to array portlet configs
	 * @param boolean $isDefault
	 * @return boolean
	 */
	public static function callback( $caller, &$portalConfig, $isDefault ) {
		$className = static::class;
		$hookHandler = new $className(
			null, null, $caller, $portalConfig, $isDefault
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param \IContextSource $context
	 * @param \Config $config
	 * @param \BlueSpice\SpecialPage $caller
	 * @param array &$portalConfig reference to array portlet configs
	 * @param boolean $isDefault
	 */
	public function __construct( $context, $config, $caller, &$portalConfig, $isDefault ) {
		parent::__construct( $context, $config );
		$this->caller = $caller;
		$this->portalConfig = &$portalConfig;
		$this->isDefault = $isDefault;
	}

}
