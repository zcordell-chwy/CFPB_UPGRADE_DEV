<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('FormInput2'))
    requireWidgetController('custom/input/FormInput2');

class DateLogicInput3 extends FormInput2
{
    function __construct()
    {
        parent::__construct();
        //this FormInput attr doesn't apply to DateInput
        $this->attrs['maxdate'] = new Attribute("maxdate", 'BOOL', "Toggle maximum date of today vs non maximum date param for the YUI Calendar widget", false);
        $this->attrs['style_custom'] = new Attribute("Custom CSS Style", 'STRING', "Custom CSS to move the field", null);
        $this->attrs['hide_hours_mins'] = new Attribute('hide_hours_mins', 'BOOL', 'Set to true to enable hiding of the hour/minutes fields when using a '.
		'date/time field', false);
	$this->attrs['validate_against_target_field'] = new Attribute('validate_against_target_field', 'STRING', "The field name to compare this field's value ".
            'against. Format: <table_name>.<field_name>. Used in combination with "validate_against_target_field_criteria".', null);
        $this->attrs['validate_against_target_field_criteria'] = new Attribute('validate_against_target_field_criteria', 'STRING', 'The criteria used for '.
            'comparison between this field and the target field specified in "validate_against_target_field_criteria".', null);
	$this->attrs['validate_against_target_field_error'] = new Attribute('validate_against_target_field_error', 'STRING', 'The error message to display '.
	    'when the validation fails.', null);
	
        unset($this->attrs['always_show_mask']);
    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] = getMessage(WDGT_ALLOWS_USERS_SET_FLD_VALS_DB_MSG).
		str_repeat('&nbsp;', 10).str_repeat('!', 10).
		' This widget is a copy of the DateLogicInput2 widget. It has been modified to hide the hours & minutes fields when a '.
		'date/time field is displayed. It will also defualt the value of the hour/minutes fields to 0 once a date has been selected via the datepicker UI. '.
		' It has also been modified to work with CBO fields.' .
		str_repeat('!', 10);
    }

    function getData()
    {
        if(parent::retrieveAndInitializeData() === false)
            return false;

        if($this->field->data_type !== EUF_DT_DATE && $this->field->data_type !== EUF_DT_DATETIME)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_DATE_DATE_SLASH_TIME_FIELD_MSG), $this->fieldName));
            return false;
        }

        $this->data['maxYear'] = getConfig(EU_MAX_YEAR, 'COMMON');
        $dateOrder = getConfig(DTF_INPUT_DATE_ORDER, 'COMMON');

        $this->data['dayLabel'] = getMessage(DAY_LBL, 'COMMON');
        $this->data['monthLabel'] = getMessage(MONTH_LBL, 'COMMON');
        $this->data['yearLabel'] = getMessage(YEAR_LBL, 'COMMON');
        $this->data['hourLabel'] = getMessage(HOUR_LBL, 'COMMON');
        $this->data['minuteLabel'] = getMessage(MINUTE_LBL, 'COMMON');

        //mm/dd/yyyy
        if ($dateOrder == 0)
        {
            $this->data['monthOrder'] = 0;
            $this->data['dayOrder'] = 1;
            $this->data['yearOrder'] = 2;
            if ($this->field->data_type === EUF_DT_DATETIME)
                $this->data['js']['min_val'] = '1/2/1970 09:00';
            else
                $this->data['js']['min_val'] = '1/2/1970';
        }
        //yyyy/mm/dd
        else if ($dateOrder == 1)
        {
            $this->data['monthOrder'] = 1;
            $this->data['dayOrder'] = 2;
            $this->data['yearOrder'] = 0;
            if ($this->field->data_type === EUF_DT_DATETIME)
                $this->data['js']['min_val'] = sprintf("1970%s/1%s/2%s 09:00", $this->data['yearLabel'], $this->data['monthLabel'], $this->data['dayLabel']);
            else
                $this->data['js']['min_val'] = sprintf("1970%s/1%s/2%s", $this->data['yearLabel'], $this->data['monthLabel'], $this->data['dayLabel']);
        }
        //dd/mm/yyyy
        else
        {
            $this->data['monthOrder'] = 1;
            $this->data['dayOrder'] = 0;
            $this->data['yearOrder'] = 2;
            if ($this->field->data_type === EUF_DT_DATETIME)
                $this->data['js']['min_val'] = '2/1/1970 09:00';
            else
                $this->data['js']['min_val'] = '2/1/1970';

        }
        if($this->data['value'])
        {
            $this->data['value'] = explode(' ', date('m j Y G i', intval($this->data['value'])));
            $this->data['defaultValue'] = true;
        }

    }
}
