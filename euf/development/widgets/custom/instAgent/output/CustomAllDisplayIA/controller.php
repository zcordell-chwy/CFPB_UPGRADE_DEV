<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('CustomAllInputIA'))
    requireWidgetController('standard/input/CustomAllInput');

class CustomAllDisplayIA extends CustomAllInput
{
    function __construct()
    {
        parent::__construct();
        $this->widgetType = 'output';
        $this->attrs['table']->options =  array('incidents', 'contacts', 'answers');
        $this->attrs['highlight'] = new Attribute(getMessage(HIGHLIGHT_LBL), 'BOOL', getMessage(HIGHLIGHTS_TXT_FLDS_SRCH_TERM_LBL), true);
        unset($this->attrs['always_show_mask']);
    }
    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] = getMessage(WDGT_DISP_END_CF_END_VIS_DB_TB_MSG);
        $this->parms['a_id'] = new UrlParam(getMessage(ANS_ID_LBL), 'a_id', false, getMessage(ANSWER_ID_DISPLAY_INFORMATION_LBL), 'a_id/3');
    }
    function getData()
    {
        parent::getData();
    }
}
