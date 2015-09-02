<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('FormInput'))
    requireWidgetController('standard/input/FormInput');

class SelectionLogicInput extends FormInput
{
    function __construct()
    {
        parent::__construct();
        $this->CI->load->helper('label_helper');

        //this FormInput attr doesn't apply to SelectionInput
        unset($this->attrs['always_show_mask']);
        $this->attrs['label_input'] = new Attribute(getMessage(INPUT_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_INPUT_CONTROL_LBL), null);
        $this->attrs['label_nothing_selected'] = new Attribute(getMessage(NOTHING_SELECTED_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_VALUE_SELECTED_LBL), 'Choose...');
        $this->attrs['is_checkbox'] = new Attribute("Swich yes/no to checkbox", 'BOOL', "Display yes/no as a checkbox", true);
        $this->attrs['style_custom'] = new Attribute("Custom CSS Style", 'STRING', "Custom CSS to move the field", null);
        $this->attrs['error_nav'] = new Attribute("Custom JavaScript function", 'STRING', "Call function when clicking on error message", null);
        $this->attrs['select_required_pos'] = new Attribute("Flag to display required label", 'INT', "Display red * next to menu drop down label. Specify left position in px", 0);
        $this->attrs['remove_menu_items'] = new Attribute("Remove specific menu items", 'STRING', "Comma seperated list of menu labels to remove for menu custom fields", null);
        $this->attrs['co_status'] = new Attribute("Company status array", 'STRING', "Array of company status labels", getLabel('CO_STATUS_ARRAY'));
        $this->attrs['co_status_desc'] = new Attribute("Company status description array", 'STRING', "Array of company status descriptions", getLabel('CO_STATUS_DESC_ARRAY'));
        $this->attrs['label_style'] = new Attribute( "Label Style", 'STRING', 'Style to be applied to the label for this element. Defaults to "float:left; left:5px; top:-2px;"', 'float:left; left:5px; top:-2px;' );
        $this->attrs['submit_positive_value_only'] = new Attribute( "Submit Positive Value Only", 'STRING', "Optionally submit positive value only. Defaults to false, will not impact form submissions via javascript.", false );
    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] = getMessage(WDGT_ALLWS_USERS_SET_FLD_VALS_DB_MSG);
    }

    function getData()
    {
        if(parent::retrieveAndInitializeData() === false)
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

        // Correct submit_positive_value_only attribute so it's actually a boolean instead of a string.
        if( $this->data['attrs']['submit_positive_value_only'] == 'true' || $this->data['attrs']['submit_positive_value_only'] === true )
            $this->data['attrs']['submit_positive_value_only'] = true;
        else
            $this->data['attrs']['submit_positive_value_only'] = false;
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

}
