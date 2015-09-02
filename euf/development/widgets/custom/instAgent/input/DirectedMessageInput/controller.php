<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('FormDirectedMessageInput'))
    requireWidgetController('custom/instAgent/input/FormDirectedMessageInput');

class DirectedMessageInput extends FormDirectedMessageInput
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['always_show_mask'] = new Attribute(getMessage(ALWAYS_SHOW_MASK_LBL), 'BOOL', getMessage(SET_TRUE_FLD_MASK_VAL_EXPECTED_MSG), false);
        $this->attrs['error_msg'] = new Attribute("Custom Error Message", 'STRING', "Display custom error message", null);
        $this->attrs['error_validation_label'] = new Attribute("Custom Error Validation Label", 'STRING', "Display label information for validation/masking errors", null);
        $this->attrs['max_chars'] = new Attribute("Max Characters", 'INT', "If set, this will be the max number of characters allowed", 0);
        $this->attrs['max_chars_label'] = new Attribute("Max Characters Label", 'STRING', "The label associated with max_chars attribute", "characters remaining");
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
        
        $this->data['js']['contactToken'] = createToken(1);
    }

}
