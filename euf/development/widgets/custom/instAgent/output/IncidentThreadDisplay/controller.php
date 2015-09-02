<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

class IncidentThreadDisplay extends Widget {

	protected $field;
	protected $table;
	protected $fieldName;

	function __construct () {
		parent::__construct();

		$this->attrs['label'] = new Attribute(getMessage((987)), 'STRING', getMessage((1390)), '{default label}');
		$this->attrs['highlight'] = new Attribute(getMessage((1153)), 'BOOL', getMessage((1154)), true);
		$this->attrs['name'] = new Attribute(getMessage((3622)), 'STRING', getMessage((1772)), '');
		$this->attrs['thread_order'] = new Attribute(getMessage((10884)), 'OPTION', getMessage((685)), 'descending');
		$this->attrs['thread_order']->options = array('ascending', 'descending');
	}
	function generateWidgetInformation () {
		$this->info['notes'] = getMessage((785));
		$this->parms['i_id'] = new UrlParam(getMessage((9348)), 'i_id', false, getMessage((1227)), 'i_id/7');
	}

	function getData () {
		if ($this->retrieveAndInitializeData() === false) {
			return false;
		}
		if ($this->field->data_type !== (10)) {
			echo $this->reportError(getMessage((1233)));
			return false;
		} else
			if ($this->data['value'] && $this->data['attrs']['thread_order'] === 'ascending') {
				$this->data['value'] = array_reverse($this->data['value'], true);
			}
	}

	protected function retrieveAndInitializeData () {
	
		$cacheKey = 'Display_' . $this->data['attrs']['name'];
		$cacheResults = checkCache($cacheKey);
		if (is_array($cacheResults)) {
			list($this->field, $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']) = $cacheResults;
			$this->field = unserialize($this->field);
			return;
		}

		$this->table = "incidents";
		$this->fieldName = "thread";
		$this->CI->load->model('custom/instagent_model');
		$incThread = $this->CI->instagent_model->getBusinessObjectField($this->table, $this->fieldName, getUrlParm('comp_id'));
		if ($incThread === false) {
			return false;
		}
		$this->field = $incThread;
		if (is_null($this->field) || $this->field === false) {
			return false;
		}
		if (is_string($this->field)) {
			echo $this->reportError($this->field);
			return false;
		}
		if ($this->field->data_type === (7)) {
			echo $this->reportError(getMessage((1935)));
			return false;
		}
		$this->data['value'] = $this->field->value;
		if ($this->data['value'] === '' || $this->data['value'] === null)
			return false;
		if ($this->data['attrs']['label'] === '{default label}')
			$this->data['attrs']['label'] = $this->field->lang_name;
		setCache($cacheKey, array(serialize($this->field), $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']));
	}
}
