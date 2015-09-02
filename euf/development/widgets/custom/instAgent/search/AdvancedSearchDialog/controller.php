<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class AdvancedSearchDialog extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['label_dialog_title'] = new Attribute(getMessage(DIALOG_TITLE_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_DIALOG_TITLE_LBL), getMessage(ADVANCED_SEARCH_LBL));
        $this->attrs['label_link'] = new Attribute(getMessage(LINK_LABEL_CMD), 'STRING', getMessage(LABEL_DISPLAY_LINK_LAUNCHES_DIALOG_LBL), getMessage(ADVANCED_SEARCH_LBL));
        $this->attrs['label_search_button'] = new Attribute(getMessage(SEARCH_BUTTON_LABEL_CMD), 'STRING', getMessage(LABEL_DISPLAY_SEARCH_BUTTON_LBL), getMessage(SEARCH_LBL));
        $this->attrs['label_cancel_button'] = new Attribute(getMessage(CANCEL_BUTTON_LABEL_CMD), 'STRING', getMessage(LABEL_DISPLAY_DIALOG_CANCEL_BUTTON_LBL), getMessage(CANCEL_LBL));
        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['report_page_url'] = new Attribute(getMessage(REPORT_PAGE_LBL), 'STRING', getMessage(PG_DSP_BTN_CLICKED_LEAVE_BLANK_MSG), '');
        $this->attrs['search_tips_url'] = new Attribute(getMessage(SEARCH_TIPS_URL_CMD), 'STRING', getMessage(PAGE_TO_DISPLAY_SEARCH_TIPS_LBL), '/app/utils/help_search');
        $this->attrs['display_products_filter'] = new Attribute(getMessage(DISPLAY_PRODUCTS_FILTER_CMD), 'BOOL', getMessage(DTERMINES_LBL), true);
        $this->attrs['display_categories_filter'] = new Attribute(getMessage(DISPLAY_CATEGORIES_FILTER_CMD), 'BOOL', getMessage(DETERMINES_LBL), true);
        $this->attrs['display_sort_filter'] = new Attribute(getMessage(DISPLAY_SORT_FILTER_CMD), 'BOOL', getMessage(DETERMINES_SORTLIST2_WIDGET_MSG), true);
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  getMessage(WDGT_DISP_LINK_INVOKES_MODAL_MSG);
    }

    function getData()
    {
        $this->data['webSearch'] = ($this->data['attrs']['report_id'] === CP_NOV09_WIDX_DEFAULT || $this->data['attrs']['report_id'] === CP_WIDX_REPORT_DEFAULT);
    }
}
