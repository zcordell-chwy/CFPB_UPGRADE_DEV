<?php
if(!defined('BASEPATH'))
	exit('No direct script access allowed');

class hideElement extends Widget {
	function __construct() {
		parent::__construct();

		$this -> attrs['control_element'] = new Attribute("control_element", 'STRING', "Contains the id of the element to hide or display", null);
		$this -> attrs['listen_ids'] = new Attribute("listen_ids", 'STRING', "A comma separated list of page element ids.  When one of these elements is clicked, the control element visibility is toggled", null);
		$this -> attrs['listen_value'] = new Attribute("listen_value", 'STRING', "Value of listen_id to show element", null);
		$this -> attrs['form_field_name'] = new Attribute('form_field_name', 'STRING', "A form field name to toggle the control element on.  Should be in the format table.field.  listen_ids must be empty for this field to work", null);
		$this -> attrs['form_field_value'] = new Attribute('form_field_value', 'STRING', "The value that the form field specified in form_field_name must have to show the control element.", null);
		$this -> attrs['inverse_compare'] = new Attribute('inverse_compare', 'BOOL', "Compare the negation of the form_field_value (i.e. if form_field_value != field value).", false);
	}

	function generateWidgetInformation() { //Create information to display in the tag gallery here
	}

	function getData() { //Perform php logic here
		if(isset($this -> data['attrs']['control_element']) && strlen($this -> data['attrs']['control_element']) > 0) {
			$this -> data['js']['control_element'] = $this -> data['attrs']['control_element'];
		}
		if(isset($this -> data['attrs']['listen_ids']) && strlen($this -> data['attrs']['listen_ids']) > 0) {
			$this -> data['js']['listen_ids'] = explode(",", $this -> data['attrs']['listen_ids']);
		}
		if(isset($this -> data['attrs']['form_field_name']) && strlen($this -> data['attrs']['form_field_name']) > 0) {
			$this -> data['js']['form_field_name'] = $this -> data['attrs']['form_field_name'];
		}
		if(isset($this -> data['attrs']['form_field_value']) && strlen($this -> data['attrs']['form_field_value']) > 0) {
            $this -> data['js']['form_field_value'] = explode(",", $this -> data['attrs']['form_field_value']);
        }
	}

}
