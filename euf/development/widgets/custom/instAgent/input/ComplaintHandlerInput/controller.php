<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('FormInput'))
    requireWidgetController('standard/input/FormInput');

class ComplaintHandlerInput extends FormInput
{
    protected $organizationID;

    function __construct()
    {
        parent::__construct();
        $this->CI->load->helper('label_helper');
        $this->CI->load->model( 'custom/redirectedCase_model' );

        //this FormInput attr doesn't apply to SelectionInput
        unset($this->attrs['always_show_mask']);
        $this->attrs['label_input'] = new Attribute(getMessage(INPUT_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_INPUT_CONTROL_LBL), null);
        $this->attrs['label_nothing_selected'] = new Attribute(getMessage(NOTHING_SELECTED_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_VALUE_SELECTED_LBL), 'Choose...');
        $this->attrs['is_checkbox'] = new Attribute("Swich yes/no to checkbox", 'BOOL', "Display yes/no as a checkbox", true);
        $this->attrs['style_custom'] = new Attribute("Custom CSS Style", 'STRING', "Custom CSS to move the field", null);
        $this->attrs['error_nav'] = new Attribute("Custom JavaScript function", 'STRING', "Call function when clicking on error message", null);
        $this->attrs['error_msg'] = new Attribute("Custom Error Message", 'STRING', "Display custom error message", null);
        $this->attrs['select_required_pos'] = new Attribute("Flag to display required label", 'INT', "Display red * next to menu drop down label. Specify left position in px", 0);
        $this->attrs['remove_menu_items'] = new Attribute("Remove specific menu items", 'STRING', "Comma seperated list of menu labels to remove for menu custom fields", null);
        $this->attrs['co_status'] = new Attribute("Company status array", 'STRING', "Array of company status labels", getLabel('CO_STATUS_ARRAY'));
        $this->attrs['co_status_desc'] = new Attribute("Company status description array", 'STRING', "Array of company status descriptions", getLabel('CO_STATUS_DESC_ARRAY'));

        $this->organizationID = null;
    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] = getMessage(WDGT_ALLWS_USERS_SET_FLD_VALS_DB_MSG);
    }

    function getData()
    {
        // User must be logged in and associated with an organization for this to work.
        $this->organizationID = $this->CI->session->getProfileData( 'org_id' );
        if( is_null( $this->organizationID ) || !is_numeric( $this->organizationID ) )
            return false;

        if( $this->retrieveAndInitializeData() === false )
            return false;

        // add functionality to remove specific menu items from menu fields
        if ($this->data['menuItems'] && $this->data['attrs']['remove_menu_items'])
        {
            $this->data['menuItems'] = $this->_removeMenuItems(explode(",", $this->data['attrs']['remove_menu_items']));
        }

        //Status field should not be shown if there is not an incident ID on the page
        if($this->fieldName === 'status' && !getUrlParm('i_id'))
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_FLD_DISPLAYED_PG_I_ID_PARAM_MSG), 'incidents.status'));
            return false;
        }

        if($this->field->data_type !== EUF_DT_SELECT && $this->field->data_type !== EUF_DT_CHECK && $this->field->data_type !== EUF_DT_RADIO)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_MENU_YES_SLASH_FIELD_MSG), $this->fieldName));
            return false;
        }

        // Determine which organizations should be presented to the user.

        //standard field
        if(!($this->field instanceof CustomField))
        {
             if(($this->CI->meta['sla_failed_page'] || $this->CI->meta['sla_required_type']) && $this->fieldName === 'sla' && count($this->field->menu_items))
                 $this->data['hideEmptyOption'] = true;
             if($this->field->data_type === EUF_DT_CHECK)
             {
                 $this->data['menuItems'] = array(getMessage(YES_PLEASE_RESPOND_TO_MY_QUESTION_MSG), getMessage(I_DONT_QUESTION_ANSWERED_LBL));
                 $this->data['hideEmptyOption'] = true;
             }
        }
        if($this->field->data_type === EUF_DT_RADIO)
        {
            $this->data['radioLabel'] = array(getMessage(NO_LBL), getMessage(YES_LBL));
            //find the index of the checked value
            if(is_null($this->data['value']))
                $this->data['checkedIndex'] = -1;
            elseif(intval($this->data['value']) === 1)
                $this->data['checkedIndex'] = 1;
            else
                $this->data['checkedIndex'] = 0;
        }
        $this->data['showAriaHint'] = $this->CI->clientLoader->getCanUseAria() && $this->data['js']['hint'];
    }

     /**
     * Private function to remove menu items from a menu custom field
     * @param $removeArray array of menu items to remove
     * @return new array of menu items
     */
    private function _removeMenuItems($removeArray)
    {
        $newArray = array();
        foreach ($this->data['menuItems'] as $menuId => $value)
        {
            if (!in_array($value, $removeArray))
                $newArray[$menuId] = $value;
        }
        return $newArray;
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

        $validAttributes = $this->parseFieldName2($this->data['attrs']['name'], true);
        if(!is_array($validAttributes))
        {
            echo $this->reportError($validAttributes);
            return false;
        }
        $this->table = $validAttributes[0];
        $this->fieldName = $validAttributes[1];

        $this->field = getBusinessObjectField($this->table, $this->fieldName, $isProfileField);

        if ($this->table == "orgcomplainthandler")
        {
            if ($this->fieldName == "authorizedcomplainthandler")
            {
                $this->field->data_type = EUF_DT_SELECT;
                $this->field->readonly = false;
                $this->field->menu_items = $this->CI->redirectedCase_model->complaintHandlersForOrg( $this->organizationID );
            }
        }

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

    function parseFieldName2($name, $input = false)
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

