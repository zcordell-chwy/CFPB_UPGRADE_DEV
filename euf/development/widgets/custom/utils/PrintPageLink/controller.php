<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class PrintPageLink extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['label_link'] = new Attribute(getMessage(LINK_LABEL_CMD), 'STRING', getMessage(STRING_DISPLAYS_LINK_PAGE_LBL), getMessage(PRINT_CMD));
        $this->attrs['label_tooltip'] = new Attribute(getMessage(TOOLTIP_LBL), 'STRING', getMessage(STRING_DISPLAYS_HOVERS_LINK_LBL), getMessage(PRINT_THIS_PAGE_CMD));
        $this->attrs['icon_path'] = new Attribute(getMessage(ICON_PATH_LBL), 'STRING', getMessage(OPTIONAL_IMAGE_FILE_DISPLAY_LINK_LBL), 'images/Print.png');
        $this->attrs['label_icon_alt'] = new Attribute(getMessage(ICON_ALT_LABEL_LBL), 'STRING', getMessage(ALTERNATIVE_TXT_ICON_ICON_PATH_MSG), '');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(CLICKED_WIDGET_CALLS_BROWSERS_PRINT_MSG);
    }

    function getData()
    {
    }
}
