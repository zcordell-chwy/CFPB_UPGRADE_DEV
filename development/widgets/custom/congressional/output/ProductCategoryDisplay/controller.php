<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

if (!class_exists('DataDisplayIA'))
	requireWidgetController('custom/congressional/output/DataDisplayIA');

class ProductCategoryDisplay extends DataDisplayIA {
	function __construct () {
		parent::__construct();
		$this->attrs['report_page_url'] = new Attribute(getMessage(REPORT_PAGE_LBL), 'STRING', getMessage(PROD_CAT_RES_LINKS_URL_APPENDED_MSG), '');
		$this->attrs['add_params_to_url'] = new Attribute(getMessage(ADD_PRMS_TO_URL_CMD), 'STRING', getMessage(COMMA_SEPARATED_L_URL_PARMS_MSG), 'kw');
		unset($this->attrs['highlight']);
		$this->attrs['name'] = new Attribute(getMessage(NAME_LBL), 'STRING', getMessage(CMBINATION_TB_FLD_DISP_ATTRIB_FORM_MSG), '');
		$this->attrs['show_both_levels'] = new Attribute('Show both 1st and 2nd level hierachy labels', 'BOOL', 'Boolean indicating whether to show both the 1st and 2nd hierarchy levels if set.', false);
	}

	function generateWidgetInformation () {
		parent::generateWidgetInformation();
		$this->info['notes'] = getMessage(DISP_VAL_DATA_SRC_NAME_ATTRIB_MSG);
		unset($this->parms['kw']);
	}

	function getData () {
		if (parent::retrieveAndInitializeData() === false)
			return false;

		// Validate data type
		if ($this->field->data_type !== EUF_DT_HIERMENU) {
			echo $this->reportError(getMessage(PRODUCTCATEGORYDISPLAY_DISP_MSG));
			return false;
		}

		/*
        // Validate data value as non-empty array
		if ((!is_array($this->data['value'])) || (count($this->data['value']) <= 0))
			return false;
		// Set the filter key for the search url.  Should be 'p' or 'c'.
		if ($this->data['attrs']['report_page_url'] !== '') {
			$this->data['filterKey'] = substr($this->fieldName, 0, 1);
			$this->data['attrs']['url'] = rtrim($this->data['attrs']['url'], '/');
			$this->data['appendedParameters'] = getUrlParametersFromList($this->data['attrs']['add_params_to_url']) . sessionParm();
		}
		$this->data['wrapClass'] = ($this->data['attrs']['left_justify']) ? ' rn_LeftJustify' : '';
        */
	}
}
