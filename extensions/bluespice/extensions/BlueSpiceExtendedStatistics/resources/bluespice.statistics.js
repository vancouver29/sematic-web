/**
 * Statistics extension
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */
Ext.onReady(function() {
	Ext.Loader.setPath( 'BS.Statistics', bs.em.paths.get('BlueSpiceExtendedStatistics') + '/resources/BS.Statistics');
	Ext.create('BS.Statistics.panel.Main', {
		renderTo: 'bs-statistics-panel'
	});
});

