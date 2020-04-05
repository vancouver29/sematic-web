<?php
/**
 * This file is a copy from BlueSpiceDistribution/MobileFrontend/includes/skins/UserLoginMobileTemplate.php
 * Due to the fact, that there are no hooks, we need to replace the whole page
 * See ln 80 - 89, 101 - 117 for changes made
 */
class BSUserLoginMobileTemplate extends UserLoginMobileTemplate {

	/**
	 * @todo Refactor this into parent template
	 */
	public function execute() {
		$action = $this->data['action'];
		$token = $this->data['token'];
		$watchArticle = $this->getArticleTitleToWatch();
		$stickHTTPS = ( $this->doStickHTTPS() ) ? Html::input( 'wpStickHTTPS', 'true', 'hidden' ) : '';
		$username = ( strlen( $this->data['name'] ) ) ? $this->data['name'] : null;

		// @TODO make sure this also includes returnto and returntoquery from the request
		$query = array(
			'type' => 'signup',
		);
		// Security: $action is already filtered by SpecialUserLogin
		$actionQuery = wfCgiToArray( $action );
		if ( isset( $actionQuery['returnto'] ) ) {
			$query['returnto'] = $actionQuery['returnto'];
		}
		if ( isset( $actionQuery['returntoquery'] ) ) {
			$query['returntoquery'] = $actionQuery['returntoquery'];
			// Allow us to distinguish sign ups from the left nav to logins.
			// This allows us to apply story 1402 A/B test.
			if ( $query['returntoquery'] === 'welcome=yes' ) {
				$query['returntoquery'] = 'campaign=leftNavSignup';
			}
		}
		// For Extension:Campaigns
		$campaign = $this->getSkin()->getRequest()->getText( 'campaign' );
		if ( $campaign ) {
			$query['campaign'] = $campaign;
		}

		$signupLink = Linker::link( SpecialPage::getTitleFor( 'Userlogin' ),
			wfMessage( 'mobile-frontend-main-menu-account-create' )->text(),
			array( 'class'=> 'mw-mf-create-account mw-ui-block' ), $query );

		$login = Html::openElement( 'div', array( 'id' => 'mw-mf-login', 'class' => 'content' ) );

		$form = Html::openElement( 'div', array() ) .
			Html::openElement( 'form',
				array( 'name' => 'userlogin',
					'class' => 'user-login',
					'method' => 'post',
					'action' => $action ) ) .
			Html::openElement( 'div', array(
				'class' => 'inputs-box',
			) ) .
			Html::input( 'wpName', $username, 'text',
				array( 'class' => 'loginText',
					'placeholder' => wfMessage( 'mobile-frontend-username-placeholder' )->text(),
					'id' => 'wpName1',
					'tabindex' => '1',
					'size' => '20',
					'required' ) ) .
			Html::input( 'wpPassword', null, 'password',
				array( 'class' => 'loginPassword',
					'placeholder' => wfMessage( 'mobile-frontend-password-placeholder' )->text(),
					'id' => 'wpPassword1',
					'tabindex' => '2',
					'size' => '20' ) );
		//start copy from includes/templates/UserLogin.php (MW 1.23)
		if ( isset( $this->data['usedomain'] ) && $this->data['usedomain'] ) {
			$select = new XmlSelect( 'wpDomain', false, $this->data['domain'] );
			$select->setAttribute( 'tabindex', 3 );
			$select->addOption( wfMessage( 'bs-distribution-yourdomainname', '' )->plain(), '' );
			foreach ( $this->data['domainnames'] as $dom ) {
				$select->addOption( $dom );
			}
			$form .= $select->getHTML();
		}
		//end copy
		$form .=
			Html::closeElement( 'div' ) .
			Html::input( 'wpRemember', '1', 'hidden' ) .
			Html::input( 'wpLoginAttempt', wfMessage( 'mobile-frontend-login' )->text(), 'submit',
				array( 'id' => 'wpLoginAttempt',
					'class' => 'mw-ui-button mw-ui-constructive',
					'tabindex' => '3' ) ) .
			Html::input( 'wpLoginToken', $token, 'hidden' ) .
			Html::input( 'watch', $watchArticle, 'hidden' ) .
			$stickHTTPS .
			Html::closeElement( 'form' );
		//start change for possibility to display password reset link
		global $wgBsLdapShowPasswordResetLink;
		if ( $wgBsLdapShowPasswordResetLink ) {
			$form .=
				Html::element( 'a', array (
					'class' => 'mw-userlogin-help mw-ui-block',
			  'href' => SpecialPage::getTitleFor( 'PasswordReset' )->getLocalUrl(),
			  ),
			  wfMessage( 'passwordreset' ) );
		}
		//end change
		//start change for possibility to display signup link
		global $wgBsLdapShowSignupLink;
		if ( $wgBsLdapShowSignupLink ) {
			$form .= $signupLink;
		}
		//end change
		$form .=
			Html::closeElement( 'div' );
		echo $login;
		$this->renderGuiderMessage();
		$this->renderMessageHtml();
		echo $form;
		echo Html::closeElement( 'div' );
	}

}
