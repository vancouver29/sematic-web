/**
 * Js for WhoIsOnline extension
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

BsWhoIsOnline = {
	interval: 0,
	limit: 0,
	init: function() {
		BsWhoIsOnline.interval = mw.config.get(
			'bsgWhoIsOnlineInterval'
		) * 1000;
		BsWhoIsOnline.limit = mw.user.options.get(
			'bs-whoisonline-pref-limitcount'
		);
		if(BsWhoIsOnline.interval < 1) return;

		BSPing.registerListener('WhoIsOnline', BsWhoIsOnline.interval, [], BsWhoIsOnline.pingListener);
	},
	pingListener: function( result, Listener) {
		if(result.success !== true) return;

		$('.bs-whoisonline-portlet').each(function(){
			var aCurrentPortlet = result['portletItems'];
			if( $(this).hasClass( 'bs-widget-body' ) == false ) {
				$(this).html( '<ul>' + aCurrentPortlet.join("\n") + '</ul>' );
				return;
			}

			$(this).html('<ul>' + aCurrentPortlet.slice(0, BsWhoIsOnline.limit).join("\n") + '</ul>');
		});

		$('.bs-whoisonline-count').each(function(){
			$(this).html(result['count']);
		});

		BSPing.registerListener('WhoIsOnline', BsWhoIsOnline.interval, [], BsWhoIsOnline.pingListener);
	}
};

mw.loader.using( 'ext.bluespice', function() {
	BsWhoIsOnline.init();
});