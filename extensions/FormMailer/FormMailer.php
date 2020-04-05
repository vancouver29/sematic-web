<?php
/**
 * FormMailer extension - Formats and sends posted form fields to email recipients
 *
 * See http://www.mediawiki.org/wiki/Extension:FormMailer for installation and usage details
 * Started: 2007-06-17
 *
 * @package MediaWiki
 * @subpackage Extensions
 * @author [http://www.organicdesign.co.nz/aran Aran Dunkley]
 * @copyright © 2007 Aran Dunkley
 * @licence GNU General Public Licence 2.0 or later
 */
if( !defined( 'MEDIAWIKI' ) ) die( 'Not an entry point.' );
define( 'FORMMAILER_VERSION', '1.0.11, 2018-04-20' );

// A list of email addresses which should recieve posted forms
$wgFormMailerRecipients = array();

// If a variable of this name is posted, the data is assumed to be for mailing
$wgFormMailerVarName = "formmailer";

// Whether to send the inquirer a confirmation email
$wgFormMailerSendConfirmation = false;

// Name of sender of forms
$wgFormMailerFrom = 'wiki@' . preg_replace( '|^.+www\.|', '', $wgServer );

// Don't post the following posted items
$wgFormMailerDontSend = array( 'title', 'action', 'debug', 'uselang', 'useskin' );

// Add a JavaScript test to protect against spambot posts
$wgFormMailerAntiSpam = true;

$wgExtensionFunctions[] = 'wfSetupFormMailer';
$wgExtensionMessagesFiles['FormMailer'] = __DIR__ . "/FormMailer.i18n.php";
$wgExtensionCredits['other'][] = array(
	'name'        => 'FormMailer',
	'author'      => '[http://www.organicdesign.co.nz/aran Aran Dunkley]',
	'description' => 'Formats and sends posted form fields to email recipients',
	'url'         => 'http://www.mediawiki.org/wiki/Extension:FormMailer',
	'version'     => FORMMAILER_VERSION
);

function wfSetupFormMailer() {
	global $wgRequest, $wgSiteNotice, $wgSitename, $wgOut, $wgResourceModules,
		$wgFormMailerVarName, $wgFormMailerRecipients, $wgFormMailerFrom, $wgFormMailerDontSend, $wgFormMailerAntiSpam, $wgFormMailerSendConfirmation;

	// Get the name of our form submit parameter
	$ip = $_SERVER['REMOTE_ADDR'];
	$md5 = md5( $ip );
	$submit = $wgFormMailerAntiSpam ? "$wgFormMailerVarName-$md5" : $wgFormMailerVarName;

	// If our form has been submitted...
	if( $wgRequest->getText( $submit ) ) {

		// Construct the message from the form data
		$from_email = '';
		$body = '';
		$subject = wfMessage( 'formmailer-subject', $wgSitename )->text();
		foreach( $wgRequest->getValues() as $k => $v ) {
			if( !in_array( $k, $wgFormMailerDontSend ) ) {
				$k = str_replace( '_', ' ', $k );
				if ( strtolower( $k ) == 'subject' ) $subject = $v;
				if( preg_match( "|^email|i", $k ) ) $from_email = $v;
				if ( $k != $submit ) $body .= "$k: $v\n\n";
			}
		}

		// Only continue if the email is valid
		if( Sanitizer::validateEmail( $from_email ) ) {

			// Send to recipients using the MediaWiki mailer
			$err  = '';
			foreach( $wgFormMailerRecipients as $recipient ) {
				if( Sanitizer::validateEmail( $recipient ) ) {
					$from = new MailAddress( $from_email );
					$to = new MailAddress( $recipient );
					$status = UserMailer::send( $to, $from, $subject, wfMessage( 'formmailer-posted', $ip )->text() . "\n\n$body" );
					if( !is_object( $status ) || !$status->ok ) $err = wfMessage( 'formmailer-failed' )->text();
				}
			}

			// Send a confirmation to the sender
			$message = wfMessage( 'formmailer-message' )->text();
			if( $wgFormMailerSendConfirmation ) {
				$message .= ' ' . wfMessage( 'formmailer-confirmsent' )->text();
				$from = new MailAddress( "\"$wgSitename\"<$wgFormMailerFrom>" );
				$to = new MailAddress( $from_email );
				$body = wfMessage( 'formmailer-confirmmessage' )->text() . "\n\n$body";
				$status = UserMailer::send( $to, $from, wfMessage( 'formmailer-confirmsubject', $wgSitename ), $body );
				if( !is_object( $status ) || !$status->ok ) $err = wfMessage( 'formmailer-failed' )->text();
			}

			// Show the thankyou message
			if( $err ) $wgSiteNotice .= "<div class='errorbox'>$err</div><div style='clear:both'></div>";
			else $wgSiteNotice .= "<div class='usermessage'>$message</div>";
		}
		
		// The inquirer's email wasn't valid
		else $wgSiteNotice .= "<div class='errorbox'>" . wfMessage( 'formmailer-invalidemail', $from_email )->text() . "</div><div style='clear:both'></div>";
	}
	
	// Add the antispam script
	// - adds the MD5 of the IP address to the formmailer input name after page load
	// - really easy to fool, but will block all the common bots
	if( $wgFormMailerAntiSpam ) {
		$wgResourceModules['ext.formmailer'] = array(
			'scripts'       => array( 'formmailer.js' ),
			'localBasePath' => __DIR__,
			'remoteExtPath' => basename( __DIR__ ),
		);
		$wgOut->addModules( 'ext.formmailer' );
		$wgOut->addJsConfigVars( 'wgFormMailerAP', $md5 );
		$wgOut->addJsConfigVars( 'wgFormMailerVarName', $wgFormMailerVarName );
	}
}

