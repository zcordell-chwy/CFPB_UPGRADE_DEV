<?

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class FormReviewFieldDisplay4 extends Widget
{
    // Put all the attributes in properties for shorthand.
    private $_full_name;
    private $_label;
    private $_label_checkbox;
    private $_name;
    private $_radio_buttons;
    private $_table;
    private $_namespace;
    private $_type;
    private $_value;
    private $_mask_value;
    private $_maskAll_value;
    private $_maskSSNAll_value;
    private $_menu_items;
    private $_full_state_name;
    
    public static $states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AS' => 'American Samoa',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District Of Columbia',
		'FM' => 'Federated States Of Micronesia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'GU' => 'Guam',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MH' => 'Marshall Islands',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'MP' => 'Northern Mariana Islands',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PW' => 'Palau',
		'PA' => 'Pennsylvania',
		'PR' => 'Puerto Rico',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VI' => 'Virgin Islands',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'AE' => 'Armed Forces Africa',
		'AA' => 'Armed Forces Americas',
		'AE' => 'Armed Forces Canada',
		'AE' => 'Armed Forces Europe',
		'AE' => 'Armed Forces Middle East',
		'AP' => 'Armed Forces Pacific',
	);

    public function __construct()
    {
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI();

        parent::__construct();

        $this->attrs['id'] = new Attribute('Record ID', 'INT', 'Record ID', null);
        $this->attrs['label'] = new Attribute(getMessage(FIELD_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_FORM_FIELD_VALUE_LBL), null);
        $this->attrs['name'] = new Attribute('Name', 'STRING', 'Name', null);
        $this->attrs['table'] = new Attribute('Table', 'STRING', 'Table', 'incidents', null);
        $this->attrs['namespace'] = new Attribute('Namespace', 'STRING', 'Namespace', null, null);
        $this->attrs['type'] = new Attribute('Type', 'INT', 'Type', null);
        $this->attrs['type']->options = array(
            EUF_DT_SELECT,
            EUF_DT_CHECK,
            EUF_DT_MEMO,
            EUF_DT_RADIO,
            EUF_DT_VARCHAR
        );
        $this->attrs['value'] = new Attribute('Value', 'STRING', 'Value', null);
        $this->attrs['allow_file_download'] = new Attribute('Allow File Download', 'BOOL', "Indicates if a file list should allow download, or be a static list", false);

        // Type-specific attributes
        $this->attrs['label_checkbox'] = new Attribute('Checkbox Label', 'STRING', 'Checkbox Label', null);
        $this->attrs['only_show_hier_level'] = new Attribute('Show Hierarchy Level X Label (Only)', 'STRING', 'If a hierarchy level number is included here, instead of ' . 'showing both the parent and sub levels separated by colons, this level\'s label will be displayed by itself', false);

        // Mask attributes
        $this->attrs['only_show_final_four'] = new Attribute('Only Show Final Four Characters', 'BOOL', "Boolean indicating if everything but the final four characters " . "should be masked with asterisks.", false);
        $this->attrs['mask_all'] = new Attribute('Mask all characters', 'BOOL', 'Boolean indicating if all characters should be masked', 'false');
        $this->attrs['mask_ssn_all'] = new Attribute('Mask all SSN-formatted characters', 'BOOL', 'Boolean indicating if all SSN-formatted characters should be masked. ' . 'Acceptable formats are: ###-##-####, and #########.', 'false');

        // Menu as radios
        $this->attrs['show_menu_as_radios'] = new Attribute('show_menu_as_radios', 'BOOL', 'Boolean which controls displaying the proper value for a menu field ' . 'displayed as radio buttons', false);
		
		$this->attrs['break_value'] = new Attribute('Break Value', 'BOOL', "We need some values to display through a secure connection in spite of the F5. ".
                "This will be accomplished by inserting a hidden HTML tag midway through the string. Defaults to false.", false);
        $this->attrs['full_state_name'] = new Attribute('Display full state name', 'BOOL', 'If this flag is set to true, then it will try to convert the output to a state name instead of the standard abbreviation', false);
		
        $this->CI->load->helper('debug');  // has the "debug_log" function for easy debug output

    }

    public function generateWidgetInformation()
    {
        $this->info['notes'] = 'Displays fields on review pages.  Automatically updates when fields on the same page change.';
        $this->parms['i_id'] = new UrlParam(getMessage(INCIDENT_ID_LBL), 'i_id', false, getMessage(INCIDENT_ID_DISPLAY_INFORMATION_LBL), 'i_id');
    }

    public function getData()
    {

        // Initialize properties.
        $this->_initializeProperties();

        // Set data for logic.
        $this->data['js']['name'] = $this->_full_name;
        $this->data['js']['type'] = $this->_type;

        // Set data for view.
        $this->data['label'] = $this->_label;
        $this->data['label_checkbox'] = $this->_label_checkbox;
        $this->data['type'] = $this->_type;
        $this->data['value'] = $this->_maskValue();
        $this->data['hidden'] = $this->_isHidden();
        
        //Map the state values to names
        if($this->attrs['full_state_name']->value == true && array_key_exists($this->data['value'], static::$states)){
	        $this->data['value'] = static::$states[$this->data['value']];
        }
        
        // Do we need to inject a hidden HTML element into the string?
        if($this->data['attrs']['break_value'] === true && strlen( $this->data['value'] ) > 0 )
        {
            // We want to hit the middle of the field, but intval will truncate a float if the length is an odd number. Overshoot by one to compensate.
            $chunkLength = intval( strlen( $this->data['value'] ) / 2 ) + 1;
            $valueToken = str_split( $this->data['value'], $chunkLength );
            $this->data['value'] = sprintf( '%s<span class="rn_Hidden"></span>%s', $valueToken[0], $valueToken[1] );
        }
		
        //debug_log($this->data, "DATA at end of getData");
    }

    /*
     * Initialize properties
     */
    private function _initializeProperties()
    {
        $this->CI->load->model('custom/instagent_model');

        $this->_label = $this->data['attrs']['label'];
        $this->_full_name = $this->data['attrs']['name'];
        $this->_name = $this->data['attrs']['name'];
        $this->_table = $this->data['attrs']['table'];
        $this->_namespace = $this->data['attrs']['namespace'];
        $this->_type = $this->data['attrs']['type'];
        $this->_value = $this->data['attrs']['value'];
        $this->_label_checkbox = $this->data['attrs']['label_checkbox'];
        $this->_radio_buttons = json_decode($this->data['attrs']['radio_buttons']);
        $this->_mask_value = $this->data['attrs']['only_show_final_four'];
        $this->_maskAll_value = $this->data['attrs']['mask_all'];
        $this->_maskSSNAll_value = $this->data['attrs']['mask_ssn_all'];
        $this->_menu_items = null;

        // menu as radio
        if ($this->data['attrs']['show_menu_as_radios'])
        {
            $this->_type = EUF_DT_SELECT;
        }

        // Properties for database-tied fields
        if ($this->_table && $this->_name)
        {
            if ($this->_namespace == null)
            {
                $this->_full_name = $this->_table . '.' . $this->_name;
                // debug_log($this->_full_name, "field is NOT a CBO");

                // Field-specific properties
        	$field = $this->CI->instagent_model->getBusinessObjectField($this->_table, $this->_name, getUrlParm('comp_id'));
            }
            else
            {
		// Get complaint against org object
		$cao = $this->CI->instagent_model->getComplaint(getUrlParm('comp_id'));
		if(isset($cao->Incident->ID))
		{
			$i_id = $cao->Incident->ID;
		}
		
                $this->_full_name = $this->_namespace . '.' . $this->_table . '.' . $this->_name;
                // debug_log($this->_full_name, "field IS a CBO");

                // get the CBO object
                $custom_object = null;
                
                // first try using i_id if it exists
                if (!is_null($i_id))
                {
                    // debug_log("calling getCBOByIncidentId($i_id)");
                    $custom_object = $this->getCBOByIncidentId($i_id, $this->_namespace, $this->_table);
                }

                // if no i_id or no object was found, then create a new instance
                if (is_null($custom_object))
                {
                    // debug_log("calling getCBOInstance");
                    $custom_object = $this->getCBOInstance($this->_namespace, $this->_table);
                }
                //debug_log($custom_object, "CUSTOM OBJECT: ");

                // set up various field information, data_type
                // debug_log($this->_name, "calling getCBOField for ");
                $this->getCBOField($custom_object, $this->_name);

                // set up the various data['js'] items
                // debug_log($this->_name, "calling getCBOFieldValues for ");
                $field = $this->getCBOFieldValues($custom_object, $this->_name);

            }
            if (is_null($this->_label))
                $this->_label = $field->lang_name;
            if (is_null($this->_label_checkbox))
                $this->_label_checkbox = $field->lang_name;
            if (is_null($this->_type))
                $this->_type = $field->data_type;

            // Record-specific properties
            if (is_null($this->_value) && getUrlParm('i_id'))
            {
                switch ($this->_type)
                {
                    case EUF_DT_SELECT :
                        $this->_value = $field->menu_items[$field->value];
                        break;
                    // format date fields
                    case EUF_DT_DATE :
                    case EUF_DT_DATETIME :
                        $dateFormat = "m/d/Y";
                        $dateValue = date($dateFormat, $field->value);
                        if (strpos($dateValue,'12/31/1969') === 0)  // may have a time as well
                            $this->_value = null;
                        else
                            $this->_value = $dateValue;
                        break;
                    case EUF_DT_HIERMENU :
                        if ($this->data['attrs']['only_show_hier_level'])
                        {
                            $tmpVal = intval($this->data['attrs']['only_show_hier_level']);
                            $tmpVal = ($tmpVal > 0) ? $tmpVal - 1 : 0;
                            $this->_value = $field->value[$tmpVal]['label'];
                        }
                        else
                        {
                            $this->_value = ($field->value[1]) ? $field->value[0]['label'] . ': ' . $field->value[1]['label'] : $field->value[0]['label'];
                        }
                        break;
                    default :
                        $this->_value = $field->value;
                        break;
                }
            }
        }
    }

    /*
     * Decides whether the field is hidden based on whether it has a pre-populated value.
     *
     * @return bool Whether the field is hidden.
     */
    private function _isHidden()
    {
        $ret = false;

        if (is_null($this->_value))
            $ret = true;

        switch ($this->_type)
        {
            case EUF_DT_CHECK :
                if (!$this->_value)
                    $ret = true;
                break;
            case EUF_DT_MEMO :
            case EUF_DT_SELECT :
            case EUF_DT_VARCHAR :
                if ($this->_value === '')
                    $ret = true;
            case EUF_DT_FATTACH :
                if (empty($this->_value))
                    $ret = true;
        }

        return $ret;
    }

    /**
     * Helper function that masks value when requested.
     *
     * @return  string  The correct field value to display to users.
     */
    private function _maskValue()
    {
        if ($this->_mask_value === true)
        {
            $substrToMask = substr($this->_value, 0, -4);
            $maskedSubstr = str_repeat('*', strlen($substrToMask));
            $this->_value = str_replace($substrToMask, $maskedSubstr, $this->_value);
        }
        if ($this->_maskAll_value === true)
        {
            $this->_value = str_replace($this->_value, str_repeat('*', strlen($this->_value)), $this->_value);
        }
        if ($this->_maskSSNAll_value === true)
        {
            if (preg_match('/^\d{9}$/', $this->_value) || preg_match('/^\d{3}-\d{2}-\d{4}$/', $this->_value))
            {
                $this->_value = str_replace($this->_value, str_repeat('*', strlen($this->_value)), $this->_value);
            }
        }

        return $this->_value;
    }

    private function getCBOField($cbo, $fieldName)
    {
        //debug_log($cbo, "CBO in getCBOField");
        //debug_log($fieldName, "fieldName in getCBOField");

        $field_type_map = array(
            "String" => EUF_DT_VARCHAR,
            "Boolean" => EUF_DT_SELECT,  // so it is displayed as Yes or No rather than radio buttons
            "Date" => EUF_DT_DATE,
            "DateTime" => EUF_DT_DATETIME,
            "Integer" => EUF_DT_INT
        );

        $md = $cbo::getMetadata();
        // debug_log($md->$fieldName, "METADATA for $fieldName");
        
        if (strpos($md->$fieldName->type_name, 'RightNow\\Connect\\') === 0)
        {            
            // Apparently, SELECT may contain a reference to the menu CBO object.
            // ie:  [type_name] => RightNow\Connect\v1_2\PSLog\Severity
            // I can't figure out a better way to handle this, so for now:
            $this->_type = EUF_DT_SELECT;
            $this->_menu_items = new $md->$fieldName->type_name;
        }
        else if (isset($field_type_map[$md->$fieldName->COM_type]))
        {
            $this->_type = $field_type_map[$md->$fieldName->COM_type];
        }
    }

    private function getCBOFieldValues($cbo, $fieldName)
    {
        //debug_log($cbo->$fieldName, "Value of CBO field ($fieldName) in getCBOFieldValues: ");
        
        $md = $cbo::getMetadata();
        // debug_log($md->$fieldName, "METADATA for $fieldName");
        
        $this->data['readOnly'] = $md->$fieldName->is_read_only_for_update;

        // set up other important data
        $this->data['attrs']['is_cbo'] = true;
        $this->data['attrs']['cbo_id'] = $cbo->ID;
        $this->data['attrs']['type'] = $this->_type;
        $this->data['attrs']['namespace'] = $this->_namespace;
        $this->data['attrs']['table'] = $this->_table;
        $this->data['attrs']['name'] = $fieldName;
        $this->data['attrs']['COM_type'] = $md->$fieldName->COM_type;

        $value = null;
        
        switch ($this->_type)
        {
            case EUF_DT_SELECT:
                if ($md->$fieldName->COM_type == "Boolean")  
                {
                    if (!is_null($cbo->$fieldName))
                    {
                        if ($cbo->$fieldName == 1)
                            $value = "Yes";
                        else if ($cbo->$fieldName == 0)
                            $value = "No";
                    }
                    else 
                    {
                        $value = null;
                    }
                }
                else 
                    $value = $cbo->$fieldName->LookupName;
                // debug_log($md->$fieldName->COM_type, "ERROR ($fieldName) : Unknown COM type for EUF_DT_SELECT _type");
                break;
          
            case EUF_DT_DATE:
            case EUF_DT_DATETIME:
                 $dateValue = date('m/d/Y', $cbo->$fieldName);
                 if (strpos($dateValue,'12/31/1969') === 0)  // may have a time as well
                     $value = null;
                 else
                     $value = $dateValue;
                break;
                
            default:
                $value = $cbo->$fieldName;    
                break;
        }

        // set the actual value
        $this->data['attrs']['value'] = $value;
        $this->_value = $value;
        
        $this->setConstraintValues($md->$fieldName->constraints, $this->data);                
        $field = (object) $this->data['attrs'];
 
        return $field;            
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

        $cbo_full_name = CO_PATH . $namespace . "\\" . $cbo_name;
        $cbo = new $cbo_full_name;

        return $cbo;
    }

    /**
     * Used to retrieve a custom business object by incident id.
     *
     * @param int $i_id incident id
     */
    private function getCBOByIncidentId($i_id, $namespace, $cbo_name)
    {

        if (is_null($i_id) || !is_numeric($i_id) || $i_id < 1)
        {
            return false;
        }
	$cbo_path = 'RightNow\Connect\v1_2\\' . $namespace . "\\" . $cbo_name;
        try
        {
            $inc = RightNow\Connect\v1_2\Incident::fetch($i_id);
                $cbo = $inc->CustomFields->$namespace->$cbo_name;
        }
        catch (Exception $e)
        {
                debug_log("EXCEPTION retrieving CBO by i_id ($i_id) " . $e->getMessage());
                return false;
        }
        //debug_log($cbo, "RETURNING CBO: ");
	
        return $cbo;
    }

    private function setConstraintValues($constraints, &$cbo_data)
    {
        foreach ($constraints as $constraint)
        {
            $value = $constraint->value;
            switch ($constraint->kind)
            {
                case (RightNow\Connect\v1_2\Constraint::Min) :
                    $cbo_data['js']['minVal'] = $value;
                    break;
                case (RightNow\Connect\v1_2\Constraint::Max) :
                    $cbo_data['js']['maxVal'] = $value;
                    break;
                case (RightNow\Connect\v1_2\Constraint::MinLength) :
                    $cbo_data['js']['minLength'] = $value;
                    break;
                case (RightNow\Connect\v1_2\Constraint::MaxLength) :
                    $cbo_data['js']['maxLength'] = $value;
                    break;
                case (RightNow\Connect\v1_2\Constraint::MaxBytes) :
                    $cbo_data['js']['fieldSize'] = $value;
                    break;
                case (RightNow\Connect\v1_2\Constraint::In) :
                    //$cbo_data['js'][''] = $value;
                    break;
                case (RightNow\Connect\v1_2\Constraint::Not) :
                    //$cbo_data['js'][''] = $value;
                    break;
                case (RightNow\Connect\v1_2\Constraint::Pattern) :
                    $cbo_data['js']['mask'] = $value;
                    break;
                default :
                    $cbo_data['kind_not_found'][$constraint->kind]++;
                    break;
            }
        }
    }

}
