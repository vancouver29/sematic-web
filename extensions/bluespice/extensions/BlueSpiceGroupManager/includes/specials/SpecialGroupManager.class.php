<?php

/**
 * Special page for GroupManager of BlueSpice (MediaWiki)
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    BlueSpiceExtensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */
class SpecialGroupManager extends \BlueSpice\SpecialPage {

	public function __construct() {
		parent::__construct( 'GroupManager', 'groupmanager-viewspecialpage' );
	}

	public function execute( $par ) {
		parent::execute( $par );
		$oOutputPage = $this->getOutput();

		$this->getOutput()->addModules( 'ext.bluespice.groupManager' );
		$oOutputPage->addHTML( '<div id="bs-groupmanager-grid" class="bs-manager-container"></div>' );
	}

}
