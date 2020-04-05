<?php

namespace BlueSpice\EchoConnector\Notifier;

use BlueSpice\EchoConnector\Formatter\EchoPlainTextEmailFormatter as BsPlainTextEmailFormatter;
use BlueSpice\EchoConnector\Formatter\EchoHTMLEmailFormatter as BsHtmlEmailFormatter;

/**
 * Override of default EchoNotifier - copy-paste from there
 * All this because Echo uses hard-coded formatters for mails
 */
class EchoNotifier extends \EchoNotifier {
	public static function notifyWithEmail( $user, $event ) {
		global $wgEnableEmail;

		if ( !$wgEnableEmail ) {
			return false;
		}
		// No valid email address or email notification
		if ( !$user->isEmailConfirmed() || $user->getOption( 'echo-email-frequency' ) < 0 ) {
			return false;
		}

		// Final check on whether to send email for this user & event
		if ( !\Hooks::run( 'EchoAbortEmailNotification', [ $user, $event ] ) ) {
			return false;
		}

		$attributeManager = \EchoAttributeManager::newFromGlobalVars();
		$userEmailNotifications = $attributeManager->getUserEnabledEvents( $user, 'email' );
		// See if the user wants to receive emails for this category or the user is eligible to receive this email
		if ( in_array( $event->getType(), $userEmailNotifications ) ) {
			global $wgEchoEnableEmailBatch, $wgEchoNotifications, $wgNotificationSender, $wgNotificationReplyName;

			$priority = $attributeManager->getNotificationPriority( $event->getType() );

			$bundleString = $bundleHash = '';

			// We should have bundling for email digest as long as either web or email bundling is on, for example, talk page
			// email bundling is off, but if a user decides to receive email digest, we should bundle those messages
			if ( !empty( $wgEchoNotifications[$event->getType()]['bundle']['web'] ) || !empty( $wgEchoNotifications[$event->getType()]['bundle']['email'] ) ) {
				\Hooks::run( 'EchoGetBundleRules', [ $event, &$bundleString ] );
			}
			if ( $bundleString ) {
				$bundleHash = md5( $bundleString );
			}

			\MWEchoEventLogging::logSchemaEcho( $user, $event, 'email' );

			$extra = $event->getExtra();
			$sendImmediately = isset( $extra['immediate-email'] ) && $extra['immediate-email'] == true;

			// email digest notification ( weekly or daily )
			if ( $wgEchoEnableEmailBatch && $user->getOption( 'echo-email-frequency' ) > 0 && !$sendImmediately ) {
				// always create a unique event hash for those events don't support bundling
				// this is mainly for group by
				if ( !$bundleHash ) {
					$bundleHash = md5( $event->getType() . '-' . $event->getId() );
				}
				\MWEchoEmailBatch::addToQueue( $user->getId(), $event->getId(), $priority, $bundleHash );

				return true;
			}

			// instant email notification
			$toAddress = \MailAddress::newFromUser( $user );
			$fromAddress = new \MailAddress( $wgNotificationSender, \EchoHooks::getNotificationSenderName() );
			$replyAddress = new \MailAddress( $wgNotificationSender, $wgNotificationReplyName );
			// Since we are sending a single email, should set the bundle hash to null
			// if it is set with a value from somewhere else
			$event->setBundleHash( null );
			$email = self::generateEmail( $event, $user );
			if ( !$email ) {
				return false;
			}

			$subject = $email['subject'];
			$body = $email['body'];
			$options = [ 'replyTo' => $replyAddress ];

			\UserMailer::send( $toAddress, $fromAddress, $subject, $body, $options );
			\MWEchoEventLogging::logSchemaEchoMail( $user, 'single' );
		}

		return true;
	}
	/**
	 * @param EchoEvent $event
	 * @param User $user
	 * @return bool|array An array of 'subject' and 'body', or false if things went wrong
	 */
	private static function generateEmail( \EchoEvent $event, \User $user ) {
		$emailFormat = \MWEchoNotifUser::newFromUser( $user )->getEmailFormat();
		$lang = wfGetLangObj( $user->getOption( 'language' ) );
		$formatter = new BsPlainTextEmailFormatter( $user, $lang );
		$content = $formatter->format( $event );
		if ( !$content ) {
			return false;
		}

		if ( $emailFormat === \EchoEmailFormat::HTML ) {
			$htmlEmailFormatter = new BsHtmlEmailFormatter( $user, $lang );
			$htmlContent = $htmlEmailFormatter->format( $event );
			$multipartBody = [
				'text' => $content['body'],
				'html' => $htmlContent['body']
			];
			$content['body'] = $multipartBody;
		}

		return $content;
	}
}
