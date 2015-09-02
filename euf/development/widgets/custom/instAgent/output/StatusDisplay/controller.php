<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class StatusDisplay extends Widget
{
    protected $field;
    protected $table;
    protected $fieldName;

    function __construct()
    {
        parent::__construct();
        $this->attrs['label'] = new Attribute(getMessage(FIELD_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_FORM_FIELD_VALUE_LBL), '{default label}');
        $this->attrs['name'] = new Attribute(getMessage(NAME_LBL), 'STRING', getMessage(COMBINATION_TB_FLD_DISP_ATTRIB_FORM_MSG), '');
        $this->attrs['highlight'] = new Attribute(getMessage(HIGHLIGHT_LBL), 'BOOL', getMessage(HIGHLIGHTS_TXT_FLDS_SRCH_TERM_LBL), true);
        $this->attrs['left_justify'] = new Attribute(getMessage(LEFT_JUSTIFY_LBL), 'BOOL', getMessage(LEFT_JUSTIFY_DATA_AND_LABEL_MSG), false);

        $this->CI->load->helper( 'label_helper' );
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  getMessage(DSP_VAL_DATA_ELEMENT_NAME_ATTRIB_MSG);
        $this->parms['kw'] = new UrlParam(getMessage(KEYWORD_LBL), 'kw', false, getMessage(THE_CURRENT_SEARCH_TERM_LBL), 'kw/search');
        $this->parms['a_id'] = new UrlParam(getMessage(ANS_ID_LBL), 'a_id', false, getMessage(ANSWER_ID_DISPLAY_INFORMATION_LBL), 'a_id/3');
        $this->parms['i_id'] = new UrlParam(getMessage(INCIDENT_ID_LBL), 'i_id', false, getMessage(INCIDENT_ID_DISPLAY_INFORMATION_LBL), 'i_id/7');
    }

    function getData()
    {
        if($this->retrieveAndInitializeData() === false)
            return false;
    }

    protected function retrieveAndInitializeData()
    {
        //Lowercase attributes
        $this->data['attrs']['name'] = strtolower($this->data['attrs']['name']);
        $validAttributes = parseFieldName($this->data['attrs']['name']);
        if(!is_array($validAttributes))
        {
            echo $this->reportError($validAttributes);
            return false;
        }

        $cacheKey = 'Display_' . $this->data['attrs']['name'];
        $cacheResults = checkCache($cacheKey);
        if(is_array($cacheResults))
        {
            list($this->field, $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']) = $cacheResults;
            $this->field = unserialize($this->field);
            return;
        }

        $fieldFormatter = getFieldFormatter($this->data['attrs']['highlight'], getUrlParm('kw'));
        $this->table = $validAttributes[0];
        $this->fieldName = $validAttributes[1];
        $this->field = getBusinessObjectField($this->table, $this->fieldName, $isFromProfile, $fieldFormatter);

        if($this->field === null)
            return false;
        if(is_string($this->field))
        {
            echo $this->reportError($this->field);
            return false;
        }
        if($this->field->data_type === EUF_DT_PASSWORD)
        {
            echo $this->reportError(getMessage(PASSWORD_FIELDS_DISPLAYED_MSG));
            return false;
        }

        //Grab the data we need.
        // Override the status display if it's 'Information provided by company' or 'Pending company information'.
        switch( $this->field->value )
        {
            case getLabel( 'SUPPORT_HISTORY_INFO_PROVIDED_FULL_LBL' ):
                $this->data['value'] = getLabel( 'SUPPORT_HISTORY_INFO_PROVIDED_ABRV_LBL' );
            break;
            case getLabel( 'SUPPORT_HISTORY_PENDING_INFO_FULL_LBL' ):
                $this->data['value'] = getLabel( 'SUPPORT_HISTORY_PENDING_INFO_ABRV_LBL' );
            break;
            default:
                $this->data['value'] = $this->field->value;
            break;
        }

        //We don't display fields whose values are an empty string or null
        if($this->data['value'] === '' || $this->data['value'] === null)
            return false;
        if($this->data['attrs']['label'] === '{default label}')
            $this->data['attrs']['label'] = $this->field->lang_name;
        setCache($cacheKey, array(serialize($this->field), $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']));
    }
}
