<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class NavigationTab2Custom extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['label_tab'] = new Attribute(getMessage(TAB_LABEL_LBL), 'STRING', getMessage(LABEL_TO_DISPLAY_ON_THE_TAB_LBL), getMessage(SUPPORT_HOME_TAB_HDG));
        $this->attrs['link'] = new Attribute(getMessage(LINK_LBL), 'STRING', getMessage(WHAT_PAGE_NAVIGATION_LINK_GOES_LBL), '/app/home');
        $this->attrs['pages'] = new Attribute(getMessage(PAGES_LBL), 'STRING', getMessage(COMMA_SEPARATED_L_PAGES_CHG_CSS_LBL), '');
        $this->attrs['subpages'] = new Attribute(getMessage(SUBPAGES_LBL), 'STRING', getMessage(COMMA_SEPARATED_L_LABEL_URL_PAIRS_MSG), '');
        $this->attrs['css_class'] = new Attribute(getMessage(CSS_CLSS_LBL), 'STRING', getMessage(CSS_CLASS_CHANGE_PAGE_LINK_ATTRIB_MSG), 'rn_SelectedTab');
        $this->attrs['target'] = new Attribute(getMessage(TARGET_OF_LINK_LBL), 'STRING', getMessage(CNTROLS_DOC_DISPLAYED_FOLLOWS_MSG), '_self');
        $this->attrs['external'] = new Attribute(getMessage(EXTERNAL_LINK_LBL), 'BOOL', getMessage(SET_TRUE_ALLOWS_LINKS_SITES_RN_CP_MSG), false);
        $this->attrs['searches_done'] = new Attribute(getMessage(SEARCHES_DONE_LBL), 'INT', getMessage(SEARCHES_REQUIRED_BEFORE_TAB_LBL), 0);
        $this->attrs['searches_done']->min = 0;
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(WIDGET_DISP_LINK_NAVIGATION_MSG);
    }

    function getData()
    {
        if(!$this->data['attrs']['external'])
            $this->data['attrs']['link'] .= sessionParm();

        //output the selected css class if we're on it's page
        if($this->data['attrs']['pages'] && $this->data['attrs']['css_class'])
        {
            $this->data['attrs']['pages'] = explode(',', str_replace(' ', '', $this->data['attrs']['pages']));
            //$currentPage = $this->CI->page;
            //logMessage($this->CI->uri->uri_string());
            $currentPage = explode('page/render/', $this->CI->uri->uri_string());
            $currentPage = rtrim($currentPage[1], '/');
            foreach($this->data['attrs']['pages'] as $page)
            {
                if($currentPage === $page)
                    $this->data['cssClass'] = $this->data['attrs']['css_class'];
            }
        }
        //get sub-pages, if any
        if($this->data['attrs']['subpages'])
        {
            $this->data['subpages'] = array();
            //get ea. comma-separated key > value pair
            $subPages = explode(',', $this->data['attrs']['subpages']);
            foreach($subPages as $value)
            {
                $splitPosition = strrpos($value, ' > ');
                if($splitPosition === false)
                {
                    echo $this->reportError("Invalid formatting of subpages attribute value '$value' : must be Name > URL separated.");
                    return false;
                }
                $pairValue['title'] = trim(substr($value, 0, $splitPosition));
                $pairValue['href'] = trim(substr($value, $splitPosition + 2));
                array_push($this->data['subpages'], $pairValue);
            }
        }
        if($this->data['attrs']['searches_done'] > 0)
        {
            $this->data['js']['searches'] = $this->CI->session->getSessionData('numberOfSearches');
            if($this->data['js']['searches'] < $this->data['attrs']['searches_done'])
                $this->data['hiddenClass'] = 'rn_Hidden';
        }
    }
}
