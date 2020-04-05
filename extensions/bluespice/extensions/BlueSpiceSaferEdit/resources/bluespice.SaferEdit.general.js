/**
 * SaferEdit extension
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.com>

 * @package    Bluespice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for all general safer edit related methods and properties
 */
BsSaferEdit = {
	/**
	 * Time between two intermediate saves
	 * @var integer time in seconds
	 */
	interval: 0,

	/**
	 * Conducts neccessary preparations of edit form and starts intermediate saving
	 */
	init: function() {
		this.interval = mw.config.get( 'bsgSaferEditInterval' ) * 1000;

		if ( this.interval < 1000 ) {
			return;
		}
		if( mw.config.get( 'wgNamespaceNumber', 0 ) < 0 ) {
			return;
		}

		BSPing.registerListener(
			'SaferEditIsSomeoneEditing',
			BsSaferEdit.interval,
			[],
			BsSaferEdit.someoneIsEditingListener
		);

		BSPing.registerListener(
			'SaferEditSave',
			BsSaferEdit.interval,
			[],
			BsSaferEdit.saferEditSave
		);
	},

	saferEditSave: function(result, Listener) {
		var data = [];

		if( BsSaferEdit._pageIsInEditMode() ) {
			data = [{
				bUnsavedChanges: true
			}];
		}

		BSPing.registerListener(
			'SaferEditSave',
			0,
			data,
			BsSaferEdit.saferEditSave
		);
	},

	_pageIsInEditMode: function() {
		var editModeQueries = [
			'action=edit',
			'action=formedit',
			'veaction=edit', 'veaction=editsource'
		];

		var queryString = window.location.search;

		for( var i = 0; i < editModeQueries.length; i++ ) {
			var editModeQuery = editModeQueries[i];
			if( queryString.indexOf( editModeQuery ) !== -1 ) {
				return true;
			}
		}

		return false;
	},

	someoneIsEditingListener: function(result, Listener) {
		if(result.success !== true) return;

		BsSaferEdit._updateLegacyUI( result );
		BsSaferEdit._updateUI( result );

		BSPing.registerListener( 'SaferEditIsSomeoneEditing', BsSaferEdit.interval, [], BsSaferEdit.someoneIsEditingListener );
	},

	_updateLegacyUI: function( result ) {
		if( $('#sb-SaferEditSomeoneEditing').length > 0 ) {
			$('#sb-SaferEditSomeoneEditing').replaceWith(result.someoneEditingView);
		} else {
			$('#bs-statebar').find('#bs-statebar-view').before(result.someoneEditingView);
		}

		if( $('#sb-SaferEdit').length > 0 ) {
			$('#sb-SaferEdit').replaceWith(result.safereditView);
		} else {
			$('#bs-statebar').find('#bs-statebar-view').before(result.safereditView);
		}
	},

	lastSomeoneEditingView: '',

	_updateUI: function( result ) {
		if( result.someoneEditingView === '' ) {
			bs.alerts.remove( 'bs-saferedit-warning' );
			return;
		}

		var $elem = $('<div>').append( result.someoneEditingView );
		BsSaferEdit.lastSomeoneEditingView = result.someoneEditingView;

		bs.alerts.add(
			'bs-saferedit-warning',
			$elem,
			bs.alerts.TYPE_WARNING
		);
	}
};

mw.loader.using( 'ext.bluespice', function() {
	BsSaferEdit.init();
});