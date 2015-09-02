<?php
if(!defined('BASEPATH'))
	exit('No direct script access allowed');

if(!class_exists('FormInput2'))
	requireWidgetController('custom/input/FormInput2');

class SelectionLogicInput3 extends FormInput2 {
	function __construct() {
		parent::__construct();
		//this FormInput attr doesn't apply to SelectionInput
		unset($this -> attrs['always_show_mask']);
		$this -> attrs['label_input'] = new Attribute(getMessage(INPUT_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_INPUT_CONTROL_LBL), null);
		$this -> attrs['label_note'] = new Attribute('label_note', 'STRING', 'Hint text below input field', null);
		$this -> attrs['label_nothing_selected'] = new Attribute(getMessage(NOTHING_SELECTED_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_VALUE_SELECTED_LBL), 'Choose...');
		$this -> attrs['is_checkbox'] = new Attribute("Swich yes/no to checkbox", 'BOOL', "Display yes/no as a checkbox", true);
        $this -> attrs['show_menu_as_radio'] = new Attribute("show_menu_as_radio", 'BOOL', "Display menu custom fields as radio input fields", false);
        $this -> attrs['style_custom'] = new Attribute("Custom CSS Style", 'STRING', "Custom CSS to move the field", null);
		$this -> attrs['error_nav'] = new Attribute("Custom JavaScript function", 'STRING', "Call function when clicking on error message", null);
	    $this -> attrs['error_msg'] = new Attribute("Custom Error Message", 'STRING', "Display custom error message", null);
        $this -> attrs['select_required_pos'] = new Attribute("Flag to display required label", 'INT', "Display red * next to menu drop down label. Specify left position in px", 0);
		$this -> attrs['optional_on_hide'] = new Attribute('Flag to indicate if the widget should become an optional widget when it is hidden', 'BOOL', 'make element optional when hidden', false);
        $this -> attrs['clear_value_on_hide'] = new Attribute('Clear Value on Hide', 'BOOL', 'Flag indicating if the value of the field should be cleared out if object is hidden.', false);
        $this -> attrs['enable_autoroutelookup'] = new Attribute('Enable autoroutelookup', 'BOOL', 'Enable dynamic behavior for autorouting of orgs', false);
        $this -> attrs['radio_group'] = new Attribute("radio_group", "STRING", "If set, causes any yes/no elements in the group to be displayed as radio buttons with only one button selectable", null);
		$this -> attrs['label_accesibility'] = new Attribute("label_accesibility", "STRING", "Hidden label for assistive technologies that is added if a regular label is not present", "");
		$this -> attrs['label_clarification'] = new Attribute("label_clarification", "STRING", "Text providing further clarification to input", "");
        $this -> attrs['required_when_parent_equals'] = new Attribute("Optional when parent equals", "STRING", "Fields rendered via the SelectionLogicInput2 widget can be made optional when another field is not equal a specific value. This attribute sets the table.field which determines if this field is required. Works in conjunction with the 'required_when_field_equals' field.", "");
        $this -> attrs['required_when_field_equals'] = new Attribute("Optional when field equals", "STRING", "Fields rendered via the SelectionLogicInput2 widget can be made optional when another field is not equal a specific value. This attribute sets the value which determines if this field is required. Works in conjunction with the 'required_when_parent_equals' field.", "");
        $this -> attrs['help_text_when_hidden'] = new Attribute("Help text when hidden", "STRING", "Text to be shown the user when the field is not required. Used in conjunction with the required_when_parent_equals and required_when_field_equals attributes.", "");
            $this->attrs['one_selection_per_widget_group'] = new Attribute( "One Selection Per Widget Group", "BOOL", "Boolean setting indicating if only one selection in the current widget group is allowed.", false );
	}

	function generateWidgetInformation() {
		parent::generateWidgetInformation();
		$this -> info['notes'] = getMessage(WDGT_ALLWS_USERS_SET_FLD_VALS_DB_MSG).
			' This widget is a copy of the SelectionLogicInput2 widget. It adds required field validation for menu fields displayed as radios. '.
			' It has also been modified to support Custom Business Objects.' .
			' To use with a CBO, set the name="NAMESPACE.cbo_name.field_name" ';
	}

	function getData() {
		//a radio group is basically a widget group that requires 1 and only 1 selection
		if(!is_null($this -> data['attrs']['radio_group'])) {
			$this -> data['js']['widget_group'] = $this -> data['attrs']['radio_group'];
			$this -> data['js']['radio_group'] = $this -> data['attrs']['radio_group'];
			$this -> data['js']['is_checkbox'] = $this -> data['attrs']['is_checkbox'];
			//$this -> data['js']['is_checkbox'] = false;
		} else {
			$this -> data['js']['widget_group'] = $this -> data['attrs']['widget_group'];
			$this -> data['js']['is_checkbox'] = $this -> data['attrs']['is_checkbox'];
		}
		$this -> data['js']['optional_on_hide'] = $this -> data['attrs']['optional_on_hide'];
		if(parent::retrieveAndInitializeData() === false)
			return false;

		$this->data['accesibility_label' ] = $this->field->lang_name;

		//Status field should not be shown if there is not an incident ID on the page
		if($this -> fieldName === 'status' && !getUrlParm('i_id')) {
			echo $this -> reportError(sprintf(getMessage(PCT_S_FLD_DISPLAYED_PG_I_ID_PARAM_MSG), 'incidents.status'));
			return false;
		}

		if($this -> field -> data_type !== EUF_DT_SELECT && $this -> field -> data_type !== EUF_DT_CHECK && $this -> field -> data_type !== EUF_DT_RADIO) {
			echo $this -> reportError(sprintf(getMessage(PCT_S_MENU_YES_SLASH_FIELD_MSG), $this -> fieldName));
			return false;
		}

		//standard field
		if(!($this -> field instanceof CustomField)) {
			if(($this -> CI -> meta['sla_failed_page'] || $this -> CI -> meta['sla_required_type']) && $this -> fieldName === 'sla' && count($this -> field -> menu_items))
				$this -> data['hideEmptyOption'] = true;
			if($this -> field -> data_type === EUF_DT_CHECK) {
				$this -> data['menuItems'] = array(getMessage(YES_PLEASE_RESPOND_TO_MY_QUESTION_MSG),
					getMessage(I_DONT_QUESTION_ANSWERED_LBL));
				$this -> data['hideEmptyOption'] = true;
			}
		}
		if($this -> field -> data_type === EUF_DT_RADIO) {
			$this -> data['radioLabel'] = array(getMessage(NO_LBL),
				getMessage(YES_LBL));
			//find the index of the checked value
			if(is_null($this -> data['value']))
				$this -> data['checkedIndex'] = -1;
			elseif(intval($this -> data['value']) === 1)
				$this -> data['checkedIndex'] = 1;
			else
				$this -> data['checkedIndex'] = 0;
		}
		$this -> data['showAriaHint'] = $this -> CI -> clientLoader -> getCanUseAria() && $this -> data['js']['hint'];
	}

}
