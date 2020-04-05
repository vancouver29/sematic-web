<?php
/**
 * Speclial:Contact, a contact form for visitors.
 * Based on SpecialEmailUser.php
 *
 * @file
 * @ingroup SpecialPage
 * @author Daniel Kinzler, brightbyte.de
 * @copyright © 2007-2014 Daniel Kinzler, Sam Reed
 * @license GPL-2.0-or-later
 */

/**
 * Provides the contact form
 * @ingroup SpecialPage
 */
class SpecialContact extends UnlistedSpecialPage {

	/**
	 * Set default value after registration
	 */
	public static function onRegistration() {
		global $wgContactConfig, $wgSitename;
		if ( $wgContactConfig['default']['SenderName'] === null ) {
			$wgContactConfig['default']['SenderName'] = "Contact Form on $wgSitename";
		}
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'Contact' );
	}

	/**
	 * @inheritDoc
	 */
	function getDescription() {
		return $this->msg( 'contactpage' )->text();
	}

	/**
	 * @var string
	 */
	protected $formType;

	/**
	 * @return array
	 */
	protected function getTypeConfig() {
		global $wgContactConfig;
		if ( isset( $wgContactConfig[$this->formType] ) ) {
			return $wgContactConfig[$this->formType] + $wgContactConfig['default'];
		}
		return $wgContactConfig['default'];
	}

	/**
	 * Main execution function
	 *
	 * @param string|null $par Parameters passed to the page
	 * @throws UserBlockedError
	 * @throws ErrorPageError
	 */
	public function execute( $par ) {
		global $wgEnableEmail;

		if ( !$wgEnableEmail ) {
			// From Special:EmailUser
			throw new ErrorPageError( 'usermaildisabled', 'usermaildisabledtext' );
		}

		$request = $this->getRequest();
		$this->formType = strtolower( $request->getText( 'formtype', $par ) );

		$config = $this->getTypeConfig();

		if ( $config['MustBeLoggedIn'] ) {
			$this->requireLogin( 'contactpage-mustbeloggedin' );
		}

		if ( !$config['RecipientUser'] ) {
			$this->getOutput()->showErrorPage( 'contactpage-config-error-title',
				'contactpage-config-error' );
			return;
		}

		$user = $this->getUser();

		$nu = User::newFromName( $config['RecipientUser'] );
		if ( is_null( $nu ) || !$nu->canReceiveEmail() ) {
			$this->getOutput()->showErrorPage( 'noemailtitle', 'noemailtext' );
			return;
		}

		// Blocked users cannot use the contact form if they're disabled from sending email.
		if ( $user->isBlockedFromEmailuser() ) {
			throw new UserBlockedError( $this->getUser()->mBlock );
		}

		$pageTitle = '';
		if ( $this->formType != '' ) {
			$message = $this->msg( 'contactpage-title-' . $this->formType );
			if ( !$message->isDisabled() ) {
				$pageTitle = $message;
			}
		}

		if ( $pageTitle === '' ) {
			$pageTitle = $this->msg( 'contactpage-title' );
		}
		$this->getOutput()->setPageTitle( $pageTitle );

		$subject = '';

		# Check for type in [[Special:Contact/type]]: change pagetext and prefill form fields
		if ( $this->formType != '' ) {
			$message = $this->msg( 'contactpage-pagetext-' . $this->formType );
			if ( !$message->isDisabled() ) {
				$formText = $message->parseAsBlock();
			} else {
				$formText = $this->msg( 'contactpage-pagetext' )->parseAsBlock();
			}

			$message = $this->msg( 'contactpage-subject-' . $this->formType );
			if ( !$message->isDisabled() ) {
				$subject = $message->inContentLanguage()->plain();
			}
		} else {
			$formText = $this->msg( 'contactpage-pagetext' )->parseAsBlock();
		}

		$subject = trim( $subject );

		if ( $subject === '' ) {
			$subject = $this->msg( 'contactpage-defsubject' )->inContentLanguage()->text();
		}

		$fromAddress = '';
		$fromName = '';
		if ( $user->isLoggedIn() ) {
			// Use real name if set
			$realName = $user->getRealName();
			if ( $realName ) {
				$fromName = $realName;
			} else {
				$fromName = $user->getName();
			}
			$fromAddress = $user->getEmail();
		}

		$additional = $config['AdditionalFields'];

		$formItems = [
			'FromName' => [
				'label-message' => 'contactpage-fromname',
				'type' => 'text',
				'required' => $config['RequireDetails'],
				'default' => $fromName,
			],
			'FromAddress' => [
				'label-message' => 'contactpage-fromaddress',
				'type' => 'email',
				'required' => $config['RequireDetails'],
				'default' => $fromAddress,
			],
			'FromInfo' => [
				'label' => '',
				'type' => 'info',
				'default' => Html::rawElement( 'small', [],
					$this->msg( 'contactpage-formfootnotes' )->escaped()
				),
				'raw' => true,
			],
			'Subject' => [
				'label-message' => 'emailsubject',
				'type' => 'text',
				'default' => $subject,
			],
		] + $additional + [
			'CCme' => [
				'label-message' => 'emailccme',
				'type' => 'check',
				'default' => $this->getUser()->getBoolOption( 'ccmeonemails' ),
			],
			'FormType' => [
				'class' => 'HTMLHiddenField',
				'label' => 'Type',
				'default' => $this->formType,
			]
		];

		if ( $config['IncludeIP'] && $user->isLoggedIn() ) {
			$formItems['IncludeIP'] = [
				'label-message' => 'contactpage-includeip',
				'type' => 'check',
			];
		}

		if ( $this->useCaptcha() ) {
			$formItems['Captcha'] = [
				'label-message' => 'captcha-label',
				'type' => 'info',
				'default' => $this->getCaptcha(),
				'raw' => true,
			];
		}

		$form = HTMLForm::factory( 'ooui',
			$formItems, $this->getContext(), "contactpage-{$this->formType}"
		);
		$form->setWrapperLegendMsg( 'contactpage-legend' );
		$form->setSubmitTextMsg( 'emailsend' );
		if ( $this->formType != '' ) {
			$form->setId( Sanitizer::escapeId( "contactpage-{$this->formType}" ) );

			$msg = $this->msg( "contactpage-legend-{$this->formType}" );
			if ( !$msg->isDisabled() ) {
				$form->setWrapperLegendMsg( $msg );
			}

			$msg = $this->msg( "contactpage-emailsend-{$this->formType}" );
			if ( !$msg->isDisabled() ) {
				$form->setSubmitTextMsg( $msg );
			}
		}
		$form->setSubmitCallback( [ $this, 'processInput' ] );
		$form->loadData();

		// Stolen from Special:EmailUser
		if ( !Hooks::run( 'EmailUserForm', [ &$form ] ) ) {
			return;
		}

		$result = $form->show();

		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			$out = $this->getOutput();
			$pageTitle = $this->msg( 'emailsent' );
			$pageText = 'emailsenttext';
			if ( $this->formType !== '' ) {
				$msg = $this->msg( "contactpage-emailsent-{$this->formType}" );
				if ( !$msg->isDisabled() ) {
					$pageTitle = $msg;
				}
				if ( !$this->msg( "contactpage-emailsenttext-{$this->formType}" )->isDisabled() ) {
					$pageText = "contactpage-emailsenttext-{$this->formType}";
				}
			}
			$out->setPageTitle( $pageTitle );
			$out->addWikiMsg( $pageText );

			$out->returnToMain( false );
		} else {
			if ( $config['RLStyleModules'] ) {
				$this->getOutput()->addModuleStyles( $config['RLStyleModules'] );
			}
			if ( $config['RLModules'] ) {
				$this->getOutput()->addModules( $config['RLModules'] );
			}
			$this->getOutput()->prependHTML( trim( $formText ) );
		}
	}

	/**
	 * @param array $formData
	 * @return bool|string
	 *      true: Form won't be displayed
	 *      false: Form will be redisplayed
	 *      string: Error message to display
	 */
	public function processInput( $formData ) {
		global $wgUserEmailUseReplyTo, $wgPasswordSender, $wgCaptcha;

		$config = $this->getTypeConfig();

		$request = $this->getRequest();
		$user = $this->getUser();

		$senderIP = $request->getIP();

		// Setup user that is going to recieve the contact page response
		$contactRecipientUser = User::newFromName( $config['RecipientUser'] );
		$contactRecipientAddress = MailAddress::newFromUser( $contactRecipientUser );

		// Used when user hasn't set an email, or when sending CC email to user
		$contactSender = new MailAddress(
			$config['SenderEmail'] ?: $wgPasswordSender,
			$config['SenderName']
		);

		$replyTo = null;

		$fromAddress = $formData['FromAddress'];
		$fromName = $formData['FromName'];
		if ( !$fromAddress ) {
			// No email address entered, so use $contactSender instead
			$senderAddress = $contactSender;
		} else {
			// Use user submitted details
			$senderAddress = new MailAddress( $fromAddress, $fromName );
			if ( $wgUserEmailUseReplyTo ) {
				// Define reply-to address
				$replyTo = $senderAddress;
			}
		}

		$includeIP = isset( $config['IncludeIP'] ) && $config['IncludeIP']
			&& ( $user->isAnon() || $formData['IncludeIP'] );
		$subject = $formData['Subject'];

		if ( $fromName !== '' ) {
			if ( $includeIP ) {
				$subject = $this->msg(
					'contactpage-subject-and-sender-withip',
					$subject,
					$fromName,
					$senderIP
				)->inContentLanguage()->text();
			} else {
				$subject = $this->msg(
					'contactpage-subject-and-sender',
					$subject,
					$fromName
				)->inContentLanguage()->text();
			}
		} elseif ( $fromAddress !== '' ) {
			if ( $includeIP ) {
				$subject = $this->msg(
					'contactpage-subject-and-sender-withip',
					$subject,
					$fromAddress,
					$senderIP
				)->inContentLanguage()->text();
			} else {
				$subject = $this->msg(
					'contactpage-subject-and-sender',
					$subject,
					$fromAddress
				)->inContentLanguage()->text();
			}
		} elseif ( $includeIP ) {
			$subject = $this->msg(
				'contactpage-subject-and-sender',
				$subject,
				$senderIP
			)->inContentLanguage()->text();
		}

		$text = '';
		foreach ( $config['AdditionalFields'] as $name => $field ) {
			$class = HTMLForm::getClassFromDescriptor( $name, $field );

			$value = '';
			// TODO: Support selectandother/HTMLSelectAndOtherField
			// options, options-messages and options-message
			if ( isset( $field['options-messages'] ) ) { // Multiple values!
				if ( is_string( $formData[$name] ) ) {
					$optionValues = array_flip( $field['options-messages'] );
					if ( isset( $optionValues[$formData[$name]] ) ) {
						$value = $this->msg( $optionValues[$formData[$name]] )->inContentLanguage()->text();
					} else {
						$value = $formData[$name];
					}
				} elseif ( count( $formData[$name] ) ) {
					$formValues = array_flip( $formData[$name] );
					$value .= "\n";
					foreach ( $field['options-messages'] as $msg => $optionValue ) {
						$msg = $this->msg( $msg )->inContentLanguage()->text();
						$optionValue = $this->getYesOrNoMsg( isset( $formValues[$optionValue] ) );
						$value .= "\t$msg: $optionValue\n";
					}
				}
			} elseif ( isset( $field['options'] ) ) {
				if ( is_string( $formData[$name] ) ) {
					$value = $formData[$name];
				} elseif ( count( $formData[$name] ) ) {
					$formValues = array_flip( $formData[$name] );
					$value .= "\n";
					foreach ( $field['options'] as $msg => $optionValue ) {
						$optionValue = $this->getYesOrNoMsg( isset( $formValues[$optionValue] ) );
						$value .= "\t$msg: $optionValue\n";
					}
				}
			} elseif ( $class === 'HTMLCheckField' ) {
				$value = $this->getYesOrNoMsg( $formData[$name] xor
					( isset( $field['invert'] ) && $field['invert'] ) );
			} elseif ( isset( $formData[$name] ) ) {
				// HTMLTextField, HTMLTextAreaField
				// HTMLFloatField, HTMLIntField

				// Just dump the value if its wordy
				$value = $formData[$name];
			} else {
				continue;
			}

			if ( isset( $field['contactpage-email-label'] ) ) {
				$name = $field['contactpage-email-label'];
			} elseif ( isset( $field['label-message'] ) ) {
				$name = $this->msg( $field['label-message'] )->inContentLanguage()->text();
			} else {
				$name = $field['label'];
			}

			$text .= "{$name}: $value\n";
		}

		/* @var SimpleCaptcha $wgCaptcha */
		if ( $this->useCaptcha() && !$wgCaptcha->passCaptchaFromRequest( $request, $user ) ) {
			return $this->msg( 'contactpage-captcha-error' )->plain();
		}

		// Stolen from Special:EmailUser
		$error = '';
		if ( !Hooks::run( 'EmailUser', [ &$contactRecipientAddress, &$senderAddress, &$subject,
			&$text, &$error ] )
		) {
			return $error;
		}

		if ( !Hooks::run( 'ContactForm', [ &$contactRecipientAddress, &$replyTo, &$subject,
			&$text, $this->formType, $formData ] )
		) {
			return false; // TODO: Need to do some proper error handling here
		}

		wfDebug( __METHOD__ . ': sending mail from ' . $senderAddress->toString() .
			' to ' . $contactRecipientAddress->toString() .
			' replyto ' . ( $replyTo == null ? '-/-' : $replyTo->toString() ) . "\n"
		);
		$mailResult = UserMailer::send(
			$contactRecipientAddress,
			$senderAddress,
			$subject,
			$text,
			[ 'replyTo' => $replyTo ]
		);

		if ( !$mailResult->isOK() ) {
			wfDebug( __METHOD__ . ': got error from UserMailer: ' . $mailResult->getMessage() . "\n" );
			return $this->msg( 'contactpage-usermailererror' )->text() . $mailResult->getMessage();
		}

		// if the user requested a copy of this mail, do this now,
		// unless they are emailing themselves, in which case one copy of the message is sufficient.
		if ( $formData['CCme'] && $fromAddress ) {
			$cc_subject = $this->msg( 'emailccsubject', $contactRecipientUser->getName(), $subject )->text();
			if ( Hooks::run( 'ContactForm',
				[ &$senderAddress, &$contactSender, &$cc_subject, &$text, $this->formType, $formData ] )
			) {
				wfDebug( __METHOD__ . ': sending cc mail from ' . $contactSender->toString() .
					' to ' . $senderAddress->toString() . "\n"
				);
				$ccResult = UserMailer::send( $senderAddress, $contactSender, $cc_subject, $text );
				if ( !$ccResult->isOK() ) {
					// At this stage, the user's CC mail has failed, but their
					// original mail has succeeded. It's unlikely, but still, what to do?
					// We can either show them an error, or we can say everything was fine,
					// or we can say we sort of failed AND sort of succeeded. Of these options,
					// simply saying there was an error is probably best.
					return $this->msg( 'contactpage-usermailererror' )->text() . $ccResult->getMessage();
				}
			}
		}

		Hooks::run( 'ContactFromComplete', [ $contactRecipientAddress, $replyTo, $subject, $text ] );

		return true;
	}

	/**
	 * @param bool $value
	 * @return string
	 */
	private function getYesOrNoMsg( $value ) {
		return $this->msg( $value ? 'htmlform-yes' : 'htmlform-no' )->inContentLanguage()->text();
	}

	/**
	 * @return boolean True if CAPTCHA should be used, false otherwise
	 */
	private function useCaptcha() {
		global $wgCaptchaClass, $wgCaptchaTriggers;

		return $wgCaptchaClass &&
			isset( $wgCaptchaTriggers['contactpage'] ) &&
			$wgCaptchaTriggers['contactpage'] &&
			!$this->getUser()->isAllowed( 'skipcaptcha' );
	}

	/**
	 * @return string CAPTCHA form HTML
	 */
	private function getCaptcha() {
		// NOTE: make sure we have a session. May be required for CAPTCHAs to work.
		\MediaWiki\Session\SessionManager::getGlobalSession()->persist();

		$captcha = ConfirmEditHooks::getInstance();
		$captcha->setTrigger( 'contactpage' );
		$captcha->setAction( 'contact' );

		$formInformation = $captcha->getFormInformation();
		$formMetainfo = $formInformation;
		unset( $formMetainfo['html'] );
		$captcha->addFormInformationToOutput( $this->getOutput(), $formMetainfo );

		return '<div class="captcha">' .
			$formInformation['html'] .
			"</div>\n";
	}
}
