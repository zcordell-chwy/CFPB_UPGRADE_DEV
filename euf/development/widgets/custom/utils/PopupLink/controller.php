<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class PopupLink extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['label_link'] = new Attribute(getMessage(LINK_LABEL_CMD), 'STRING', getMessage(STRING_DISPLAYS_LINK_PAGE_LBL), 'Terms of Service');
        $this->attrs['label_tooltip'] = new Attribute(getMessage(TOOLTIP_LBL), 'STRING', getMessage(STRING_DISPLAYS_HOVERS_LINK_LBL), '');
        $this->attrs['popup'] = new Attribute('Popup message', 'STRING', 'Message for popup window', '');
        $this->attrs['class_name'] = new Attribute('CSS Class name', 'STRING', 'CSS Class used for position and dimensions', '');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = "Link that will popup a message area when clicked.";
    }

    function getData()
    {
    }
}
