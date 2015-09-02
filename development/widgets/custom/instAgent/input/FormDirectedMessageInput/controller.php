<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class FormDirectedMessageInput extends Widget
{
    protected $field;
    protected $table;
    protected $fieldName;
    function __construct()
    {
        parent::__construct();
        $this->attrs['label_input'] = new Attribute(getMessage(INPUT_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_INPUT_CONTROL_LBL), '{default_label}');
        $this->attrs['label_required'] = new Attribute(getMessage(REQUIRED_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_REQUIREMENT_MESSAGE_LBL), getMessage(PCT_S_IS_REQUIRED_MSG));
        $this->attrs['name'] = new Attribute(getMessage(NAME_LBL), 'STRING', getMessage(COMBINATION_TB_FLD_INPUT_ATTRIB_MSG), '');
        $this->attrs['required'] = new Attribute(getMessage(REQUIRED_LBL), 'BOOL', getMessage(SET_TRUE_FLD_CONT_VAL_CF_SET_REQD_MSG), false);
        $this->attrs['hint'] = new Attribute(getMessage(HINT_LBL), 'STRING', getMessage(HINT_TXT_DISP_FLD_CF_VAL_OVRRIDE_MSG), '');
        $this->attrs['always_show_hint'] = new Attribute(getMessage(ALWAYS_SHOW_HINT_LBL), 'BOOL', getMessage(SET_TRUE_FLD_HINT_HINT_DISPLAYED_MSG), false);
        $this->attrs['initial_focus'] = new Attribute(getMessage(INITIAL_FOCUS_LBL), 'BOOL', getMessage(SET_TRUE_FIELD_FOCUSED_PAGE_LOADED_MSG), false);
        $this->attrs['validate_on_blur'] = new Attribute(getMessage(VALIDATE_ON_BLUR_LBL), 'BOOL', getMessage(VALIDATES_INPUT_FLD_DATA_REQS_FOCUS_LBL), false);
        $this->attrs['always_show_mask'] = new Attribute(getMessage(ALWAYS_SHOW_MASK_LBL), 'BOOL', getMessage(SET_TRUE_FLD_MASK_VAL_EXPECTED_MSG), false);
        $this->attrs['default_value'] = new Attribute(getMessage(DEFAULT_VALUE_LBL), 'STRING', getMessage(DEF_VAL_POPULATE_FLD_CF_VAL_OVRRIDE_MSG), '');
        $this->attrs['allow_external_login_updates'] = new Attribute(getMessage(ALLOW_EXTERNAL_LOGIN_UPDATES_LBL), 'BOOL', getMessage(ALLWS_USERS_AUTHENTICATE_CP_EXT_MSG), false);
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = sprintf(getMessage(WDGT_ALLWS_USRS_ST_FLD_VLS_DB_MSG), 'name', 'name');
        $this->parms['i_id'] = new UrlParam(getMessage(INCIDENT_ID_LBL), 'i_id', false, getMessage(INCIDENT_ID_DISPLAY_INFORMATION_LBL), 'i_id/7');
    }

    function getData()
    {
        if($this->retrieveAndInitializeData() === false)
            return false;
        if($this->field->data_type === EUF_DT_FATTACH)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_FLD_TYPE_FILE_ATTACH_PLS_MSG), $this->fieldName));
            return false;
        }
    }

    /**
     * Recreated from middlelayer to handle custom objects.
     *
     * Returns the field object given the table and field to look for. If an error occured, a string will
     * be returned, denoting the error message.
     * @return mixed Object if field was found, string if not
     * @param string $table The business object to search within
     * @param mixed $field The field or custom field ID on the object to retrieve
     */
    protected function getBusinessObjectField($table, $field, $formatter = null)
    {
        /*
        if ($table === 'directedReq$message') {
            $this->CI->load->model('custom/dr_model');
            $middleLayerObject = $this->CI->dr_model->getBlank();
        }
        
        if($middleLayerObject === null) {
            return null;
        }
        */
        if ($field  === "msgText")
            $middleLayerObject->$field->data_type = EUF_DT_MEMO;
        
        return $middleLayerObject->$field;
    }

    /**
     * Recreted from middlelayer.php for custom objects.
     *
     * Parse and validate a display field name attribute.
     * @return Mixed String error message if attribute is invalide, or an array of the table and field parsed values
	 * @param String $name The field name attribute in the form table.field
	 */
	protected function parseFieldName($name, $input = false)
	{
	    if(!$name)
	        return sprintf(getMessage(PCT_S_ATTRIB_IS_REQUIRED_MSG), 'name');
	    $nameParts = explode('.', $name);
	    if(count($nameParts) !== 2)
	        return sprintf(getMessage(FND_INV_VAL_NAME_ATTRIB_VAL_MSG), $name);
        if($nameParts[1] === '')
            return getMessage(FND_EMPTY_VAL_FLD_NAME_NAME_ATTRIB_MSG);
        return $nameParts;
    }

    protected function retrieveAndInitializeData()
    {
        $cacheKey = 'Input_' . $this->data['attrs']['name'];
        $cacheResults = checkCache($cacheKey);
        if(is_array($cacheResults))
        {
            list($this->field, $this->table, $this->fieldName, $this->data) = $cacheResults;
            $this->field = unserialize($this->field);
            return;
        }
        
        $validAttributes = $this->parseFieldName($this->data['attrs']['name'], true);
        if(!is_array($validAttributes))
        {
            echo $this->reportError($validAttributes);
            return false;
        }
        $this->table = $validAttributes[0];
        $this->fieldName = $validAttributes[1];
        $this->field = $this->getBusinessObjectField($this->table, $this->fieldName);
        
        //Common between both standard + custom fields
        $this->data['js']['type'] = $this->field->data_type;
        $this->data['js']['table'] = $this->table;
        $this->data['js']['name'] = $this->fieldName;
 
        if($this->data['attrs']['label_input'] === '{default_label}')
            $this->data['attrs']['label_input'] = $this->field->lang_name;
        
        if($this->data['attrs']['hint'] && strlen(trim($this->data['attrs']['hint']))){
            $this->data['js']['hint'] = $this->data['attrs']['hint'];
        }
        
        setCache($cacheKey, array(serialize($this->field), $this->table, $this->fieldName, $this->data));
    }
    
}
