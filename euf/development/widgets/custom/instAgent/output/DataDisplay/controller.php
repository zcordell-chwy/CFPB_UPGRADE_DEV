<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}
//class DataDisplayIA extends Widget {
class DataDisplay extends Widget {
	protected $field;
	protected $table;
	protected $fieldName;
	function __construct () {
		parent::__construct();
		$this->attrs['label'] = new Attribute(getMessage((987)), 'STRING', getMessage((1390)), '{default label}');
		$this->attrs['name'] = new Attribute(getMessage((3622)), 'STRING', getMessage((373)), '');
		$this->attrs['highlight'] = new Attribute(getMessage((1153)), 'BOOL', getMessage((1154)), true);
		$this->attrs['left_justify'] = new Attribute(getMessage((1574)), 'BOOL', getMessage((1573)), false);
	}
	function generateWidgetInformation () {
		$this->info['notes'] = getMessage((787));
		$this->parms['kw'] = new UrlParam(getMessage((13321)), 'kw', false, getMessage((2764)), 'kw/search');
		$this->parms['a_id'] = new UrlParam(getMessage((3470)), 'a_id', false, getMessage((146)), 'a_id/3');
		$this->parms['i_id'] = new UrlParam(getMessage((9348)), 'i_id', false, getMessage((1227)), 'i_id/7');

	}

	function getData () {
		if ($this->retrieveAndInitializeData() === false){
			return false;
		}
	}
	protected function retrieveAndInitializeData () {
		$this->data['attrs']['name'] = strtolower($this->data['attrs']['name']);
		$validAttributes = parseFieldName($this->data['attrs']['name']);
		if (!is_array($validAttributes)) {
			echo $this->reportError($validAttributes);
			return false;
		}
		$cacheKey = 'Display_' . $this->data['attrs']['name'];
		$cacheResults = checkCache($cacheKey);
		if (is_array($cacheResults)) {
			
			list($this->field, $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']) = $cacheResults;
			$this->field = unserialize($this->field);
			return;
		}
		$fieldFormatter = getFieldFormatter($this->data['attrs']['highlight'], getUrlParm('kw'));
		$this->table = $validAttributes[0];
		$this->fieldName = $validAttributes[1];

		$this->CI->load->model('custom/instagent_model');
		$this->field = $this->CI->instagent_model->getBusinessObjectField($this->table, $this->fieldName, getUrlParm('comp_id'));
		if ($this->field === null)
			return false;
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
