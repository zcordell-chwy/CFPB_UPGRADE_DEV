<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('FormInput'))
    requireWidgetController('standard/input/FormInput');

class TextLogicInput extends FormInput
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
        $this->attrs['is_review'] = new Attribute("Is Review Flag", 'BOOL', "The flag used to determine if widget is used in review section", false);
        $this->attrs['is_other_checkbox'] = new Attribute("Is Other Flag", 'BOOL', 
            "The flag used to determine when 'other' checkbox is checked to display text input", false);
        $this->attrs['is_anon'] = new Attribute("Enable Anonymous Email", 'BOOL', "This flag enables the user to submit anonymously", false);
        $this->attrs['label_input_sub'] = new Attribute("Label subtext", 'STRING', "Provides additional detail under label", false);
        $this->attrs['label_before_input'] = new Attribute("Label before input tag", 'STRING', "Label before input of type text tag", false);
    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] =  getMessage(WDGT_ALLWS_USRS_SET_FLD_VALS_DB_MSG);
    }

    function getData()
    {
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
