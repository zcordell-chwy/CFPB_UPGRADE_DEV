<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('FormInput2'))
    requireWidgetController('custom/input/FormInput2');

class TextLogicInput3 extends FormInput2
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['always_show_mask'] = new Attribute(getMessage(ALWAYS_SHOW_MASK_LBL), 'BOOL', getMessage(SET_TRUE_FLD_MASK_VAL_EXPECTED_MSG), false);
        $this->attrs['max_chars'] = new Attribute("Max Characters", 'INT', "If set, this will be the max number of characters allowed", 0);
        $this->attrs['max_chars_label'] = new Attribute("Max Characters Label", 'STRING', "The label associated with max_chars attribute", "characters remaining");
        $this->attrs['style_custom'] = new Attribute("Custom CSS Style", 'STRING', "Custom CSS to move the field", null);
        $this->attrs['error_msg'] = new Attribute("Custom Error Message", 'STRING', "Display custom error message", null);
        $this->attrs['error_validation_label'] = new Attribute("Custom Error Validation Label", 'STRING', "Display label information for validation/masking errors", null);
        $this->attrs['is_copy'] = new Attribute("Is Copy Flag", 'BOOL', "The flag used keep a copy of the value in case we need to reset to it", false);
        $this->attrs['is_review'] = new Attribute("Is Review Flag", 'BOOL', "The flag used to determine if widget is used in review section", false);
        $this->attrs['is_other_checkbox'] = new Attribute("Is Other Flag", 'BOOL',
            "The flag used to determine when 'other' checkbox is checked to display text input", false);
        $this->attrs['is_anon'] = new Attribute("Enable Anonymous Email", 'BOOL', "This flag enables the user to submit anonymously", false);
        $this->attrs['optional_on_hide'] = new Attribute('Flag to indicate if the widget should become an optional widget when it is hidden', 'BOOL',
            'make element optional when hidden', false);
        $this->attrs['clear_value_on_hide'] = new Attribute('Clear Value on Hide', 'BOOL', 'Flag indicating if the value of the field should be cleared out if object is hidden.', false);
        $this->attrs['enable_cclookup'] = new Attribute('Enable cclookup', 'BOOL', 'Enable ccLookup for credit card form', false);
        $this->attrs['enable_autoroutelookup'] = new Attribute('Enable autoroutelookup', 'BOOL', 'Enable dynamic behavior for autorouting of orgs', false);
        $this->attrs['regex_mask'] = new Attribute('Regular Expression Mask', 'STRING', "Regular expression used to validate form field value entered by user. ".
	        "Defaults to empty string, which indicates pattern matching will not be attempted. Pattern matching applied in widget's javascript.", '' );
        $this->attrs['max_age'] = new Attribute('Maximum Age', 'INT', "Maximum age a date can be. Defaults to zero, which will do no validation of known date ".
		    "fields (incidents.c\$date_birth).", 0);
	    $this -> attrs['required_when_parent_equals'] = new Attribute("Optional when parent equals", "STRING", "Fields rendered via the TextLogicInput3 widget can be made optional when ".
		    "another field is not equal a specific value. This attribute sets the table.field which determines if this field is required. Works in conjunction with the ".
		    "'required_when_field_equals' field.", null);
	    $this -> attrs['required_when_field_equals'] = new Attribute("Optional when field equals", "STRING", "Fields rendered via the TextLogicInput3 widget can be made optional when ".
		    "another field is not equal a specific value. This attribute sets the value which determines if this field is required. Works in conjunction with the ".
		    "'required_when_parent_equals' field.", null);
        $this->attrs['section_id'] = new Attribute('Section ID', 'INT', 'Set the section id (step #) of the page for the IntakeNavigation and NavigationProcess widgets', null);
        $this->attrs['validate_on_blur'] = new Attribute("validate_on_blur", 'BOOL', "The flag to validate at field level", false);

    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] =  getMessage(WDGT_ALLWS_USRS_SET_FLD_VALS_DB_MSG).
		str_repeat('&nbsp;', 10).str_repeat('!', 10).
		' This widget is a copy of the TextLogicInput2 widget. It has been modified to include parameters (required_when_parent_equals, required_when_field_equals) to allow '.
		'controlling whether the field is required based on the value of another field'.
		' It has also been modified to support CBO fields. '.
		str_repeat('!', 10);
    }

    function getData()
    {
    	$this -> data['js']['optional_on_hide'] = $this -> data['attrs']['optional_on_hide'];
        if(parent::retrieveAndInitializeData() === false)
            return false;

        if($this->field->data_type !== EUF_DT_PASSWORD && $this->field->data_type !== EUF_DT_THREAD && $this->field->data_type !== EUF_DT_MEMO &&
           $this->field->data_type !== EUF_DT_VARCHAR && $this->field->data_type !== EUF_DT_INT)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_TXT_INT_PASSWD_THREAD_MSG), $this->fieldName));
            return false;
        }

        if($this->data['js']['mask'] && $this->data['value'])
            $this->data['value'] = $this->_addMask($this->data['value'], $this->data['js']['mask']);

        //Standard Field
        if(!($this->field instanceof CustomField))
        {
            if($this->field->data_type === EUF_DT_PASSWORD)
            {
                //honor config: don't output password fields
                if(!getConfig(EU_CUST_PASSWD_ENABLED)) return false;

                $this->data['value'] = '';
                //Get password length, but make sure value is at most 20 since that's the highest we can support
                $this->data['js']['passwordLength'] = min(getConfig(MYSEC_MIN_PASSWD_LEN), 20);
                if($this->data['js']['passwordLength'] > 0 && !in_array($this->fieldName, array('password', 'organization_password'), true))
                    $this->data['attrs']['required'] = true;
            }
            //Error if using alt first/last name fields when not on Japanese site
            if(($this->fieldName === 'alt_first_name' || $this->fieldName === 'alt_last_name') && LANG_DIR !== 'ja_JP')
            {
                echo $this->reportError(getMessage(ALT_FIRST_NAME_ALT_LAST_NAME_FLDS_MSG));
                return false;
            }
            //Prepopulate email address field if it is not set and it has been entered on a previous feedback
            if($this->fieldName === 'email' && !$this->field->value && $this->CI->session->getSessionData('previouslySeenEmail'))
                $this->data['value'] = $this->CI->session->getSessionData('previouslySeenEmail');
        }
        $this->data['js']['contactToken'] = createToken(1);
    }

     /**
     * Creates and returns a mask string based upon the field's
     * value.
     * @param $value String the field's initial value
     * @param $mask String the Mask value
     * @return string the field's initial mask value
     */
     private static function _addMask($value, $mask)
     {
         $j = 0;
         $result = '';
         for($i = 0; $i < strlen($mask); $i+=2)
         {
             while($mask[$i] === 'F')
             {
                 $result .= $mask[$i + 1];
                 $i+=2;
             }
             $result .= $value[$j];
             $j++;
         }
         return $result;
     }
}
