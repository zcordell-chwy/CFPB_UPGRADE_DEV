<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

use RightNow\Connect\v1_2 as RNCPHP;

class FormInput2 extends Widget
{
    protected $field;
    protected $table;
    protected $namespace;
    protected $fieldName;
    protected $obj;

    function __construct()
    {
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI();
        parent::__construct();
        
        $this->attrs['label_input'] = new Attribute(getMessage(INPUT_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_INPUT_CONTROL_LBL), '{default_label}');
        $this->attrs['label_required'] = new Attribute(getMessage(REQUIRED_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_REQUIREMENT_MESSAGE_LBL), getMessage(PCT_S_IS_REQUIRED_MSG));
        $this->attrs['label_optional'] = new Attribute("label_optional", 'STRING', "Label to display when a value is not required", "");
        $this->attrs['name'] = new Attribute(getMessage(NAME_LBL), 'STRING', getMessage(COMBINATION_TB_FLD_INPUT_ATTRIB_MSG), '');
        $this->attrs['required'] = new Attribute(getMessage(REQUIRED_LBL), 'BOOL', getMessage(SET_TRUE_FLD_CONT_VAL_CF_SET_REQD_MSG), false);
        $this->attrs['hint'] = new Attribute(getMessage(HINT_LBL), 'STRING', getMessage(HINT_TXT_DISP_FLD_CF_VAL_OVRRIDE_MSG), '');
        $this->attrs['always_show_hint'] = new Attribute(getMessage(ALWAYS_SHOW_HINT_LBL), 'BOOL', getMessage(SET_TRUE_FLD_HINT_HINT_DISPLAYED_MSG), false);
        $this->attrs['initial_focus'] = new Attribute(getMessage(INITIAL_FOCUS_LBL), 'BOOL', getMessage(SET_TRUE_FIELD_FOCUSED_PAGE_LOADED_MSG), false);
        $this->attrs['validate_on_blur'] = new Attribute(getMessage(VALIDATE_ON_BLUR_LBL), 'BOOL', getMessage(VALIDATES_INPUT_FLD_DATA_REQS_FOCUS_LBL), false);
        $this->attrs['always_show_mask'] = new Attribute(getMessage(ALWAYS_SHOW_MASK_LBL), 'BOOL', getMessage(SET_TRUE_FLD_MASK_VAL_EXPECTED_MSG), false);
        $this->attrs['default_value'] = new Attribute(getMessage(DEFAULT_VALUE_LBL), 'STRING', getMessage(DEF_VAL_POPULATE_FLD_CF_VAL_OVRRIDE_MSG), '');
        $this->attrs['allow_external_login_updates'] = new Attribute(getMessage(ALLOW_EXTERNAL_LOGIN_UPDATES_LBL), 'BOOL', getMessage(ALLWS_USERS_AUTHENTICATE_CP_EXT_MSG), false);
        $this->attrs['widget_group'] = new Attribute('widget_group', 'STRING', 'Only a single widget within a group will be required, if not set this attribute does nothing', '');
    
        $this->CI->load->helper('debug'); // has the "debug_log" function for easy debug output
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = sprintf(getMessage(WDGT_ALLWS_USRS_ST_FLD_VLS_DB_MSG), 'name', 'name');
        $this->parms['i_id'] = new UrlParam(getMessage(INCIDENT_ID_LBL), 'i_id', false, getMessage(INCIDENT_ID_DISPLAY_INFORMATION_LBL), 'i_id/7');
    }

    function getData()
    {
        if ($this->retrieveAndInitializeData() === false)
            return false;
        if ($this->field->data_type === EUF_DT_HIERMENU)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_FLD_TYPE_PROD_CAT_PLS_INPUT_S_MSG), $this->fieldName));
            return false;
        }
        if ($this->field->data_type === EUF_DT_FATTACH)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_FLD_TYPE_FILE_ATTACH_PLS_MSG), $this->fieldName));
            return false;
        }
    }

    protected function retrieveAndInitializeData()
    {

        // see if it's cached
        $cacheKey = 'Input_' . $this->data['attrs']['name'];
        $cacheResults = checkCache($cacheKey);
        if (is_array($cacheResults))
        {
            list($this->field, $this->namespace, $this->table, $this->fieldName, $this->data) = $cacheResults;
            $this->field = unserialize($this->field);
            return;
        }

        // see if this field references a standard table or a CBO
        $standard_field = $this->isStandardTableName($this->data['attrs']['name']);

        // parse the field name (incidents.xyz or NAMESPACE.CustomObjectName.fieldName)
        // NOTE: CBO fields are case sensitive -- must use the right case in the widget or else there will be errors
        if ($standard_field) 
        {
            $this->data['attrs']['name'] = strtolower($this->data['attrs']['name']);
            $validAttributes = parseFieldName($this->data['attrs']['name'], true);
        }
        else
            $validAttributes = $this->parseCBOFieldName($this->data['attrs']['name']);

        if (!is_array($validAttributes))
        {
            echo $this->reportError($validAttributes);
            return false;
        }

        // now get the the field and retrieve the values for that field
        if ($standard_field)
        {
            $this->table = $validAttributes[0];
            $this->fieldName = $validAttributes[1];

            $this->field = getBusinessObjectField($this->table, $this->fieldName, $isProfileField);
            $success = $this->getStandardFieldValues();
            if (!$success)
            {
                return false;
            }
        }
        else
        {
            // this is a CBO field, so handle accordingly
            
            // get the different parts of the name
            $this->namespace = $validAttributes[0];
            $this->table = $validAttributes[1];
            $this->fieldName = $validAttributes[2];

            // get the CBO object
            //debug_log("calling getCBOInstance");
            $custom_object = $this->getCBOInstance($this->namespace, $this->table);

            // set up various field information, data_type
            //debug_log("calling getCBOField");
            $this->getCBOField($custom_object, $this->fieldName);
   
            // set up the various data['js'] items
            //debug_log("calling getCBOFieldValues");
            $this->getCBOFieldValues($custom_object, $this->fieldName);
        }
        
        // update the cache
        setCache($cacheKey, array( serialize($this->field), $this->namespace, $this->table, $this->fieldName, $this->data ));

    }

    private function getStandardFieldValues()
    {
        if (is_string($this->field))
        {
            echo $this->reportError($this->field);
            return false;
        }

        //Not a visible custom field
        if (is_null($this->field))
            return false;

        //Common between both standard + custom fields
        $this->data['js']['type'] = $this->field->data_type;
        $this->data['js']['table'] = $this->table;
        $this->data['js']['name'] = $this->fieldName;
        if ($isProfileField === true)
            $this->data['js']['profile'] = true;
        $this->data['readOnly'] = $this->field->readonly;
        if ($this->field->readonly)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_READ_FIELD_INPUT_WIDGET_MSG), $this->fieldName));
            return false;
        }
        $this->data['js']['mask'] = $this->field->mask;
        if ($this->field->menu_items)
            $this->data['menuItems'] = $this->field->menu_items;
        if (!is_null($this->field->max_val))
            $this->data['js']['maxVal'] = $this->field->max_val;
        if (!is_null($this->field->min_val))
            $this->data['js']['minVal'] = $this->field->min_val;
        if ($this->data['attrs']['label_input'] === '{default_label}')
            $this->data['attrs']['label_input'] = $this->field->lang_name;
        if ($this->field->field_size)
        {
            $this->data['maxLength'] = $this->field->field_size;
            //allow for -/+ sign on ints
            if ($this->field->data_type === EUF_DT_INT)
                $this->data['maxLength']++;
            else
            if ($this->field->data_type === EUF_DT_MEMO)
                $this->data['js']['fieldSize'] = $this->field->field_size;
        }

        //custom field specific
        if ($this->field instanceof CustomField)
        {
            //If not Live Chat, don't show non-editable fields
            if (((($this->field->visibility & VIS_LIVE_CHAT) == false) && $this->CI->page === getConfig(CP_CHAT_URL)) || ((($this->field->visibility & VIS_ENDUSER_EDIT_RW) == false) && (!($this->CI->page === getConfig(CP_CHAT_URL)))))
            {
                echo $this->reportError(sprintf(getMessage(PCT_S_READ_FIELD_INPUT_WIDGET_MSG), $this->fieldName));
                return false;
            }
            $this->data['js']['name'] = preg_replace('(^c\$)', '', $this->fieldName);
            $this->data['js']['customID'] = $this->field->custom_field_id;
            if ($this->field->lang_hint && strlen(trim($this->field->lang_hint)))
                $this->data['js']['hint'] = $this->field->lang_hint;
            $this->data['attrs']['required'] = ($this->field->required === 1) ? true : $this->data['attrs']['required'];
            if (($this->field->data_type === EUF_DT_VARCHAR) && ($this->field->attr & CFATTR_URL))
                $this->data['js']['url'] = true;
            if (($this->field->data_type === EUF_DT_VARCHAR) && ($this->field->attr & CFATTR_EMAIL))
                $this->data['js']['email'] = true;
        }
        else
        if ($this->field instanceof ChannelField)
        {
            $this->data['js']['name'] = str_replace('$', '', $this->fieldName);
            $this->data['js']['channelID'] = $this->field->id;
        }
        //standard field w/read-only visibility
        elseif ($this->field->readonly)
        {
            return false;
        }

        if ($this->data['attrs']['hint'] && strlen(trim($this->data['attrs']['hint'])))
        {
            $this->data['js']['hint'] = $this->data['attrs']['hint'];
        }

        $this->data['value'] = $this->setFieldValue();
        if ($this->field->data_type !== EUF_DT_PASSWORD)
        {
            $this->data['js']['prev'] = $this->data['value'];
        }
        //Override visibility of doing a contact field and pta or chat
        if ($this->table === 'contacts' && isLoggedIn())
        {
            if ($this->CI->page === getConfig(CP_CHAT_URL) || (getConfig(PTA_ENABLED) && $this->fieldName === 'login') || (isPta() && (!$this->data['attrs']['allow_external_login_updates'] || $this->fieldName === 'login')))
            {
                $this->data['readOnly'] = true;
            }
        }

        return true;

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

        if ($valueSpecifiedInPost !== false && $valueSpecifiedInPost !== '')
            $dynamicDefaultValue = str_replace("'", '&#039;', str_replace('"', '&quot;', $valueSpecifiedInPost));
        else
        if ($valueSpecifiedInUrl !== null && $valueSpecifiedInUrl !== '')
            $dynamicDefaultValue = $valueSpecifiedInUrl;
        else
        if ($this->data['attrs']['default_value'] !== '')
            $dynamicDefaultValue = $this->data['attrs']['default_value'];

        //If this is a custom field, it has a default value that's the same (in a
        // string comparison) as what it's current value is
        //and a value is specified in the URL or through an attribute, overwrite
        // the value
        if ($this->field instanceof CustomField && $this->field->default_value !== null && ((string)$this->field->value === (string)$this->field->default_value) && $dynamicDefaultValue !== '')
        {
            $this->field->value = null;
        }

        if ($this->field->value !== null && !is_array($this->field->value))
        {
            $fieldValue = htmlspecialchars($this->field->value, ENT_QUOTES, 'UTF-8', false);
        }
        else
        if ($dynamicDefaultValue !== '')
        {
            $fieldValue = $dynamicDefaultValue;
        }
        return $fieldValue;
    }

    private function getCBOField($cbo, $fieldName)
    {
        try
        {
            $field_type_map = array(
                "String" => EUF_DT_VARCHAR,
                "Boolean" => EUF_DT_RADIO,
                "Date" => EUF_DT_DATE,
                "DateTime" => EUF_DT_DATETIME,
                "Integer" => EUF_DT_INT,
                "NamedIDLabel" => EUF_DT_SELECT
            );
            
            $md = $cbo::getMetadata();
    
            if (isset($field_type_map[$md->$fieldName->COM_type]))
            {
                $this->field->data_type = $field_type_map[$md->$fieldName->COM_type];
                //debug_log($this->field->data_type, "field type is known");
            }
            else if (strpos($md->$fieldName->type_name, 'RightNow\\Connect\\') === 0)
            {
                // Apparently, SELECT may contain a reference to the menu CBO object.
                // ie:  [type_name] => RightNow\Connect\v1_2\PSLog\Severity
                // I can't figure out a better way to handle this, so for now:
                $this->field->data_type = EUF_DT_SELECT;
                $this->field->menu_items = new $md->$fieldName->type_name;
            }
            else
            {
                debug_log("unknown field type: {$md->$fieldName->COM_type}");
            }
           

            //debug_log($this->field, "this field ($fieldName)");
        }
        catch (Exception $e)
        {
            debug_log("EXCEPTION! " . $e->getMessage());
        }
    }

    private function getCBOFieldValues($cbo, $fieldName)
    {
        try 
        {
            $md = $cbo::getMetadata();
            $this->data['readOnly'] = $md->$fieldName->is_read_only_for_update;
    
             // set up other important data
            $this->data['js']['is_cbo'] = true;
            $this->data['js']['cbo_id'] = $cbo->ID;
            $this->data['js']['type'] = $this->field->data_type;
            $this->data['js']['namespace'] = $this->namespace;
            $this->data['js']['table'] = $this->table;
            $this->data['js']['name'] = $fieldName;
            // debug_log($this->data, "in getCBOFieldValues -- DATA: ");
            // need special handling for menu type fields
            if ($this->field->data_type == EUF_DT_SELECT)
            {
                if ($md->$fieldName->COM_type != 'NamedIDLabel')
                {
                    $menu_items = RNCPHP\ConnectAPI::getNamedValues($md->$fieldName->type_name);        
                    foreach ($menu_items as $menu_item)
                    {
                        $this->data['menuItems'][$menu_item->ID] = $menu_item->LookupName;
                    }
                }
                else 
                {
                    foreach ($md->$fieldName->named_values as $index => $nvp) 
                    {
                        $this->data['menuItems'][$nvp->ID] = $nvp->LookupName;
                    }
                }
            }
    
            $this->setConstraintValues($md->$fieldName->constraints, $this->data);
    
            // set the actual value
            $this->data['value'] = $this->setFieldValue();
        }
        catch (Exception $e)
        {
            debug_log("EXCEPTION! " . $e->getMessage());
        }
    }

    /**
     * Parse and validate a CBO display field name attribute.
     * @return Mixed String error message if attribute is invalid, or an array of
     * the table and field parsed values
     * @param String $name The field name attribute in the form
     * NAME_SPACE.CBO.field
     */
    private function parseCBOFieldName($name)
    {
        if (!$name)
            return sprintf(getMessage(PCT_S_ATTRIB_IS_REQUIRED_MSG), 'name');
        $nameParts = explode('.', $name);
        if (count($nameParts) !== 3)
            return sprintf(getMessage(FND_INV_VAL_NAME_ATTRIB_VAL_MSG), $name);

        if ($nameParts[2] === '')
            return getMessage(FND_EMPTY_VAL_FLD_NAME_NAME_ATTRIB_MSG);
        return $nameParts;
    }

    /**
     * Returns an instance of a custom business object or null if one
     * cannot be returned
     * @return Object $cbo The newly instantiated custom object
     * @param String $namespace The namespace that contains the CBO
     * @param String $cbo_name The CBO type to return
     *
     */
    private function getCBOInstance($namespace, $cbo_name)
    {
        // TODO: FOR NOW, just create a new CBO - don't need update capability yet
        if (!defined('CO_PATH'))
        {
            define('CO_PATH', 'RightNow\Connect\v1_2\\');
        }

        // Assumes namespace has correct case
        $cbo_full_name = CO_PATH . $namespace . "\\" . $cbo_name;
        $cbo = new $cbo_full_name;

        return $cbo;
    }

    private function setConstraintValues($constraints, &$cbo_data)
    {
        foreach ($constraints as $constraint)
        {
            $value = $constraint->value;
            switch ($constraint->kind)
            {
                case (RNCPHP\Constraint::Min) :
                    $cbo_data['js']['minVal'] = $value;
                    break;
                case (RNCPHP\Constraint::Max) :
                    $cbo_data['js']['maxVal'] = $value;
                    break;
                case (RNCPHP\Constraint::MinLength) :
                    $cbo_data['js']['minLength'] = $value;
                    break;
                case (RNCPHP\Constraint::MaxLength) :
                    $cbo_data['js']['maxLength'] = $value;
                    break;
                case (RNCPHP\Constraint::MaxBytes) :
                    $cbo_data['js']['fieldSize'] = $value;
                    break;
                case (RNCPHP\Constraint::In) :
                    //$cbo_data['js'][''] = $value;
                    break;
                case (RNCPHP\Constraint::Not) :
                    //$cbo_data['js'][''] = $value;
                    break;
                case (RNCPHP\Constraint::Pattern) :
                    $cbo_data['js']['mask'] = $value;
                    break;
                default :
                    $cbo_data['kind_not_found'][$constraint->kind]++;
                    break;
            }
        }
    }

    private function isStandardTableName($name)
    {
        return (strpos($name, 'contacts') === 0 || strpos($name, 'incidents') === 0 || strpos($name, 'answers') === 0);
    }
}
