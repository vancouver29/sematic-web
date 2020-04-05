<?php

/**
 * GroupManager Extension for BlueSpice
 *
 * Administration interface for adding, editing and deleting usergroups.
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
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */


namespace BlueSpice\GroupManager;

class Extension extends \BlueSpice\Extension {

	/**
	 * extension.json callback
	 * @global array $bsgConfigFiles
	 */
	public static function onRegistration() {
		$GLOBALS[ 'bsgConfigFiles' ][ 'GroupManager' ] = \BSCONFIGDIR . '/gm-settings.php';
	}

	/**
	 * saves all groupspecific data to a config file
	 * @return array the json answer
	 */
	public static function saveData() {
		global $wgAdditionalGroups;
		$sSaveContent = "<?php\n\$GLOBALS['wgAdditionalGroups'] = [];\n\n";
		foreach ( $wgAdditionalGroups as $sGroup => $mValue ) {
			$nameErrors = self::getNameErrors( $sGroup );
			if( !empty( $nameErrors ) ) {
				return $nameErrors;
			} else {
				if ( $mValue !== false ) {
					$sSaveContent .= "\$GLOBALS['wgAdditionalGroups']['{$sGroup}'] = [];\n";
					self::checkI18N( $sGroup );
				} else {
					self::checkI18N( $sGroup, $mValue );
				}
			}
		}

		$sSaveContent .= "\n\$GLOBALS['wgGroupPermissions'] = array_merge(\$GLOBALS['wgGroupPermissions'], \$GLOBALS['wgAdditionalGroups']);";

		$res = file_put_contents($GLOBALS[ 'bsgConfigFiles' ][ 'GroupManager' ], $sSaveContent );
		if ( $res ) {
			return [
				'success' => true,
				'message' => \wfMessage( 'bs-groupmanager-grpadded' )->plain()
			];
		} else {
			return [
				'success' => false,
				// TODO SU (04.07.11 11:44): i18n
				'message' => 'Not able to create or write file "' . $GLOBALS[ 'bsgConfigFiles' ][ 'GroupManager' ] . '".'
			];
		}
	}

	public static function getNameErrors( $name ) {
		$aInvalidChars = [];
		$name = trim( $name );
		if ( substr_count( $name, '\'' ) > 0 ) {
			$aInvalidChars[] = '\'';
		}
		if ( substr_count( $name, '"' ) > 0 ) {
			$aInvalidChars[] = '"';
		}
		if ( !empty( $aInvalidChars ) ) {
			return [
				'success' => false,
				'message' => \wfMessage( 'bs-groupmanager-invalid-name' )
					->numParams( count( $aInvalidChars ) )
					->params( implode( ',', $aInvalidChars ) )
					->text()
			];
		} elseif ( preg_match( "/^[0-9]+$/", $name ) ) {
			return [
				'success' => false,
				'message' => \wfMessage( 'bs-groupmanager-invalid-name-numeric' )->plain()
			];
		} elseif ( strlen( $name ) > 255 ) {
			return [
				'success' => false,
				'message' => \wfMessage( 'bs-groupmanager-invalid-name-length' )->plain()
			];
		}
		return [];
	}

	public static function checkI18N( $sGroup, $bValue = true ) {
		$oTitle = \Title::newFromText( 'group-' . $sGroup, NS_MEDIAWIKI );
		$oArticle = null;

		if ( $bValue === false ) {
			if ( $oTitle->exists() ) {
				$oArticle = new \Article( $oTitle );
				$oArticle->doDeleteArticle( 'Group does no more exist' );
			}
		} else {
			if ( !$oTitle->exists() ) {
				$oArticle = new \Article( $oTitle );
				$oArticle->doEditContent( \ContentHandler::makeContent( $sGroup, $oTitle ), '', EDIT_NEW );
			}
		}
	}

}
