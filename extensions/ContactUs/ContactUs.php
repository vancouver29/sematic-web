<?php
# Alert the user that this is not a valid access point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<HTML
To install my extension, put the following line in LocalSettings.php:<br/>
require_once( "\$IP/extensions/ContactUs/ContactUs.php" );
HTML;
	exit( 1 );
}
 
$wgExtensionCredits[ 'specialpage' ][] = array(
	'path' => __FILE__,
	'name' => 'ContactUs',
	'author' => 'Justin Folvarcik',
	'url' => 'http://zeldawiki.org/User:Justin',
	'descriptionmsg' => 'contactus-desc',
	'version' => '0.1',
);
 
$wgAutoloadClasses[ 'SpecialContactUs' ] = __DIR__ . '/SpecialContactUs.php'; # Location of the SpecialContactUs class (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles[ 'ContactUs' ] = __DIR__ . '/ContactUs.i18n.php'; # Location of a messages file (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles[ 'ContactUsAlias' ] = __DIR__ . '/ContactUs.alias.php'; # Location of an aliases file (Tell MediaWiki to load this file)
$wgSpecialPages[ 'ContactUs' ] = 'SpecialContactUs'; # Tell MediaWiki about the new special page and its class name