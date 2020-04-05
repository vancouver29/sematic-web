/**
 * SmartList extension
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage SmartList
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

Ext.define( 'BS.SmartList.MostVisitedPortlet', {
	extend: 'BS.portal.APIPortlet',
	portletConfigClass: 'BS.SmartList.MostVisitedPortletConfig',
	module: 'smartlist',
	task: 'getMostVisitedPages',
	makeData: function() {
		return {
			count: this.portletItemCount,
			period: this.portletTimeSpan
		};
	}
} );