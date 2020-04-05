<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'BlueSpiceEditNotifyConnector' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['BlueSpiceEditNotifyConnector'] = __DIR__ . '/i18n';
	wfWarn(
	    'Deprecated PHP entry point used for BlueSpiceEditNotifyConnector extension. Please use wfLoadExtension instead'
	);
	return true;
} else {
	die( 'This version of the BlueSpiceEditNotifyConnector extension requires MediaWiki 1.25+' );
}
