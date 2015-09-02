<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
 
if(!class_exists('DataDisplayIA'))
    requireWidgetController('custom/instAgent/output/DataDisplayIA');

class FieldDisplayIA extends DataDisplayIA
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['dt_fields_to_convert'] = new Attribute('DateTime Fields to Convert', 'STRING', 'A comma-delimited string of field names to consider when it parses data', '');
    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] =  getMessage(DISP_VAL_DATA_ELEMENT_NAME_ATTRIB_MSG);
    }

    function getData()
    {
        if(parent::retrieveAndInitializeData() === false)
            return false;
		if($this->data['attrs']['name'] == 'incidents.c$related_case'){
			if(strlen($this->data['value']))
				$this->data['value'] = 'Yes';
			else
				$this->data['value'] = 'No';
		}
		

        //Validate data type.
        switch($this->field->data_type)
        {
            case EUF_DT_THREAD:
            case EUF_DT_HIERMENU:
            case EUF_DT_FATTACH:
                echo $this->reportError(getMessage(FIELDDISPLAY_DISP_DATA_TYPES_FILE_LBL));
                return false;
        }
        if(is_null($this->data['value']))
            return false;

        if($this->field->data_type === EUF_DT_VARCHAR)
        {
            if(is_array($this->data['value']))
                $this->data['value'] = joinOmittingBlanks(', ', $this->data['value']);
            if(!is_null($this->field->mask) && $this->field->mask !== '')
                $this->data['value'] = put_format_chars($this->data['value'], $this->field->mask);
        }

        if($this->field-data_type === EUF_DT_DATE || $this->field-data_type === EUF_DT_DATETIME) {
            if (strpos($this->data['value'], '12/31/1969') === 0)
                return false;

            $fieldsToFormat = explode(",", $this->data['attrs']['dt_fields_to_convert']);

            if(in_array($this->data['attrs']['name'], $fieldsToFormat)){
                // This is no longer in the DST requirements:
                $timeVal = strtotime($this->data['value']); // - 86400; //Need to subtract seconds as the dates in these fields need to display as if they are due in 15 days.
                $this->data['value'] = date('m/d/Y', $timeVal);
            }
        }

        // Set up label-value justification
        $this->data['wrapClass'] = ($this->data['attrs']['left_justify']) ? ' rn_LeftJustify' : '';

        // Special case for getting around the F5 data-masking
        if($this->data['attrs']['name'] == 'incidents.c$ssn')
        {
            $this->data['value'] = str_replace('-', '&#45;', $this->data['value']);
        }
    }
}
