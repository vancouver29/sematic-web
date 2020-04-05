<?php
/**
 * InterWiki Links extension for BlueSpice MediaWiki
 *
 * Administration interface for adding, editing and deleting interwiki links
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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @package    BlueSpice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Main class for InterWikiLinks extension
 * @package BlueSpice_Extensions
 * @subpackage InterWikiLinks
 */
class InterWikiLinks extends BsExtensionMW {

	protected function initExt() {}

	public static function purgeTitles($iw_prefix) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'iwlinks',
			array('iwl_from', 'iwl_prefix'),
			array('iwl_prefix' => $iw_prefix)
		);

		foreach( $res as $row ) {
			$oTitle = Title::newFromID( $row->iwl_from );
			if( $oTitle instanceof Title == false ) continue;
			$oTitle->invalidateCache();
		}
	}

}
