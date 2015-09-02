<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class LogoutLink2 extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['label'] = new Attribute(getMessage(LABEL_LBL), 'STRING', getMessage(LABEL_TO_DISPLAY_ON_LINK_LBL), getMessage(LOGOUT_CMD));
        $this->attrs['redirect_url'] = new Attribute(getMessage(REDIRECT_URL_LBL), 'STRING', getMessage(PG_REDIRECT_LOGOUT_ATTRIB_AFFECT_MSG), '/app/' . getConfig(CP_HOME_URL));
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  getMessage(WIDGET_DISP_LINK_LOGS_ACTIVATED_MSG);
    }

    function getData()
    {
        if(!isLoggedIn() || (isPta() && !getConfig(PTA_EXTERNAL_LOGOUT_SCRIPT_URL)))
            return false;

        if(isPta()){
            $this->data['js']['redirectLocation'] = urlParmDelete((getConfig(SEC_END_USER_HTTPS, 'COMMON') ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], 'sno');
        }
        else if($this->data['attrs']['redirect_url']){
            $this->data['js']['redirectLocation'] = $this->data['attrs']['redirect_url'];
        }
        else{
            $this->data['js']['redirectLocation'] = urlParmDelete($_SERVER['REQUEST_URI'], 'sno');
        }
        
        $this->data['js']['redirectLocation'] = urlParmDelete($this->data['js']['redirectLocation'], 'sno');
        if(sessionParm() !== '')
            $this->data['js']['redirectLocation'] = urlParmAdd($this->data['js']['redirectLocation'], 'session', getSubstringAfter(sessionParm(), "session/"));
        
        if(isPta()){
            $this->data['js']['redirectLocation'] = str_ireplace('%source_page%', urlencode($this->data['js']['redirectLocation']), getConfig(PTA_EXTERNAL_LOGOUT_SCRIPT_URL));
        }
        
        //If this interface utilizes the community module, make sure to log the user out of
        //there as well and tell them where to go afterwards
        if(getConfig(COMMUNITY_ENABLED, 'RNW')){
            if($socialLogoutUrl = getConfig(COMMUNITY_BASE_URL, 'RNW')){
                $socialLogoutUrl .= '/scripts/signout';
                //Check if redirect is fully qualified and on the same domain
                $redirectComponents = parse_url($this->data['js']['redirectLocation']);
                if($redirectComponents['host']){
                    $socialLogoutUrl .= '?redirectUrl=' . urlencode($this->data['js']['redirectLocation']);
                }
                else{
                    $socialLogoutUrl .= '?redirectUrl=' . urlencode(getShortEufBaseUrl('sameAsCurrentPage', $this->data['js']['redirectLocation']));
                }
                $this->data['js']['redirectLocation'] = $socialLogoutUrl;
                // temporarily set redirect to home page
                $this->data['js']['redirectLocation'] = "/app/utils/login_form/redirect/account/complaints/list";
            }
            else{
                echo $this->reportError(getMessage(COMMUNITY_ENABLED_CONFIG_SET_MSG));
                return false;
            }
        }
    }
}
