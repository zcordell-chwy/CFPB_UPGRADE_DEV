<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class DataDisplayIA extends Widget
{
    protected $field;
    protected $table;
    protected $fieldName;
    protected $fieldSuppressed;

    function __construct()
    {
        parent::__construct();
        $this->attrs['label'] = new Attribute(getMessage(FIELD_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_FORM_FIELD_VALUE_LBL), '{default label}');
        $this->attrs['name'] = new Attribute(getMessage(NAME_LBL), 'STRING', getMessage(COMBINATION_TB_FLD_DISP_ATTRIB_FORM_MSG), '');
        $this->attrs['highlight'] = new Attribute(getMessage(HIGHLIGHT_LBL), 'BOOL', getMessage(HIGHLIGHTS_TXT_FLDS_SRCH_TERM_LBL), true);
        $this->attrs['left_justify'] = new Attribute(getMessage(LEFT_JUSTIFY_LBL), 'BOOL', getMessage(LEFT_JUSTIFY_DATA_AND_LABEL_MSG), false);
		$this->attrs['initial_attachments_only'] = new Attribute("Initially Uploaded Attachments Only", 'BOOL', "If this widget is returning file attachment ".
			"data, should it only return the initially uploaded attachments? Defaults to false.", false);
        $this->attrs['mask_data'] = new Attribute("Mask Data", 'BOOL', "Boolean flag indicating if the data shown to the user for the given field should be ".
			"masked. Defaults to false.", false);
        $this->attrs['num_digits_to_mask'] = new Attribute('Number Digits to Mask', 'INT', "Used in conjunction with the mask_data attribute. If mask_data is ".
			"true, and this value is greater than zero, the widget will inspect the value to see if it's a number of the specified number of digits. If ".
			"so, it will be masked. Defaults to zero.", 0);
		$this->attrs['mask_ssn_all'] = new Attribute('Mask all SSN-formatted characters', 'BOOL', 'Boolean indicating if all SSN-formatted characters should be '.
			'masked. Acceptable formats are: ###-##-####, and #########.', 'false');
        $this->attrs['suppress_field_if_value_equals'] = new Attribute("Suppress Field If Value Equals", 'STRING', "There are occassions where a field should ".
			"not be shown if it contains a specific value (i.e. Don't show the 'Referred To' field if it equals 'FTC' in Government Portal). Defaults to ".
			"an empty string.", '');
        $this->attrs['suppress_field_unless_value_equals'] = new Attribute("Suppress Field Unless Value Equals", 'STRING', "Opposite of ".
			"suppress_field_if_value_equals attribute. Only display the field if it equals the value in this attribute. Defaults to an empty string.", '');
        $this->attrs['break_value'] = new Attribute('Break Value', 'BOOL', "We need some values to display through a secure connection in spite of the F5. ".
			"This will be accomplished by inserting a hidden HTML tag midway through the string. Defaults to false.", false);
        $this->attrs['alt_field'] = new Attribute('Alternate Field', 'STRING', "Occassionally an alternate field needs to be displayed in lieu of the one ".
			"requested. This attribute defines the field that should be displayed in this case. If suppress_field_if_value_equals indicates a field ".
			"should be suppressed, this field will be inspected to determine if something else should be displayed instead. Defaults to blank.", '');
		$this->attrs['hide_time'] = new Attribute('Hide Time', 'BOOL', 'Flag indicating if the time component of a date/time field will be displayed or not', false);
		$this->attrs['append_additional_field_value_for'] = new Attribute('Append additional field Value for', 'STRING', 'The field name indicated here will have ".
			"its value retrieved and appended to the value of the current field.', null);
		$this->attrs['service_product_exclusion_list'] = new Attribute("Service/Product Exclusion List",
				'STRING',
				'This is a comma delimited string of products/services for which file attachments should be ignored.',
				implode(',',getSetting('SERVICE_PRODUCT_EXCLUSION_LIST')));

$this->attrs['negate_value'] = new Attribute("Negate value if Yes/No", 'STRING', 'Negates value of Yes/No field.  Expects true/false as value.', 'false');


        $this->CI->load->helper('config');
        $this->CI->load->helper('label');

        $this->fieldSuppressed = false;
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  getMessage(DSP_VAL_DATA_ELEMENT_NAME_ATTRIB_MSG);
        $this->parms['kw'] = new UrlParam(getMessage(KEYWORD_LBL), 'kw', false, getMessage(THE_CURRENT_SEARCH_TERM_LBL), 'kw/search');
        $this->parms['a_id'] = new UrlParam(getMessage(ANS_ID_LBL), 'a_id', false, getMessage(ANSWER_ID_DISPLAY_INFORMATION_LBL), 'a_id/3');
        $this->parms['i_id'] = new UrlParam(getMessage(INCIDENT_ID_LBL), 'i_id', false, getMessage(INCIDENT_ID_DISPLAY_INFORMATION_LBL), 'i_id/7');
    }

    function getData()
    {
        if($this->retrieveAndInitializeData() === false)
            return false;

        if( $this->fieldSuppressed && $this->data['attrs']['alt_field'] != '' )
        {
            logmessage( sprintf( "'%s' was suppressed. Showing '%s' instead.", $this->data['attrs']['name'], $this->data['attrs']['alt_field'] ) );
            $this->data['attrs']['name'] = $this->data['attrs']['alt_field'];

            // Reset suppression attributes so they don't break us later.
            $this->data['attrs']['suppress_field_if_value_equals'] = '';
            $this->data['attrs']['suppress_field_unless_value_equals'] = '';
            $this->fieldSuppressed = false;

            if( $this->retrieveAndInitializeData() === false )
            {
                return false;
            }

            logmessage( sprintf( "New field value: '%s'", $this->data['value'] ) );
        }

        logmessage( sprintf( "'%s' value: '%s'", $this->data['attrs']['name'], $this->data['value'] ) );

        if( $this->data['value'] === '' || $this->data['value'] === null )
        {
            return false;
        }
    }

    protected function retrieveAndInitializeData()
    {
        //Lowercase attributes
        $this->data['attrs']['name'] = strtolower($this->data['attrs']['name']);
        $validAttributes = parseFieldName($this->data['attrs']['name']);
        if(!is_array($validAttributes))
        {
            echo $this->reportError($validAttributes);
            return false;
        }

        $cacheKey = 'Display_' . $this->data['attrs']['name'];
        $cacheResults = checkCache($cacheKey);
        if(is_array($cacheResults))
        {
            list($this->field, $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']) = $cacheResults;
            $this->field = unserialize($this->field);
            return;
        }

        $fieldFormatter = getFieldFormatter($this->data['attrs']['highlight'], getUrlParm('kw'));
        $this->table = $validAttributes[0];
        $this->fieldName = $validAttributes[1];
        //call custom model
        $this->CI->load->model('custom/InboundReferral_model');
        //$this->field = getBusinessObjectField($this->table, $this->fieldName, $isFromProfile, $fieldFormatter);
//force-bypass permissions check
if($this->table == 'contacts'){
$this->field = $this->CI->InboundReferral_model->getBusinessObjectField($this->table, $this->fieldName, getUrlParm('referral_id'), false);
}else{
        $this->field = $this->CI->InboundReferral_model->getBusinessObjectField($this->table, $this->fieldName, getUrlParm('referral_id'));
}
        if($this->field === null)
            return false;
        if(is_string($this->field))
        {
            echo $this->reportError($this->field);
            return false;
        }
        if($this->field->data_type === EUF_DT_PASSWORD)
        {
            echo $this->reportError(getMessage(PASSWORD_FIELDS_DISPLAYED_MSG));
            return false;
        }
		if(property_exists($this->field->value, 'LookupName')){
			$this->data['value'] = $this->field->value->LookupName;
		}else{
			//Grab the data we need.
			$this->data['value'] = $this->field->value;
		}

		/*
		 * Deal with date/time values
		 */
		if($this->data['attrs']['hide_time'])
		{
			if($this->field->data_type === EUF_DT_DATETIME)
			{
				list($this->data['value'], $garbage) = explode(' ', $this->data['value']);
			}
		}

		/*
		 * Append additional field's value to this field?
		 */
		if($this->attrs['append_additional_field_value_for'])
		{
			if($this->data['value'])
			{
				$addField = strtolower($this->attrs['append_additional_field_value_for']);
				$validAddFieldAttrs = parseFieldName($this->data['attrs']['append_additional_field_value_for']);
				if(is_array($validAddFieldAttrs))
				{
					$addField = $this->CI->InboundReferral_model->getBusinessObjectField($validAddFieldAttrs[0],
						$validAddFieldAttrs[1], getUrlParm('referral'));
					if(!is_null($addField) && !is_string($addField))
					{
						$this->data['value'] .= ' '.$addField->value;
					}
				}
			}
		}

		/*
         * File attachment visibility in Congressional Portal is fairly selective and has the following requirements:
         *
         * Referring agencies will have the ability to view and download consumer provided attachments.
         * Referring agencies will be restricted from viewing attachments marked as Private.
         * Referring agencies will have the ability to view and download Company attachments except:
         * 1)	Communications between Company and Investigations;
         * 2)	Administrative responses from Company;
         * 3)	Consumer�s Credit Report attachment(s) from Company; or
         * 4)	Medical Debt attachments.
         *
         * The below approach was adapted from the vangent interface custom/output/FileListDisplay widget.
		 */
        if( $this->fieldName == 'fattach'){
            logmessage( "Loading inboundreferral model." );
            $incident = $this->CI->InboundReferral_model->getIncident(getUrlParm('referral_id'));
            $newAttachments = array();

            // $this->CI->load->model('standard/Incident_model');
            logmessage( "loading fattach model" );
            $this->CI->load->model('custom/Fattach_model');
            logmessage( "fattach model loaded" );
            logmessage( $this->data['value'] );
            foreach ($this->data['value'] as $key => $attachment)
            {
                $fattach_id = $attachment[0];
                $created = $attachment[2];
                logmessage( "Grabbing attachment details." );
                //$fattachObj = $this->CI->Fattach_model->getFattach($fattach_id, $created);
                $fattach = $this->CI->Fattach_model->get($fattach_id, $created, TRUE);
                logmessage( $fattach );
                if (strpos($fattach[0], getSetting('CFPBFI_DOMAIN')) > 0)
                {
                    /*
                    $incident = $this->CI->Incident_model->get($CPHPincident->ID);
                    */
                    $status = null;
                    if( $incident->CustomFields->company_status_1 )
                        $status = $incident->CustomFields->company_status_1->LookupName;
                    else if( $incident->CustomFields->company_status_2 )
                        $status = $incident->CustomFields->company_status_2->LookupName;

                    switch ($status)
                    {
                        case getLabel('CO_STATUS_INCORRECT_COMPANY'):
                        case getLabel('CO_STATUS_DUPLICATE_CASE'):
                        case getLabel('CO_STATUS_SENT_TO_REGULATOR'):
                        case getLabel('CO_STATUS_ALERTED_CFPB'):
                        case getLabel('CO_STATUS_REDIRECTED'):
                            logmessage( sprintf( "Company status was an administrative response (%s)", $status ) );
                            break;
                        default:
                            // Only include attachments from Company Portal that aren't in our exclusion list.
                            if(!in_array($incident->Product->ID, explode(',',$this->data['attrs']['service_product_exclusion_list'])))
                                $newAttachments[] = $this->data['value'][$key];
                            break;
                    }
                }
                else
                    $newAttachments[] = $this->data['value'][$key];
            }

            $this->data['value'] = $newAttachments;
		}

        /*
		 * Should the value be masked?
		 */
        $maskData = false;
        if( $this->data['attrs']['mask_data'] === true )
        {
            if( $this->data['attrs']['num_digits_to_mask'] > 0 )
            {
                if( is_numeric( $this->data['value'] ) && strlen( strval( $this->data['value'] ) ) === $this->data['attrs']['num_digits_to_mask'] )
                    $maskData = true;
            }
            else
            {
                $maskData = true;
            }
        }

        if( $maskData )
            $this->data['value'] = str_replace( $this->data['value'], str_repeat( '*', strlen( $this->data['value'] ) ), $this->data['value'] );

		if($this->data['attrs']['mask_ssn_all'] === true)
		{
			if(preg_match('/^\d{9}$/', $this->data['value']) || preg_match('/^\d{3}-\d{2}-\d{4}$/', $this->data['value']))
			{
				$this->data['value'] = str_replace($this->data['value'], str_repeat('*', strlen($this->data['value'])), $this->data['value']);
			}
		}

        // Does this field need to be suppressed?
        if( $this->_suppressField() )
            $this->data['value'] = '';

        // Do we need to inject a hidden HTML element into the string?
        if( $this->data['attrs']['break_value'] === true && strlen( $this->data['value'] ) > 0 )
        {
            // We want to hit the middle of the field, but intval will truncate a float if the length is an odd number. Overshoot by one to compensate.
            $chunkLength = intval( strlen( $this->data['value'] ) / 2 ) + 1;
            $valueToken = str_split( $this->data['value'], $chunkLength );
            $this->data['value'] = sprintf( '%s<span class="rn_Hidden"></span>%s', $valueToken[0], $valueToken[1] );
        }

        //We don't display fields whose values are an empty string or null.
        // 9/19/2012: Moving this capability to the getData() function.
        /*
        if($this->data['value'] === '' || $this->data['value'] === null)
            return false;
        */
        if( $this->data['value'] !== '' || $this->data['value'] !== null )
        {
            if($this->data['attrs']['label'] === '{default label}')
                $this->data['attrs']['label'] = $this->field->lang_name;
            setCache($cacheKey, array(serialize($this->field), $this->table, $this->fieldName, $this->data['value'], $this->data['attrs']['label']));
        }
    }

    /**
     * Helper function that determines if a field should be, or currently is, suppressed.
     *
     * @return  BOOL    Boolean value indicating if a field should be suppressed.
     */
    private function _suppressField()
    {
        if( $this->data['value'] === '' || $this->data['value'] === null )
        {
            $this->fieldSuppressed = true;
        }
        else
        {
            if(
                ( $this->data['attrs']['suppress_field_if_value_equals'] != '' && $this->data['value'] == $this->data['attrs']['suppress_field_if_value_equals'] ) ||
                ( $this->data['attrs']['suppress_field_unless_value_equals'] != '' && $this->data['value'] != $this->data['attrs']['suppress_field_unless_value_equals'] )
            )
            {
                logmessage( $this->data['value'] );
                $this->fieldSuppressed = true;
            }
            else
            {
                $this->fieldSuppressed = false;
            }
        }

        return $this->fieldSuppressed;
    }
}
