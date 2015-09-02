<?php 
if (!defined('BASEPATH')){
	exit('No direct script access allowed');
}
if (!class_exists('FormInput'))
	requireWidgetController('custom/instAgent/input/FormInput');
class SelectionInput extends FormInput {
	function __construct () {
		parent::__construct();
		unset($this->attrs['always_show_mask']);
	}
	function generateWidgetInformation () {
		parent::generateWidgetInformation();
		$this->info['notes'] = getMessage((3022));
	}
	function getData () {
		if (parent::retrieveAndInitializeData() === false) {
			return false;
		}
		if ($this->fieldName === 'status' && !getUrlParm('comp_id')) {
			echo $this->reportError(sprintf(getMessage((1981)), 'incidents.status'));
			return false;
		}
		if ($this->field->data_type !== (4) && $this->field->data_type !== (12) && $this->field->data_type !== (3)) {
			echo $this->reportError(sprintf(getMessage((1994)), $this->fieldName));
			return false;
		}
		if (!($this->field instanceof CustomField)) {
			if (($this->CI->meta['sla_failed_page'] || $this->CI->meta['sla_required_type']) && $this->fieldName === 'sla' && count($this->field->menu_items))
				$this->data['hideEmptyOption'] = true;
			if ($this->field->data_type === (12)) {
				$this->data['menuItems'] = array(getMessage((3221)), getMessage((1207)));
				$this->data['hideEmptyOption'] = true;
			}
		}
		if ($this->field->data_type === (3)) {
			$this->data['radioLabel'] = array(getMessage((22)), getMessage((28)));
			if (is_null($this->data['value']))
				$this->data['checkedIndex'] = -1;
			elseif (intval($this->data['value']) === 1)
				$this->data['checkedIndex'] = 1;
			else
				$this->data['checkedIndex'] = 0;
		}
		$this->data['showAriaHint'] = $this->CI->clientLoader->getCanUseAria() && $this->data['js']['hint'];
	}
}
