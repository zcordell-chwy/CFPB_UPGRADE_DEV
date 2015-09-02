<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class FormInput extends Widget
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
        if($this->field->data_type === EUF_DT_HIERMENU)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_FLD_TYPE_PROD_CAT_PLS_INPUT_S_MSG), $this->fieldName));
            return false;
        }
        if($this->field->data_type === EUF_DT_FATTACH)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_FLD_TYPE_FILE_ATTACH_PLS_MSG), $this->fieldName));
            return false;
        }
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
        $this->data['attrs']['name'] = strtolower($this->data['attrs']['name']);

        $validAttributes = parseFieldName($this->data['attrs']['name'], true);
        if(!is_array($validAttributes))
        {
            echo $this->reportError($validAttributes);
            return false;
        }
        $this->table = $validAttributes[0];
        $this->fieldName = $validAttributes[1];
        $this->field = getBusinessObjectField($this->table, $this->fieldName, $isProfileField);

        if(is_string($this->field))
        {
            echo $this->reportError($this->field);
            return false;
        }
        
        //Not a visible custom field
        if(is_null($this->field))
            return false;

        //Common between both standard + custom fields
        $this->data['js']['type'] = $this->field->data_type;
        $this->data['js']['table'] = $this->table;
        $this->data['js']['name'] = $this->fieldName;
        if($isProfileField === true)
            $this->data['js']['profile'] = true;
        $this->data['readOnly'] = $this->field->readonly;
        if($this->field->readonly)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_READ_FIELD_INPUT_WIDGET_MSG), $this->fieldName));
            return false;
        }
        $this->data['js']['mask'] = $this->field->mask;
        if($this->field->menu_items)
            $this->data['menuItems'] = $this->field->menu_items;
        if(!is_null($this->field->max_val))
            $this->data['js']['maxVal'] = $this->field->max_val;
        if(!is_null($this->field->min_val))
            $this->data['js']['minVal'] = $this->field->min_val;
        if($this->data['attrs']['label_input'] === '{default_label}')
            $this->data['attrs']['label_input'] = $this->field->lang_name;
        if($this->field->field_size)
        {
            $this->data['maxLength'] = $this->field->field_size;
            //allow for -/+ sign on ints
            if($this->field->data_type === EUF_DT_INT)
                $this->data['maxLength']++;
            else if($this->field->data_type === EUF_DT_MEMO)
                $this->data['js']['fieldSize'] = $this->field->field_size;
        }

        //custom field specific
        if($this->field instanceof CustomField)
        {
            //If not Live Chat, don't show non-editable fields
            if(((($this->field->visibility & VIS_LIVE_CHAT) == false) && $this->CI->page === getConfig(CP_CHAT_URL)) ||
               ((($this->field->visibility & VIS_ENDUSER_EDIT_RW) == false) && (!($this->CI->page === getConfig(CP_CHAT_URL)))))
            {
                    echo $this->reportError(sprintf(getMessage(PCT_S_READ_FIELD_INPUT_WIDGET_MSG), $this->fieldName));
                    return false;
            }
            $this->data['js']['name'] = preg_replace('(^c\$)', '', $this->fieldName);
            $this->data['js']['customID'] = $this->field->custom_field_id;
            if($this->field->lang_hint && strlen(trim($this->field->lang_hint)))
                $this->data['js']['hint'] = $this->field->lang_hint;
            $this->data['attrs']['required'] = ($this->field->required === 1) ? true : $this->data['attrs']['required'];
            if(($this->field->data_type === EUF_DT_VARCHAR) && ($this->field->attr & CFATTR_URL))
                $this->data['js']['url'] = true;
            if(($this->field->data_type === EUF_DT_VARCHAR) && ($this->field->attr & CFATTR_EMAIL))
                $this->data['js']['email'] = true;
        }
        else if($this->field instanceof ChannelField)
        {
            $this->data['js']['name'] = str_replace('$', '', $this->fieldName);
            $this->data['js']['channelID'] = $this->field->id;
        }
        //standard field w/read-only visibility
        elseif($this->field->readonly)
        {
            return false;
        }
        
        if($this->data['attrs']['hint'] && strlen(trim($this->data['attrs']['hint']))){
            $this->data['js']['hint'] = $this->data['attrs']['hint'];
        }
        
        $this->data['value'] = $this->setFieldValue();
        if($this->field->data_type !== EUF_DT_PASSWORD){
            $this->data['js']['prev'] = $this->data['value'];
        }
        //Override visibility of doing a contact field and pta or chat
        if($this->table === 'contacts' && isLoggedIn())
        {
            if($this->CI->page === getConfig(CP_CHAT_URL) ||
               (getConfig(PTA_ENABLED) && $this->fieldName === 'login') ||
               (isPta() && (!$this->data['attrs']['allow_external_login_updates'] || $this->fieldName === 'login')))
            {
                $this->data['readOnly'] = true;
            }
        }
        setCache($cacheKey, array(serialize($this->field), $this->table, $this->fieldName, $this->data));
    }
    
    /**
     * Retrieves default value for field either out of the URL or based on
     * the attribute set. 
     * @return String The value for the field
     */
    private function setFieldValue()
    {
        $fieldValue = null;
        $valueSpecifiedInUrl = getUrlParm($this->data['attrs']['name']);
        $valueSpecifiedInPost = $this->CI->input->post(str_replace(".", "_", $this->data['attrs']['name']));
        $dynamicDefaultValue = '';
        
        if($valueSpecifiedInPost !== false && $valueSpecifiedInPost !== '')
            $dynamicDefaultValue = str_replace("'", '&#039;', str_replace('"', '&quot;', $valueSpecifiedInPost));
        else if($valueSpecifiedInUrl !== null && $valueSpecifiedInUrl !== '')
            $dynamicDefaultValue = $valueSpecifiedInUrl;
        else if($this->data['attrs']['default_value'] !== '')
            $dynamicDefaultValue = $this->data['attrs']['default_value'];

        //If this is a custom field, it has a default value that's the same (in a string comparison) as what it's current value is
        //and a value is specified in the URL or through an attribute, overwrite the value
        if($this->field instanceof CustomField && $this->field->default_value !== null && ((string)$this->field->value === (string)$this->field->default_value) && $dynamicDefaultValue !== '')
        {
            $this->field->value = null;
        }

        if($this->field->value !== null && !is_array($this->field->value)){
            $fieldValue = htmlspecialchars($this->field->value, ENT_QUOTES, 'UTF-8', false);
        }
        else if($dynamicDefaultValue !== ''){
            $fieldValue = $dynamicDefaultValue;
        }
        return $fieldValue;
    }
}
