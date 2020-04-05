/**
 * Adds a required hash to the formmailer input name to avoid spam if $wgFormMailerAntiSpam is set
 */
$(document).ready(function() {
	$('input[name=' + mw.config.get('wgFormMailerVarName') + ']').each(function() {
		$(this).attr('name', $(this).attr('name') + '-' + mw.config.get('wgFormMailerAP') );
	});
});
