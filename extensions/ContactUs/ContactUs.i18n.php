<?php
if (!defined('MEDIAWIKI'))
    die("Not a valid access point");
/**
 * Internationalisation for ContactUs
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();
 
/** English
 * @author Justin Folvarcik
 */
$messages[ 'en' ] = array(
	'contactus' => "Contact Us", // Important! This is the string that appears on Special:SpecialPages
	'contactus-desc' => "Creates a special page to allow users to contact specific staff based on the nature of their inquiry.",
    'contactus-head' => "Email the staff",
    'contactus-legend' => 'Email Form',
    'contactus-page-desc' => '<p>This page allows users and readers to contact the staff of {{SITENAME}}. Please try to be specific with you inquiry, '
   .'so that it can be sent to the proper staff members.</p>',
    'contactus-text' => 'This is the email form. Please be sure that you fill out all information properly.',
    'contactus-your-email' => 'Your Email Address',
    'contactus-your-username' => 'Your {{SITENAME}} Username',
    'contactus-problem-question' => 'What would you like to ask about?',
    'contactus-subject' => 'Subject:',
    'contactus-message' => 'Message body:',
    'contactus-settings-msg' => 'This page allows you to view the current settings of the extension.',
    'contactus-bad-settings' => 'Misconfiguration',
    'contactus-bad-recipients' => 'Your recipient users are configured improperly. Please check your LocalSettings.php and correct the problem.',
    'contactus-bad-groups' => 'Your recipient groups are improperly configured. Please check your LocalSettings.php and correct the problem.',
    'contactus-settings-error-public' => 'This extension has not been configured properly. If you are attempting to contact the administration, you '.
    'will be unable to use this form until the problem has been rectified.',
    'contactus-settings-error-sysop' => "The extension has not been properly configured. The following error was encountered:\n\n" . '$1',
    'contactus-settings-settings' => 'Settings',
    'contactus-table-users' => 'Users set to receive emails',
    'contactus-table-groups' => 'Types of emails user will receive',
    'contactus-table-custom' => 'Custom message',
    'contactus-table-variable' => 'Setting',
    'contactus-table-value' => 'Value',
    'contactus-table-page' => 'Page to change setting',
    'contactus-table-other' => 'Other',
    'right-contactus-admin' => 'Allows user to view the settings of the ContactUs extension and make changes.',
    'contactus-no-recipients' => 'Error: No recipients could be found for the email. The extension may be configured incorrectly.',
    'contactus-emailfail' => 'Email failed',
    'contactus-emailfail-message' => 'Sending message failed. ',
    'contactus-success' => 'Email sent',
    'contactus-email-sent' => 'Thank you. Your message has been sent to the appropriate staff. You will receive a reply as soon as possible.'


);