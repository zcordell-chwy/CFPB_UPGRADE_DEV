<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class KeywordText2 extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['label_text'] = new Attribute(getMessage(LABEL_LBL), 'STRING', getMessage(STRING_LABEL_DISP_WARN_MODIFICATION_LBL), getMessage(SEARCH_BY_KEYWORD_CMD));
        $this->attrs['initial_focus'] = new Attribute(getMessage(INITIAL_FOCUS_LBL), 'BOOL', getMessage(SET_TRUE_FIELD_FOCUSED_PAGE_LOADED_MSG), false);
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(WIDGET_DISP_INPUT_TEXTBOX_ALLOWS_MSG);
        $this->parms['kw'] = new UrlParam(getMessage(KEYWORD_LBL), 'kw', false, getMessage(SETS_TXT_KEYWORD_BOX_URL_PARAM_VAL_LBL), 'kw/roam');
    }

    function getData()
    {
        $this->CI->load->model('custom/Report_model2');
        setFiltersFromUrl($this->data['attrs']['report_id'], $filters);
        $reportToken = createToken($this->data['attrs']['report_id']);
        $results = $this->CI->Report_model2->getDataHTML($this->data['attrs']['report_id'], $reportToken, $filters, null);
        $this->data['initialValue'] = $results['search_term'];
        $this->data['js']['initialValue'] = $this->data['initialValue'];
        $this->data['js']['rnSearchType'] = 'keyword';
        $this->data['js']['searchName'] = 'keyword';
    }
}
