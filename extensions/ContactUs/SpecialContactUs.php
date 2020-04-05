<?php
/** ContactUs class
* This is what is executed when a user accesses Special:ContactUs
*/
if (!defined('MEDIAWIKI'))
    die("Not a valid entry point.");

class SpecialContactUs extends FormSpecialPage {
    /**
     * @var \User A user object to work with.
     */
    protected $user;
    /**
     * @var array People who will receive emails
     */
    private $recipients;
    /**
     * @var array Recipient groups
     */
    private $groups;
    /**
     * @var bool Whether or not to disable grouped emails.
     * If this is true, emails will go to all listed users, and their
     * groups will be ignored if they're set.
     */
    private $no_groups;
    /**
     * Constructor. Sets up the User object, then
     * calls the parent's constructor.
      */

    function __construct(){
        // Make $this->user into a user object
        $this->user = $this->getUser();
        // And get the result of the parent constructor.
		parent::__construct( 'ContactUs' );
    }

    /**
     * Get the custom message
     * @return null|string $cont
     */
    private function load_custom_message(){
        $msg = 'Contactus_custom';
        $page = Title::newFromText($msg, NS_MEDIAWIKI);
        if (!$page->exists())
            return null;
        else {
            $page = wikiPage::factory($page);
            $cont = $page->getContent();
            $cont = $cont->mText;
        }
        return $cont;
    }

    /**
     * Gathers all settings information from the mediawiki pages
     * and sets appropriate variables
     * @return array $settings
     * @throws ErrorPageError
     *
     */
    private function load_all_settings(){
        global $wgContactUs_Recipients, $wgContactUs_Groups, $wgContactUs_DisableGroups;
        if ($wgContactUs_DisableGroups === true)
            $this->no_groups = true;
        $return = array();
        if (!is_array($wgContactUs_Recipients)){
          if  (in_array('sysop',$this->user->getAllGroups()))
                $msg = 'contactus-settings-error-sysop';
          else
                $msg = 'contactus-settings-error-public';
            throw new ErrorPageError('contactus-bad-settings', $msg, wfMessage('contactus-bad-recipients') );
        }
        $this->recipients = $wgContactUs_Recipients;
        if ($this->no_groups !== true){
            if (!is_array($wgContactUs_Groups)){
                if  (in_array('sysop',$this->user->getAllGroups()))
                    $msg = 'contactus-settings-error-sysop';
                else
                    $msg = 'contactus-settings-error-public';
                throw new ErrorPageError('contactus-bad-settings', $msg, wfMessage('contactus-bad-groups'));
            }
        $this->groups = $wgContactUs_Groups;
        }

    }

    /**
     * Gathers recipients' emails
     * @param string|bool $group Which group is being emailed. Set to false if grouped emails are disabled.
     * @return array|MailAddress $email
     */
    private function get_to_address($group){
        $email = array();
        foreach ($this->recipients as $name => $groups){
            if ($this->no_groups !== true && in_array($group, $groups)):
                $address = User::newFromName($name);
                $email[] = new MailAddress($address);
            else:
                $address = User::newFromName($name);
                $email[] = new MailAddress($address);
            endif;
        }
        return $email;

    }
    /**
     * This function actually sends the email.
     * @param array|MailAddress $to array of email addresses to receive the message
     * @param string|MailAddress $from sender's email address
     * @param string $subject the subject of the message
     * @param string $body the message itself
     * @return bool|array $errors Returns true if everything went alright,
     * or returns an error array if there were problems.
     */
    private function send_mail($to, $from, $subject, $body){
        $status = userMailer::send($to, $from, $subject, $body);
        if ($status->isGood() == true)
            return true;
        $errors = $status->getErrorsArray();
        return $errors;
    }

    /**
     * Create the template to pass to $this->getForm()
     * @return Array
     */
    protected function getFormFields(){
        $formDescriptor = array(
            'user-email' => array(
                'label-message' => 'contactus-your-email',
                'type' => 'text',
                'required' => 'true',
            ));
            if ($this->no_groups !== true){
                $formDescriptor['problem-or-question'] = array(
                    'type' => 'select',
                    'label-message' => 'contactus-problem-question',
                );
                foreach ($this->groups as $key => $val){
                    $formDescriptor['problem-or-question']['options'][$val] = $key;
                }
            }

        $formDescriptor['subject'] = array(
                'class' => 'HTMLTextField',
                'label-message' => 'contactus-subject',
                'maxlength' => 200,
                'size' => 60,
                'required' => true,
            );
        $formDescriptor['body'] = array(
                'label-message' => 'contactus-message',
                'type' => 'textarea',
                'rows' => 20,
                'cols' => 80,
                'required' => true,
            );
        return $formDescriptor;
    }

    /**
     * Validate the user's email.
     * @param string $email the data passed onSubmit
     * @return bool|string false|$email_v returns false if there's a problem with the email
     */
    private function validate_email($email){
/*        preg_match('/^.*@.*\..*$/', $email, $matches);
        if (empty($matches))
            return false;
        else {
*/            $email = new MailAddress($email);
            return $email;
        }

    /**
     * Validate the subject and body to make sure they aren't doing anything funky.
     * @param string $subject
     * @param string $body
     * @return bool|array false|$message Returns false upon failing validation,
     * otherwise returns the subject and body in an associative array
     */
    private function validate_message($subject, $body){
        if (!$subject || !$body)
            return false;

        $message = array('subject' => $subject, 'body' =>$body);
        return $message;
    }

    /**
     * @structure submit
     * Data submitted
     * onSubmit called
     * acquire data
     * check recipient groups
     * trim recipients based on groups
     * put recipients in $to as an array
     * get user email and validate it
     * assign it to $from
     * get subject and body and validate both
     * assign them to $subject and $body
     * if anything fails up to this point, everything returns false and aborts the submit
     * Finally do userMailer::send($to, $from, $subject, $body)
     * Get status of mailer, and return either true or an array of errors
     */

    /**
     * Submit callback. Runs when a user submits the form.
     * @param array $data
     * @return Array|Bool|void
     */
    function onSubmit(array $data){
        if (empty($data['subject']) OR empty($data['body']) OR empty($data['user-email'])){
            return false;
        }
        $data['problem-or-question'] ? $query = $data['problem-or-question'] : $query = false;
        $to = $this->get_to_address($query);
        if ($to === false)
            return false;

        $from = $this->validate_email($data['user-email']);
        if ($from === false)
            return false;
        $message = $this->validate_message($data['subject'], $data['body']);
        if ($message['subject'] === false OR $message['body'] === false)
            return false;
        $status = $this->send_mail($to, $from, $message['subject'], $message['body']);
        if ($status === true)
            return true;
        if (is_array($status)){
            return $status;
        }
        return false;
    }

    /**
     * What to do when we're successful
     */
    function onSuccess(){
        $op = $this->getOutput();
        $op->setTitle(Title::newFromText($this->msg('contactus-success')->parse()));
        $op->addWikiMsg('contactus-email-sent');
    }
    /**
     * @structure execute
     * Set headers
     * resolve context
     * check permissions/blocks and deny access if necessary
     * check $this->no_groups: Skip question form if true
     * gather group settings if false;
     * build form
     */

    /**
    * Page execution.
    * @param null|string $par
    * @return void
    * @throws userBlockedError
    */
    function execute( $par ) {
        // execute must call this
        $this->setHeaders();
        $msg = $this->load_custom_message();
        if ($msg == null)
            $msg = wfMessage('contactus-page-desc')->parse();
        $op = $this->getOutput();
        $op->addElement('p', array('id' => 'contactus-description'), $msg);
        $this->load_all_settings();

        if ($this->user->isBlocked())
            throw new userBlockedError($this->user->getBlock());
        //$this->build_form('email') ;
        parent::execute($par);
    }

    public function isListed(){
        return false;
    }
}

