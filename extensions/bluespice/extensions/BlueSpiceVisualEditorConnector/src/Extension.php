<?php
/**
 * VisualEditorConnector Extension for BlueSpice
 *
 * Provides a visual editor widget for various form fields.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser
 * @package    BlueSpice_Extensions
 * @subpackage VisualEditorConnector
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */
namespace BlueSpice\VisualEditorConnector;

use MediaWiki\MediaWikiServices;

class Extension extends \BlueSpice\Extension {

	/**
	 *
	 * @return void
	 */
	public static function onRegistration() {
		// Setup our Restbase Mock API to allow switching from WikiText editor to VE
		$config = MediaWikiServices::getInstance()->getMainConfig();
		if( $config->get( 'VisualEditorFullRestbaseURL' ) !== false ) {
			return;
		}

		$server = $config->get( 'Server' );
		$scriptPath = $config->get( 'ScriptPath' );
		$actionApiBase = '/api.php?action=bs-vec-restbase-mock&path=';

		$GLOBALS['wgVisualEditorFullRestbaseURL'] = $server . $scriptPath . $actionApiBase;
	}
}
