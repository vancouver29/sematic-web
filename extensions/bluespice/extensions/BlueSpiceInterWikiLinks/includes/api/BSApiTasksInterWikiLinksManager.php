<?php
/**
 * Provides the Interwiki links manager tasks api for BlueSpice.
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 */

/**
 * InterWikiLinksManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksInterWikiLinksManager extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'editInterWikiLink' => [
			'examples' => [
				[
					'prefix' => 'mywiki',
					'url' => 'http://some.wiki.com/$1'
				],
				[
					'oldPrefix' => 'old_name',
					'prefix' => 'new_name',
					'url' => 'http://some.wiki.com/$1'
				]
			],
			'params' => [
				'oldPrefix' => [
					'desc' => 'Old prefix',
					'type' => 'string',
					'required' => false,
					'default' => ''
				],
				'url' => [
					'desc' => 'Url of the wiki',
					'type' => 'string',
					'required' => true
				],
				'prefix' => [
					'desc' => 'Prefix to set',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'removeInterWikiLink' => [
			'examples' => [
				[
					'prefix' => 'mywiki'
				]
			],
			'params' => [
				'prefix' => [
					'desc' => 'Prefix to remove',
					'type' => 'string',
					'required' => true
				]
			]
		]
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'editInterWikiLink' => [ 'wikiadmin' ],
			'removeInterWikiLink' => [ 'wikiadmin' ]
		];
	}

	/**
	 * Creates or edits an interwiki link.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_editInterWikiLink( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();
		$oPrefix = null;

		$sOldPrefix = isset( $oTaskData->oldPrefix )
			? (string) $oTaskData->oldPrefix
			: ''
		;
		$sUrl = isset( $oTaskData->url )
			? (string) $oTaskData->url
			: ''
		;
		$sPrefix = isset( $oTaskData->prefix )
			? (string) $oTaskData->prefix
			: ''
		;

		if( !empty( $sOldPrefix ) && !$this->interWikiLinkExists( $sOldPrefix ) ) {
			$oReturn->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nooldpfx' )->plain()
			];
		} elseif( !empty( $sPrefix ) && $this->interWikiLinkExists( $sPrefix ) && $sPrefix !== $sOldPrefix ) {
			$oReturn->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-pfxexists' )->plain()
			];
		}
		if( !empty( $oReturn->errors ) ) {
			return $oReturn;
		}

		if( !$oPrefix && empty( $sUrl ) ) {
			$oReturn->errors[] = [
				'id' => 'iwediturl',
				'message' => wfMessage( 'bs-interwikilinks-nourl' )->plain()
			];
		}
		if( !$oPrefix && empty( $sPrefix ) ) {
			$oReturn->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nopfx' )->plain()
			];
		}
		if( !empty( $sUrl ) ) {
			$oValidationResult = BsValidator::isValid(
				'Url',
				$sUrl,
				[ 'fullResponse' => true ]
				);
			if( $oValidationResult->getErrorCode() ) {
				$oReturn->errors[] = [
					'id' => 'iwediturl',
					'message' => $oValidationResult->getI18N()
				];
			}
			if( strpos( $sUrl, ' ' ) ) {
				$oReturn->errors[] = [
					'id' => 'iwediturl',
					'message' => wfMessage(
						'bs-interwikilinks-invalid-url-spc'
					)->plain()
				];
			}
		}
		if( !empty( $sPrefix ) ) {
			if ( strlen( $sPrefix ) > 32 ) {
				$oReturn->errors[] = [
					'id' => 'iweditprefix',
					'message' => wfMessage(
						'bs-interwikilinks-pfxtoolong'
					)->plain()
				];
			}

			foreach( [ ' ', '"', '&', ':' ] as $sInvalidChar ) {
				if( substr_count( $sPrefix, $sInvalidChar ) === 0 ) {
					continue;
				}
				//TODO (PW 19.02.2016): Return the invalid char(s)
				$oReturn->errors[] = [
					'id' => 'iweditprefix',
					'message' => wfMessage(
						'bs-interwikilinks-invalid-pfx-spc'
					)->plain()
				];
				break;
			}
		}

		if( !empty( $oReturn->errors ) ) {
			return $oReturn;
		}

		$oDB = $this->getDB();
		$sTable = 'interwiki';
		$aConditions = [ 'iw_local' => '0' ];
		$aValues = [
			'iw_prefix' => $sPrefix,
			'iw_url' => $sUrl,
			'iw_api' => '',
			'iw_wikiid' => ''
		];

		if( empty( $sOldPrefix ) ) {
			$oReturn->success = $oDB->insert(
				$sTable,
				array_merge( $aConditions, $aValues ),
				__METHOD__
			);
			$oReturn->message = wfMessage(
				'bs-interwikilinks-link-created'
			)->plain();

			InterWikiLinks::purgeTitles( $sPrefix );
			\BlueSpice\Services::getInstance()->getInterwikiLookup()->invalidateCache( $sPrefix );
			return $oReturn;
		}

		$aConditions['iw_prefix'] = $sOldPrefix;
		$oReturn->success = $oDB->update(
			$sTable,
			$aValues,
			$aConditions,
			__METHOD__
		);
		$oReturn->message = wfMessage(
			'bs-interwikilinks-link-edited'
		)->plain();

		InterWikiLinks::purgeTitles( $sOldPrefix );
		\BlueSpice\Services::getInstance()->getInterwikiLookup()->invalidateCache( $sOldPrefix );

		return $oReturn;
	}

	/**
	 * Creates or edits an interwiki link.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_removeInterWikiLink( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$sPrefix = isset( $oTaskData->prefix )
			? addslashes( $oTaskData->prefix )
			: ''
		;

		if( empty( $sPrefix ) ) {
			$oReturn->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nopfx' )->plain()
			];
			return $oReturn;
		}

		if( !$this->interWikiLinkExists( $sPrefix ) ) {
			$oReturn->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nooldpfx' )->plain()
			];
			return $oReturn;
		}

		$oReturn->success = (bool) $this->getDB()->delete(
			'interwiki',
			[ 'iw_prefix' => $sPrefix ],
			__METHOD__
		);

		if( $oReturn->success ) {
			$oReturn->message = wfMessage(
				'bs-interwikilinks-link-deleted'
			)->plain();
		}

		//Make sure to invalidate as much as possible!
		InterWikiLinks::purgeTitles( $sPrefix );
		\BlueSpice\Services::getInstance()->getInterwikiLookup()->invalidateCache( $sPrefix );
		return $oReturn;
	}

	protected function interWikiLinkExists( $sPrefix ) {
		return \BlueSpice\Services::getInstance()->getInterwikiLookup()->isValidInterwiki( $sPrefix );
	}
}