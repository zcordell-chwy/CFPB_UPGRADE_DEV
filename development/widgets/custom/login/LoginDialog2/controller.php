<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class LoginDialog2 extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['trigger_element'] = new Attribute(getMessage(TRIGGER_ELEMENT_LBL), 'STRING', getMessage(HTML_ELEMENT_ID_CLICK_LOGIN_DIALOG_LBL), '');
        $this->attrs['label_username'] = new Attribute(getMessage(USERNAME_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_USERNAME_BOX_LBL), getMessage(USERNAME_LBL));
        $this->attrs['label_password'] = new Attribute(getMessage(LABEL_PASSWORD_LBL), 'STRING', getMessage(LABEL_DISPLAY_PASSWORD_BOX_LBL), getMessage(PASSWD_LBL));
        $this->attrs['label_login_button'] = new Attribute(getMessage(LOGIN_BUTTON_LABEL_LBL), 'STRING', getMessage(LABEL_TO_DISPLAY_ON_SUBMIT_BUTTON_LBL), getMessage(LOG_IN_LBL));
        $this->attrs['label_cancel_button'] = new Attribute(getMessage(LABEL_FOR_CANCEL_BUTTON_LBL), 'STRING', getMessage(LABEL_FOR_CANCEL_BUTTON_LBL), getMessage(CANCEL_LBL));
        $this->attrs['label_dialog_title'] = new Attribute(getMessage(WINDOW_TITLE_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_DIALOG_TITLE_LBL), getMessage(PLEASE_LOG_IN_TO_CONTINUE_MSG));
        $this->attrs['label_assistance'] = new Attribute(getMessage(ACCOUNT_ASSISTANCE_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_LINK_ACCT_ASST_MSG), getMessage(FORGOT_YOUR_USERNAME_OR_PASSWORD_MSG));
        $this->attrs['disable_password'] = new Attribute(getMessage(DISABLE_PASSWORD_INPUT_CMD), 'BOOL', getMessage(ST_TRUE_VAL_HONORED_EU_CUST_PASSWD_MSG), false);
        $this->attrs['append_to_url'] = new Attribute(getMessage(PARAMETER_TO_APPEND_TO_URL_LBL), 'STRING', getMessage(PARAM_APPEND_URL_REDIRECTED_LBL), '');
        $this->attrs['redirect_url'] = new Attribute(getMessage(REDIRECT_PAGE_LBL), 'STRING', getMessage(PG_REDIRECT_SUCCFUL_LOGIN_SET_PG_MSG), '');
        $this->attrs['assistance_url'] = new Attribute(getMessage(ASSISTANCE_URL_LBL), 'STRING', getMessage(URL_NAVIGATE_CLICK_LABEL_ASST_MSG), '/app/utils/account_assistance');
        $this->attrs['open_login_url'] = new Attribute(getMessage(OPEN_LOGIN_URL_CMD), 'STRING', getMessage(URL_PAGE_CONT_OPENLOGIN_WIDGETS_LBL), '');
        $this->attrs['label_open_login_link'] = new Attribute(getMessage(OPEN_LOGIN_LINK_CMD), 'STRING', sprintf(getMessage(LABEL_DISPLAYS_LINK_PCT_S_LBL), 'open_login_url'), getMessage(LOG_IN_USING_A_THIRD_PARTY_ACCOUNT_LBL));
        $this->attrs['username_help_text'] = new Attribute('username help text', 'STRING', "Help message to be displayed to uesers underneath the field where their username is entered.", 'Use only lowercase letters');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(WIDGET_DISP_MODAL_POPUP_LOGIN_FORM_MSG);
        $this->parms['redirect'] = new UrlParam(getMessage(REDIRECT_LBL), 'redirect', false, getMessage(ENCODED_LOC_URL_REDIRECT_SUCCESSFUL_LBL), 'redirect/home');
        $this->parms['username'] = new UrlParam(getMessage(USERNAME_LBL), 'username', false, getMessage(POPULATES_USERNAME_FLD_VALUE_URL_MSG), 'username/JohnDoe');
    }

    function getData()
    {
        if($this->data['attrs']['trigger_element'] === '')
        {
            echo $this->reportError(getMessage(MISSING_VAL_TRIGGER_ELEMENT_ATTRIB_MSG));
            return false;
        }
        if(getUrlParm('redirect'))
        {
            //We need to check if the redirect location is a fully qualified URL, or just a relative one
            $redirectLocation = urldecode(urldecode(getUrlParm('redirect')));
            $parsedURL = parse_url($redirectLocation);

            if($parsedURL['scheme'] || (beginsWith($parsedURL['path'], '/ci/') || beginsWith($parsedURL['path'], '/cc/')))
            {
                $this->data['js']['redirectOverride'] = $redirectLocation;
            }
            else
            {
                $this->data['js']['redirectOverride'] = beginsWith($redirectLocation, '/app/') ? $redirectLocation : "/app/$redirectLocation";
            }
        }
        if($this->data['attrs']['open_login_url'] && !stringContains($this->data['attrs']['open_login_url'], '/redirect/'))
        {
            $redirectPage = $this->data['js']['redirectOverride'] ?: ($this->data['attrs']['redirect_url'] ?: $_SERVER['REQUEST_URI']);
            $this->data['attrs']['open_login_url'] = urlParmAdd($this->data['attrs']['open_login_url'], 'redirect', urlencode(urlencode($redirectPage)));
        }

        //honor: (1) attribute's T value (2) config
        $this->data['attrs']['disable_password'] = ($this->data['attrs']['disable_password']) ? $this->data['attrs']['disable_password'] : !getConfig(EU_CUST_PASSWD_ENABLED);
        $this->data['username'] = getUrlParm('username');
    }
}
