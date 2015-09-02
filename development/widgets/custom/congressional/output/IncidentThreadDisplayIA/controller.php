<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

//if(!class_exists('DataDisplay'))
//    requireWidgetController('standard/output/DataDisplay');

//class IncidentThreadDisplay extends DataDisplay
class IncidentThreadDisplayIA extends Widget {

    protected $field;
    protected $table;
    protected $fieldName;

    function __construct()
    {
        parent::__construct();
        //unset($this->attrs['left_justify']);
        $this->attrs['label'] = new Attribute(getMessage((987)), 'STRING', getMessage((1390)), '{default label}');
        $this->attrs['highlight'] = new Attribute(getMessage((1153)), 'BOOL', getMessage((1154)), true);
        $this->attrs['name'] = new Attribute(getMessage(NAME_LBL), 'STRING', getMessage(NAME_ATTRIB_INC_THREAD_INC_MSG), '');
        $this->attrs['thread_order'] = new Attribute(getMessage(DISPLAY_ORDER_CMD), 'OPTION', getMessage(DETERMINES_DISP_THREAD_POSTS_LBL), 'descending');
        $this->attrs['thread_order']->options = array('ascending', 'descending');
    }

    function generateWidgetInformation()
    {
        //parent::generateWidgetInformation();
        $this->info['notes'] = getMessage(DSP_ENTRIES_INC_CORRESPONDENCE_UC_MSG);
        $this->parms['i_id'] = new UrlParam(getMessage(INCIDENT_ID_LBL), 'i_id', false, getMessage(INCIDENT_ID_DISPLAY_INFORMATION_LBL), 'i_id/7');
        //unset($this->parms['a_id']);
        //unset($this->parms['kw']);
    }

    function getData()
    {
        //if(parent::retrieveAndInitializeData() === false)
        if($this->retrieveAndInitializeData() === false)
            return false;

        // Validate data type
        if ($this->field->data_type !== EUF_DT_THREAD)
        {
            echo $this->reportError(getMessage(INCIDENTTHREADDISPLAY_DISP_THREAD_MSG));
            return false;
        }
        else if($this->data['value'] && $this->data['attrs']['thread_order'] === 'ascending')
        {
            $this->data['value'] = array_reverse($this->data['value'], true);
        }
    }

    protected function retrieveAndInitializeData () {

        $cacheKey = 'Display_' . $this->data['attrs']['name'];
        $cacheResults = checkCache($cacheKey);
        if (is_array($cacheResults)) {
            list($this->field, $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']) = $cacheResults;
            $this->field = unserialize($this->field);
            return;
        }

        $this->table = "incidents";
        $this->fieldName = "thread";
        $this->CI->load->model('custom/inboundreferral_model');
        $incThread = $this->CI->InboundReferral_model->getBusinessObjectField($this->table, $this->fieldName, getUrlParm('referral_id'));
        if ($incThread === false) {
            return false;
        }
        $this->field = $incThread;
        if (is_null($this->field) || $this->field === false) {
            return false;
        }
        if (is_string($this->field)) {
            echo $this->reportError($this->field);
            return false;
        }
        if ($this->field->data_type === (7)) {
            echo $this->reportError(getMessage((1935)));
            return false;
        }
        $this->data['value'] = $this->field->value;
        if ($this->data['value'] === '' || $this->data['value'] === null)
            return false;
        if ($this->data['attrs']['label'] === '{default label}')
            $this->data['attrs']['label'] = $this->field->lang_name;
        setCache($cacheKey, array(serialize($this->field), $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']));
    }

}
