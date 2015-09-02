<?php
use RightNow\Connect\v1 as RNCPHP;
include_once(APPPATH . "libraries/PSLog-2.0.php");

/**
 * This class provides functionality to allow institutional agents to access incidents
 * that they don't own.  A modified security mode is used.  Agents may only view
 * data if they have the incidents.c$is_inst_agent field set to true.  They may only view
 * an incident there is a complaint custom object that ties that incident to the organization
 * that the agent is associated with.
 *
 * @author ben.hussey
 *
 */
class InstAgent_model extends Model {
	/**
	 * Used for debugging.  Examine for error information
	 *
	 * @var unknown_type
	 */
	private $errors = array();

	/**
	 * Indicates what fields should be displayed for the data grid
	 *
	 * @var array
	 */
	private $dbFields = array(
        "incidents.ref_no",
        'incidents.c$name_on_card',
        'incidents.c$rccnumber',
        'incidents.cat',
	    /*'incidents.subject',*/
		'incidents.c$bank_statuses',
        'incidents.c$sent_bank_date',
	    /*'incidents.updated',*/
		'incidents.c$response_due',
        'incidents.prod'
    );

	/**
	 * Used to store the formatting information for the grid display
	 * @var array
	 */
	private $columns = array();

	/**
	 * This field is used to indicate the mapping between the database field (used in the widgets)
	 * and the PHP connect values.  In some cases, the mapping is not direct and some massaging must take place
	 * in code.
	 *
	 * @var array
	 */
	private $fieldLookup = array(
        'ref_no' => 'ReferenceNumber',
        'contact_email' => 'PrimaryContact->Emails->0->Address',
        'status' => 'StatusWithType->Status->LookupName',
        'created' => 'CreatedTime',
        'updated' => 'UpdatedTime',
        'prod' => 'Product',
        'cat' => 'Category',
        'fattach' => 'FileAttachments',
        'due_data' => 'InitialSolutionTime',
        'subject' => 'Subject'
    );

	private $dateFormat = "m/d/Y";
	private $timeFormat = "H:i";

	/**
	 * Indicates what incidents should be displayed and available
	 * @var array
	 */
	private $incStatusToShow = null;//array('In progress', 'Sent to company', 'Past due');

	/**
	 * Indicates if only archived (responded to) incidents should be returned
	 * @var BOOL	 */
	private $archivedIncidents = FALSE;

	/**
	 * Indicates list type (BANK / CFPB)
	 * @var STRING	 */
	private $listType = null;
	
	/**
	* PSLog instance
	*/
	protected $log;

	function __construct() {
		require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
                initConnectAPI();
		parent::__construct();

        $this->load->model( 'custom/ContactPermissions_model' );
        
        $this->log = new \PS\Log\v2\Log(\PS\Log\v2\Type::CP);
	    $this->log
        	->subtype('InstaAgentModel')
        	->logThreshold(\PS\Log\v2\Severity::Info) //Set default log level
        	->debug("Logger initialized");
	}

	/*********************************Public Methods*********************************/

    /**
     * Provide public method to get the complaint object
     *
     * @param $compId
     * @return complaint object
     */
    public function getComplaint($compId) {
        return $this->get_compObj($compId);
    }

	/**
	 * Returns how many file attachments are on an existing incident
	 *
	 * @param $compId int The ID of the complaint that contains the incident
	 * @return int How many file attachments there are
	 */
	public function getFileAttachmentCount($compId) {
		if(is_null($compId) || !is_numeric($compId) || $compId < 1) {
			return false;
		}

		$complaint = $this -> get_compObj($compId);
		if($complaint == false) {
			return false;
		}

		$attachCount = count($complaint -> Incident -> FileAttachments);
		return $attachCount;
	}

	/**
	 * Returns an array of incidents that the agent may view formatted for a custom Grid2
	 * widget
	 */
	public function getOrgIncidentsForGrid($sortCol = NULL, $desc = 0) {
        $profile = $this -> session -> getProfile();
		if(is_null($profile -> org_id -> value) || !is_numeric($profile -> org_id -> value)) {
			$this -> add_error("Invalid Profile Org Id");
			return false;
		}
		$orgComplaints = $this -> getOrgIncidents($profile -> org_id -> value);
		if($orgComplaints === false) {
			$this -> add_error("Invalid Complaint Array", __LINE__);
			return false;
		}
		$gridData = $this -> getGridData($orgComplaints);
		$returnData['headers'] = $this -> get_columns();

		if($sortCol !== NULL) {//sort column is set
			foreach($gridData as $k => $v) {
				$sortSubject[] = $v[$sortCol];
			}
			$ascdesc = $desc ? SORT_DESC : SORT_ASC;
			// need to sort based on data type...
			// for now we will hard code the type of sort based on column
			switch ($sortCol) {
				case 0:
					// callback function to get refno from html grid data to sort on
					function _get_refno($str) {
						$str_arr = explode('>', $str);
						return $str_arr[1];
					}
					$array_sortable = array_map('_get_refno', $sortSubject);
					array_multisort($array_sortable, $ascdesc, SORT_STRING, $gridData);
					break;
				case 1:
				case 2:
				case 3:
				case 4:
				case 7:
					$array_sortable = array_map('strtolower', $sortSubject);
					array_multisort($array_sortable, $ascdesc, SORT_STRING, $gridData);
					break;
				case 5:
				case 6:
					$array_sortable = array_map('strtotime', $sortSubject);
					array_multisort($array_sortable, $ascdesc, SORT_NUMERIC, $gridData);
					break;
			}
		}

		$returnData['data'] = $gridData;
		$returnData['page'] = 0;
		//intval($pageNumber);
		return $returnData;
	}


	/**
	 * Recreates the functionality of the middlelayer-getBusinessObjectField method.
	 *
	 * @param string $table
	 * @param string $field
	 * @param string $comp_id
	 * @param bool $returnId Indicates if the id rather than the value of the field should be returned.  Used for menus.
	 */
	public function getBusinessObjectField($table, $field, $comp_id, $returnId = false, $checkUserPrivs = true) {
		if($table == 'incidents') {

			$fieldInfo = getBusinessObjectField("incidents", $field);
			//handle special cases
			switch ($field) {
				case 'thread' :
					return $this -> getIncThread($comp_id);
					break;
			}
			
			//try to get the field heuristically
			$value = $this -> getFieldValue($comp_id, $field, $returnId);

            return $value;

		} elseif($table == 'contacts') {
			//verify that the user has privs
			//TODO move below and use $compObj->Incident->ID instead of comp_id
			//if (!$this->validateAgentIncAccess($comp_id)) {
			//header("Location: /app/error/error_id/4" . sessionParm());
			//$this->add_error("Access Denied", __LINE__);
			//return false;
			//}
			try {
				$compObj = RNCPHP\CO\ComplaintAgainstOrg::fetch($comp_id);
			} catch (Exception $e) {
				$this -> add_error($e -> getMessage(), __LINE__);
				return false;
			}
			try {
				$res = RNCPHP\ROQL::queryObject("SELECT Incident FROM Incident I WHERE I.ID = " . $compObj -> Incident -> ID . " ") -> next();
			} catch (RNCPHP\ConnectAPIError $err) {
				$msg = "Error Generated ::" . $err -> getCode() . "::" . $err -> getMessage();
				die($msg);

			}

			$incident = $res -> next();
			$contact = $incident -> PrimaryContact;
			// added phone field
            $contactFields = array(
		'c$salutation' => array('label' => "Salutation", 'value' => $contact->CustomFields->salutation->LookupName),
                "first_name" => array('label' => "First Name", 'value' => $contact -> Name -> First),
		'c$middle_name' => array( 'label' => "Middle Name", 'value' => $contact -> CustomFields -> middle_name),
                "last_name" => array('label' => "Last Name", 'value' => $contact -> Name -> Last),
                'c$suffix' => array('label' => "Suffix", 'value' => $contact -> CustomFields -> suffix -> LookupName),
                "street" => array('label' => "Street", 'value' => $contact -> Address -> Street),
                "city" => array('label' => "City", 'value' => $contact -> Address -> City),
                "prov_id" => array('label' => "State", 'value' => $contact -> Address -> StateOrProvince -> LookupName),
                "postal_code" => array('label' => "Zip Code", 'value' => $contact -> Address -> PostalCode),
                "ph_mobile" => array('label' => "Phone", 'value' => $contact -> Phones[0] -> Number)
            );
			$contactField = new TextField();
			$contactField -> data_type = 8;
			$contactField -> value = $contactFields[$field]['value'];
			$contactField -> lang_name = $contactFields[$field]['label'];

			return $contactField;

		}
	}

	/**
	 * Used to process form input.
	 *
	 * @param unknown_type $data
	 * @param unknown_type $comp_id
	 */
	public function sendForm($data, $comp_id) {
		$status = array();
	
		try{
	        $this->log->debug('Data passed', null, print_r($data, true));
	        $this->log->debug('Id passed', null, print_r($comp_id, true));
        	
			// Test the security synchronization token to ensure that
			// it matches with the source side.  Return the error
			// condition indicating a token error.
			$formToken = $this -> input -> post('f_tok');
			if(isValidSecurityToken($formToken, 0) === false) {
				$log->warning("Invalid security token.");
				return array('status' => '-1', 'sessionParm' => sessionParm());
			}
			
			$incidentFields = array();
			$investigationFields = array();
			
			foreach($data as $field) {
				switch ($field->table) {
					case 'incidents' :
						$incidentFields[$field -> name] = $field;
						break;
					case 'CO_Invest$Investigation' :
						$investigationFields[$field -> name] = $field;
						break;
				}
			}
		    // add attachments to the investigation
		    if(count($investigationFields) > 0) {
		      $this->log->debug('Adding attachments to investigation');
		      $result = $this->updateInvestigation($comp_id, $investigationFields);
					if($result === true) {
						$status['message'] = "Attachment Added";
						$status['status'] = 1;
					} else {
						$status['message'] = "Unable to add attachment";
					}
		    }
		    
			if(count($incidentFields) > 0) {
		        $this->log->debug('Updating incident.');
				$result = $this->updateIncident($comp_id, $incidentFields);
				$this->log->debug('Setting status.');
				if($result === true) {
					$status['message'] = "Incident Updated";
					$status['status'] = 1;
				} else {
					$status['message'] = "Unable to update incident";
				}
			} else {
				$status['message'] = "No Data to Update";
			}
	
			//If the current token being used will no longer be valid on the next submit, pass the new token back
			//to the client so they can resubmit
			$this->log->debug('Checking form token.');
			if($newFormToken !== '') {
				$result['newFormToken'] = $newFormToken;
			}
			
			$this->log->debug('Setting session parm.');
			$status['sessionParm'] = sessionParm();
		}
		catch(Exception $e){
			$this->log->error("Exception Thrown", null, $e->getMessage());
		}
		
		$this->log->debug("Return status", null, print_r($status, true));
		return $status;

	}

	/*********************************Private Methods*********************************/

	/**
	 * Returns the value of the passed field.  The data is pulled via PHP Connect.
	 *
	 * @param string $comp_id
	 * @param string $dbField
	 * @param bool $returnId indicates if the id or lookupname should be returned.
	 */
	protected function getFieldValue($comp_id, $dbField, $returnId) {

		$compObj = $this -> get_compObj($comp_id);
		if($compObj === false) {
			return false;
		}
		
		$incident = $compObj -> Incident;
		$fieldInfo = getBusinessObjectField("incidents", $dbField);
		//This breaks 'Age' for some reason and doesn't seem to have any other side effects.
		/*
		if(is_null($fieldInfo)) {
			return ;
		}
		*/

		if(substr($dbField, 0, 2) == 'c$') {
			$connectField = substr($dbField, 2);
			$fieldInfo -> value = $this -> getFieldValStr($fieldInfo, $incident -> CustomFields -> $connectField, $returnId);
		} else {
			$connectField = $this -> getConnectFieldFromDbField($dbField, $incident);
			if($connectField === false) {
				return false;
			}

			$fieldInfo -> value = $this -> getFieldValStr($fieldInfo, $connectField, $returnId, $incident -> ID);
		}

		return $fieldInfo;
	}

	/**
	 * Formats data from the db appropriate for its type.
	 *
	 * @param array $fieldInfo
	 * @param string $value
	 * @param bool $returnId indicates if the field value should contain an id or lookup name.
	 * @param int $i_id Required for HierMenus on incidents.
	 */
	private function getFieldValStr($fieldInfo, $value, $returnId, $i_id = null) {
		switch ($fieldInfo->data_type) {

			case EUF_DT_VARCHAR :

			case EUF_DT_MEMO :

			case EUF_DT_INT :
				return $value;
				break;

			case EUF_DT_SELECT :
				if($returnId) {
					return $value -> ID;
				} else {
					return $value -> LookupName;
				}
				break;

			case EUF_DT_RADIO :
                if( isset( $value ) )
    				return ($value == 1) ? "Yes" : "No";
                else
                    return null;
				break;

			case EUF_DT_DATE :
				return date($this -> get_dateFormat(), $value);
				break;
			case EUF_DT_DATETIME :
				return date($this -> get_dateTimeFormat(), $value);
				break;

			case EUF_DT_HIERMENU :
				if(is_null($i_id) || !is_numeric($i_id) || $i_id < 1) {
					return null;
				}
				if(is_null($fieldInfo -> hm_type) || !is_numeric($fieldInfo -> hm_type) || $fieldInfo -> hm_type < 1) {
					return null;
				}
				return Incident_model::hierDisplayGet($i_id, $fieldInfo -> hm_type);
				break;

			case EUF_DT_FATTACH :
				if(is_null($i_id) || !is_numeric($i_id) || $i_id < 1) {
					return false;
				}
				return Incident_model::getFileAttachments($i_id);
				break;
			default :
				return $value;
				break;
		}

	}

	/**
	 * Returns a formatted incident thread.
	 *
	 * @param int $comp_id
	 */
	private function getIncThread($comp_id) {
		$compObj = $this -> get_compObj($comp_id);
		if($compObj === false) {
			return false;
		}
		$incident = $compObj -> Incident;
		if($incident === false) {
			return false;
		}
		if( $thread->EntryType->ID == ENTRY_NOTE){
			continue;
		}
		$this -> markIncidentRead($incident);
		$fieldInfo = getBusinessObjectField("incidents", "thread");

		//make sure that we don't double display thread entries
		$fieldInfo -> value = array();

		if(is_object($fieldInfo)) {
			foreach($incident->Threads as $thread) {

				//setup the name
				$custName = $thread -> Contact -> Name -> First . ' ' . $thread -> Contact -> Name -> Last;
				$nameFld = " ";
				switch ($thread->EntryType->ID) {
					case ENTRY_CUST_PROXY :

					//display nae
						$nameFld = sprintf('%s %s', getMessage(CUSTOMER_ENTERED_BY_LBL), $thread -> Account -> DisplayName);
						break;
					case ENTRY_STAFF :

					case ENTRY_RNL :
						$nameFld = $thread -> Account -> DisplayName;
						break;
					case ENTRY_CUSTOMER :
						if(getConfig(intl_nameorder, 'COMMON')) //5=ln, 4=fn
							$nameFld = rtrim(sprintf('%s %s', $thread -> Contact -> Name -> Last, $thread -> Contact -> Name -> First));
						else
							$nameFld = rtrim(sprintf('%s %s', $thread -> Contact -> Name -> First, $thread -> Contact -> Name -> Last));
						break;
					case ENTRY_RULE_RESP :
						unset($name);
						break;
					case ENTRY_NOTE :
						break;
				}

				//setup the thread display
				$fieldInfo -> value[] = array('content' => $thread -> Text, 'time' => date($this -> get_dateTimeFormat(), $thread -> CreatedTime), 'type' => $thread -> EntryType -> LookupName, 'entry_type' => $thread -> EntryType -> ID, 'channel_label' => $thread -> Channel -> LookupName, 'name' => $nameFld);
			}
		}

		return $fieldInfo;
	}

	/**
	 * Changes the passed incident's custom field to read, saves the incident
	 * This is now being done in a pre_page_render hoook. FTSAI
	 */
	private function markIncidentRead(RNCPHP\Incident $incidentObj) {
		$incidentObj -> CustomFields -> isunread = 0;
		$incidentObj -> save();

		return true;

	}

	/**
	 * Updates an incident with additional or new data
	 *
	 * @param int $comp_id
	 * @param array $data
	 */
	private function updateInvestigation($comp_id, $data) {

		try {
			initConnectAPI();
		} catch (Exception $err) {
			print($err->getMessage());
			return false;
		}

		try {
    		$compObj = $this->get_compObj($comp_id);
            // use function from dr_model to get investigation by incident id
            $incident = $compObj->Incident;
     		$CI = get_instance();
            $CI->load->model('custom/dr_model');
            $investigation = $CI->dr_model->getInvestigationByIncidentComplaint($incident->ID, $comp_id);
            if (!$investigation) {
                $investigation = new RNCPHP\CO_Invest\Investigation();
                $investigation->incident_id = $incident;
            }
            foreach($data as $field) {
                switch ($field->name) {
    				case 'fattach' :
    					$investigation->FileAttachments = new RNCPHP\FileAttachmentArray();
    					foreach($field->value as $attachment) {
    						$fattach = new RNCPHP\FileAttachment();
    						$fattach->ContentType = $attachment->content_type;
    						$fattach->FileName = $attachment->userfname;
    						$fileLocation = '/tmp/' . $attachment->localfname;
    						$fattach->setFile($fileLocation);
    						$investigation->FileAttachments[] = $fattach;
    					}
	    				break;
                }
		    }
			$investigation->save();
			RNCPHP\ConnectAPI::commit();
		} catch (Exception $e) {
			$this->add_error($e->getMessage(), __LINE__);
			return false;
        }
        return true;
	}

    /**
	 * Updates an incident with additional or new data
	 *
	 * @param int $comp_id
	 * @param array $data
	 */
	private function updateIncident($comp_id, $data) {

		try {
			initConnectAPI();
			$this->log->debug("updateIncident called.");

			$compObj = $this -> get_compObj($comp_id);
			if($compObj === false) {
				$this->log->error("Cannot get comp object");
				return false;
			}
	
			$incident = $compObj -> Incident;
			if($incident === false) {
				$this->log->error("Cannot get incident");
				return false;
			}
			
			$this->log->debug("Got comp and incident.");
	
			//get agent name
			$nameAccesible = true;
			$CI = get_instance();
			try {
				$contact = RNCPHP\Contact::fetch($CI -> session -> getProfileData('c_id'));
			} catch (Exception $e) {
				$this->log->error("Cannot get contact data");
				$nameAccesible = false;
			}
			$agentName = $nameAccesible ? $contact -> LookupName : '';
	
			$threadIntroduction = "********** Response from ";
			$threadIntroduction .= $compObj -> Organization -> LookupName;
			// if(strlen($agentName) > 0) {
			// $threadIntroduction .= ' agent ' . $agentName;
			// }
			$threadIntroduction .= " **********\n\n";
	
			$this->log->debug('Parsing fields');
			$incident -> CustomFields = new RNCPHP\IncidentCustomFields();
			foreach($data as $name => $field) {
	            // not sure why all of a sudden getting error in php trace about
	            // Empty string found in bound parameter :cf227 in the 0 row of data. Should either be NULL or a non-zero length string
	            // adding the following line fixed it
	            //$field->value = ($field->value) ? $field->value : null;
	            // logmessage( $field );
	            $this->log->debug($name, null, print_r($field, true));
	            if ($field->value || $field->customType == EUF_DT_RADIO) {
					switch ($name) {
						case 'thread' :
							$incident -> Threads = new RNCPHP\ThreadArray();
							$incident -> Threads[0] = new RNCPHP\Thread();
							$incident -> Threads[0] -> EntryType = new RNCPHP\NamedIDOptList();
							$incident -> Threads[0] -> EntryType -> ID = ENTRY_STAFF;
							// Used the ID here. See the Thread object for definition
							$incident -> Threads[0] -> Text = $threadIntroduction . sprintf('%s', $field -> value);
							break;
						case 'bank_statuses' :
							$incident -> CustomFields -> bank_statuses = new RNCPHP\NamedIDLabel();
							$incident -> CustomFields -> bank_statuses -> ID = intval($field -> value);
							break;
						case 'bank_resolution' :
							$incident -> CustomFields -> bank_resolution = $field -> value;
							break;
						case 'company_status_1' :
							$incident -> CustomFields -> company_status_1 = new RNCPHP\NamedIDLabel();
							$incident -> CustomFields -> company_status_1 -> ID = intval($field -> value);
							break;
						case 'company_status_2' :
							$incident -> CustomFields -> company_status_2 = new RNCPHP\NamedIDLabel();
							$incident -> CustomFields -> company_status_2 -> ID = intval($field -> value);
							break;
						case 'cfpb_status' :
							$incident -> CustomFields -> cfpb_status = new RNCPHP\NamedIDLabel();
							$incident -> CustomFields -> cfpb_status -> ID = intval($field -> value);
							break;
						case 'comp_describe_relief' :
							$incident -> CustomFields -> comp_describe_relief = $field -> value;
							break;
						case 'comp_dollar_amount' :
							$incident -> CustomFields -> comp_dollar_amount = $field -> value;
							break;
						case 'comp_provide_a_response' :
							$incident -> CustomFields -> comp_provide_a_response = $field -> value;
							break;
						case 'comp_explanation_of_closure' :
							$incident -> CustomFields -> comp_explanation_of_closure = $field -> value;
							break;
                            
						// Eric Gottesman - 2014.12.26 - added company_public_comment
						case 'company_public_comment' :
							$incident -> CustomFields -> company_public_comment = $field -> value;
							break;
                                                // Eric Gottesman - 2015.02.11 - added company_comment_category - it's a menu, so needs this code:
						case 'company_comment_category' :
							$incident -> CustomFields -> company_comment_category = new RNCPHP\NamedIDLabel();
							$incident -> CustomFields -> company_comment_category -> ID = intval($field -> value);
							break;

						case 'cfpb_describe_relief' :
							$incident -> CustomFields -> cfpb_describe_relief = $field -> value;
							break;
						case 'cfpb_dollar_amount' :
							$incident -> CustomFields -> cfpb_dollar_amount = $field -> value;
							break;
						case 'cfpb_provide_a_response' :
							$incident -> CustomFields -> cfpb_provide_a_response = $field -> value;
							break;
						case 'cfpb_explanation_of_closure' :
							$incident -> CustomFields -> cfpb_explanation_of_closure = $field -> value;
							break;
						case 'redirect_explanation' :
							$incident -> CustomFields -> redirect_explanation = $field -> value;
							break;
						case 'company_no_dispute_filed' :
							$incident -> CustomFields -> company_no_dispute_filed = $field -> value;
							break;
						case 'added_to_case' :
							$incident -> CustomFields -> added_to_case = $field -> value;
							break;
						case 'agency_name' :
							$incident -> CustomFields -> agency_name = $field -> value;
							break;
						case 'alerted_cfpb_reason':
							$incident->CustomFields->alerted_cfpb_reason = new RNCPHP\NamedIDLabel();
							$incident->CustomFields->alerted_cfpb_reason->ID = intval($field->value);
							break;
						case 'fattach' :
							if(!empty($field->value)){
								try{
									$incident -> FileAttachments = new RNCPHP\FileAttachmentIncidentArray();
									foreach($field->value as $attachment) {
										$fattach = new RNCPHP\FileAttachmentIncident();
										$fattach -> ContentType = $attachment -> content_type;
										$fattach -> FileName = $attachment -> userfname;
										$fileLocation = '/tmp/' . $attachment -> localfname;
										$fattach -> setFile($fileLocation);
										$incident -> FileAttachments[] = $fattach;
									}
								} catch(Exception $e){
									$this->log->error("Line ({$e->getLine()}) - Error with attachments", null, $e->getMessage());
								}
							}
						break;
						default;
					}
		        }
			}
	
	        try {
	        	$this->log->debug("Saving incident.");
				$incident->save();
				$this->log->debug("Incident saved.");
			} catch (Exception $e) {
				$this->log->error("Line ({$err->getLine()}) - Error saving incident", null, $e->getMessage());
				return false;
			}
		} catch (Exception $e) {
			$this->log->error("Line ({$err->getLine()}) - Error updating incident", null, $err->getMessage());
			return false;
		}
		
		return true;
	}

	/**
	 *  Creates the columns array suitable for the grid2 widget
	 */
	private function get_columns() {
		$colId = 1;
		$order = $idx = 0;
		foreach($this->dbFields as $fieldStr) {
			$field = parseFieldName($fieldStr, true);
			$fieldInfo = getBusinessObjectField($field[0], $field[1]);
			if(is_object($fieldInfo)) {
				$this -> columns[] = array('heading' => $fieldInfo -> lang_name, 'data_type' => $fieldInfo -> data_type, 'col_id' => $idx,        //$colId + 1,
					'order' => $order + 1, 'width' => '15.77608', 'url_info' => '');
			}
			++$idx;
		}
		/*$this->columns[] = array(
		 'heading'	 => 'Action',
		 'data_type'	 => 8,
		 'col_id'	 => $idx,//$colId + 1,
		 'order'		 => $order + 1,
		 'width'		 => '15.77608',
		 'url_info'	 => ''
		 );*/
		return $this -> columns;
	}

	/**
	 * Retrieves and formats data for the grid2 widget
	 *
	 * @param array $complaints
	 */
	private function getGridData(array $complaints) {
		$data = array();

		foreach($complaints as $complaint) {
			$complaintArray = array();
			$refno = $complaint -> Incident -> ReferenceNumber;
			$product = $complaint -> Incident -> Product -> Name;
			$cat = $complaint -> Incident -> Category -> Name;

			foreach($this->get_dbFields() as $key => $fieldName) {
				$dbRequest = explode('.', $fieldName);
				$fieldInfo = $this -> getFieldValue($complaint -> ID, $dbRequest[1], false);
				//hack to display the second level of the menu
				//changed from index 1 => 0 to diplay level 1 items
				/*if($fieldInfo -> data_type == EUF_DT_HIERMENU) {
					$fieldInfo -> value = $fieldInfo -> value[0]['label'];
				}*/
				if($dbRequest[1] == 'ref_no') {
                    // set up html link for case detail
                    $incstatus = explode('/incstatus/', $this->uri->uri_string());
                    $incstatus = ($incstatus[1]) ? '/incstatus/' . $incstatus[1] : null;
					$fieldInfo -> value = '<a href="/app/instAgent/detail/comp_id/' . $complaint -> ID
                        . $incstatus . '" >' . $fieldInfo -> value . '</a>';
				}
                // if bank status = 'Pending info from company', modify html link to private message form
                /*else if($dbRequest[1] == 'c$bank_statuses') {
                    if($fieldInfo -> value == 'Pending info from company')
                        $complaintArray[0] = str_replace('detail', 'detail_dr', $complaintArray[0]);
                }*/
				$complaintArray[$key] = ($fieldInfo -> value === null) ? '' : $fieldInfo -> value;
			}
			//$complaintArray[isUnread = $complaint -> Incident -> CustomFields -> isunread;
			//$complaintArray[] = '<a href="/app/instAgent/detail/comp_id/' . $complaint->ID . '" >[Update]</a>';
			$complaintArray['isUnread'] = $complaint -> Incident -> CustomFields -> isunread;
			$data[] = $complaintArray;

		}
        return ($data);
	}

	/**
	 * Used to retrieve a complaint custom object.  This method provides security functionality and
	 * is the only appropriate way to retrieve this data.
	 *
	 * @param int $comp_id
	 */
	protected function get_compObj($comp_id) {
		if(is_null($comp_id) || !is_numeric($comp_id) || $comp_id < 1) {
			return false;
		}
		try {
			$compObj = RNCPHP\CO\ComplaintAgainstOrg::fetch($comp_id);
		} catch (Exception $e) {
			$this->log->error("Exception getting complaint object", null, $e->getMessage());
			$this -> add_error($e -> getMessage(), __LINE__);
			return false;
		}
		if(!$this -> verifyAgentAccessAllowed($compObj -> Organization -> ID)) {
			$this->log->error("Agent access denied to org ({$compObj->Organization->ID}).");
			header("Location: /app/error/error_id/4" . sessionParm());
			$this -> add_error("No Agent Credentials", __LINE__);
			return false;
		}
		if(!$this -> validateAgentIncAccess($compObj -> Incident -> ID)) {
			$this->log->error("Agent access denied to incident ({$compObj->Incident->ID}).");
			header("Location: /app/error/error_id/4" . sessionParm());
			$this -> add_error("Access Denied", __LINE__);
			return false;
		}
		return $compObj;
	}

	/**
	 * Retrieves all the ComplaintAgainstOrg objects associated with an org_id
	 */
	private function getOrgIncidents($orgId, $i_id = null) {
		if(!$this -> verifyAgentAccessAllowed($orgId)) {
			header("Location: /app/error/error_id/4" . sessionParm());
			$this -> add_error("No Agent Credentials", __LINE__);
			return false;
		}

		//setup and error handling
		$resultObjs = array();
		if(is_null($orgId) || !is_numeric($orgId)) {
			$this -> add_error("Invalid org id parameter", __LINE__);
			return false;
		}

		if(!is_null($i_id) && is_numeric($i_id) && $i_id > 0) {
			//find an incident w/ passed org and i_id
			$roql = sprintf('SELECT
								CO.ComplaintAgainstOrg
							FROM
								CO.ComplaintAgainstOrg
							WHERE
								CO.ComplaintAgainstOrg.Organization = %d
                                AND CO.ComplaintAgainstOrg.Incident = %d',
			//AND CO.ComplaintAgainstOrg.Incident.StatusWithType.Status.LookupName in (%s)
			$orgId, $i_id
			//, $this->get_incStatusToShowStr()
			);
		} else {

			//find all incidents w/ passed org id
			//$archivedroql = $this->archivedIncidents ? " AND CO.ComplaintAgainstOrg.Incident.c\$bank_statuses > 0 " : "";
			//$archivedroql = $this -> archivedIncidents ? " AND CO.ComplaintAgainstOrg.Incident.c\$bank_resolution != '' " : sprintf(" AND CO.ComplaintAgainstOrg.Incident.c\$bank_resolution is null
            //        AND CO.ComplaintAgainstOrg.Incident.StatusWithType.Status.LookupName in (%s)", $this -> get_incStatusToShowStr());
			//$archivedroql = $this -> archivedIncidents
            //    ? " AND CO.ComplaintAgainstOrg.Incident.c\$bank_resolution != '' "
            //    : sprintf(" AND CO.ComplaintAgainstOrg.Incident.StatusWithType.Status.LookupName in (%s)", $this -> get_incStatusToShowStr());
            // archive status is now dependent on only on incident status
            //$archivedroql = sprintf(" AND CO.ComplaintAgainstOrg.Incident.StatusWithType.Status.LookupName in (%s)", $this -> get_incStatusToShowStr());
            // changing from incident status to c$bank_statuses
            $archivedroql = sprintf(" AND CO.ComplaintAgainstOrg.Incident.c\$bank_statuses in (%s)", $this -> get_incStatusToShowMenuIdStr());
			$roql = sprintf('SELECT
								CO.ComplaintAgainstOrg
							FROM
								CO.ComplaintAgainstOrg
							WHERE
								CO.ComplaintAgainstOrg.Organization = %d
								%s', $orgId, $archivedroql);
		}

		try {
			$result = RNCPHP\ROQL::queryObject($roql) -> next();
		} catch (Exception $e) {
			$this -> add_error($e -> getMessage(), __LINE__);
			$this->add_error( $roql, __LINE__);
			return false;
		}
		while($cao = $result -> next()) {
			$resultObjs[] = $cao;
		}

		return $resultObjs;

	}

	/**
	 * Security function to verify that the agent has an org id, and is_inst_agent = true
     * T. Woodham (8/16/2012): Extended to allow access to government portal users.
	 *
	 * This is used for allowing access to a list of incidents.
     * Make public so other models can also use.
     *
     * @param integer org_id
	 */
	public function verifyAgentAccessAllowed($orgId = null) {
		if(is_null($orgId) || $orgId < 1) {
			return false;
		}

        if( in_array( $this->ContactPermissions_model->userType(), array( getSetting( 'GOVERNMENT_PORTAL_STATE_AGENT_USER' ), getSetting( 'GOVERNMENT_PORTAL_FEDERAL_AGENT_USER' ) ) ) )
        {
            return true;
        }

        $CI = get_instance();
		try {
			$contact = RNCPHP\Contact::fetch($CI -> session -> getProfileData('c_id'));
		} catch (Exception $e) {
			$this -> add_error("Unable to access db", __LINE__);
			return false;
		}

		$isAgent = $contact -> CustomFields -> is_inst_agent;
		if(is_null($isAgent) || $isAgent == 0) {
			$this -> add_error("No Agent Credentials", __LINE__);
			return false;
		}

        $contactOrgID = $CI -> session -> getProfileData('org_id');
		if(is_numeric($contactOrgID) && $contactOrgID > 0) {
			if($contactOrgID == $orgId) {
				return true;
			}
			$this -> add_error("Invalid org for agent", __LINE__);
			return false;
		}
		$this -> add_error("Unable to Validate", __LINE__);

        return false;
	}

	/**
	 * Security function to verify that the agent may access the requested incident
     * T. Woodham (8/16/2012): Extending for use in Government Portal.
	 * Make public so other model can use.
     *
	 * @param unknown_type $i_id
	 */
	public function validateAgentIncAccess($i_id) {
		$CI = get_instance();
		$contactOrgID = $CI -> session -> getProfileData('org_id');

        if( in_array( $this->ContactPermissions_model->userType(), array( getSetting( 'GOVERNMENT_PORTAL_STATE_AGENT_USER' ), getSetting( 'GOVERNMENT_PORTAL_FEDERAL_AGENT_USER' ) ) ) )
        {
            try {
            // TODO move this code into its own function and also have the incident_model call this to peform the agent state matching logic.
            // we need to also check if org state jurisdiction is not null, then that the incident consumer state must match
            initConnectAPI();
            $orgObj = RNCPHP\Organization::fetch($contactOrgID);
            $orgType = $orgObj->CustomFields->org_type->LookupName; // i.e. Regulatory Agency
            $stateJurisdiction = $orgObj->CustomFields->state_jurisdiction->LookupName; // i.e. AL
            $incObj = RNCPHP\Incident::fetch($i_id);
            $consumerState = $incObj->CustomFields->consumer_state; // i.e. AL
            if ($orgType === "Regulatory Agency" && ($stateJurisdiction === null || $stateJurisdiction === $consumerState))
                return true;

            } catch (RNCPHP\ConnectAPIError $err) {
                $msg = "Error Generated ::" . $err -> getCode() . "::" . $err -> getMessage();
                die($msg);
            }
        }

		if(is_numeric($contactOrgID) && $contactOrgID > 0) {
			$incidentArr = $this -> getOrgIncidents($contactOrgID, $i_id);
			if(count($incidentArr) > 0) {
				return true;
			}
		}
		$this -> add_error("Unable to Validate", __LINE__);
		return false;
	}

	/*********************************Accessor Methods*********************************/

	/**
	 * Provides a mapping between the database field name and the PHP Connect value.  Uses the data
	 * in the fieldLookup array to determine the correct connect values.  Traverses objects to retrieve
	 * the data.
	 *
	 * @param string $dbFieldName
	 * @param compObject $objectToSearch
	 */
	private function getConnectFieldFromDbField($dbFieldName, $objectToSearch) {
		if(isset($this -> fieldLookup[$dbFieldName])) {
			$connectFieldName = $this -> fieldLookup[$dbFieldName];
			if(strlen($connectFieldName) < 1) {
				return $objectToSearch;
			}
			$objHier = split('->', $connectFieldName);

			$returnObj = $objectToSearch;
			for($i = 0; $i < count($objHier); $i++) {
				//lazy loading doesn't work unless we access the variables in some tangible manner, wtf?
				sprintf("%s", print_r($returnObj, true));
				$returnObj = $returnObj -> $objHier[$i];
			}

			return $returnObj;
		} else {
			return false;
		}
	}

	/**
	 * Get accessor for errors
	 */
	public function get_errors() {
		return $this -> errors;
	}

	/**
	 *  Set-accessor for $this->errors
	 *  @var errors
	 */
	public function add_error($error, $line) {
		$this -> errors[] = 'line: ' . $line . ' Error: ' . $error;
	}

	/**
	 *  Get-accessor for $this->dbFields
	 */
	public function get_dbFields() {
		return $this -> dbFields;
	}

	/**
	 *  Set-accessor for $this->dbFields
	 *  @var dbFields
	 */
	public function set_dbFields($dbFields) {
		$this -> dbFields = $dbFields;
	}

	/**
	 *  Get-accessor for $this->incStatusToShow
	 */
	public function get_incStatusToShow() {
		return $this -> incStatusToShow;
	}

	/**
	 *  Set-accessor for $this->incStatusToShow
	 *  @var incStatusToShow
	 */
	public function set_incStatusToShow($incStatusToShow) {
		$this -> incStatusToShow = $incStatusToShow;
	}

	/**
	 *  Set-accessor for $this->incStatusToShow
     *  converts menu names to ids
	 *  @var incStatusToShow
	 */
    public function set_incStatusToShowMenuId($incStatusToShow, $cf_name) {
        $sql_cf_id = sprintf("SELECT cf_id FROM custom_fields WHERE col_name = '%s'", $cf_name);
        $cf_id = sql_get_int($sql_cf_id);
        foreach ($incStatusToShow as $i => $name) {
            $sql = sprintf("SELECT m.id FROM menu_items m, labels l
                WHERE m.id = l.label_id AND m.cf_id = %d AND l.label = '%s'
                  AND l.lang_id = 1 AND l.fld = 1 AND l.tbl = 20", $cf_id, $name);
            $id_list[] = sql_get_int($sql);
        }
        $this -> incStatusToShow = $id_list;
    }

	/**
	 *  Set-accessor for $this->listType
	 *  @var archived
	 */
	public function set_listType($type) {
		return $this -> listType = $type;

	}

    /**
	 *  Set-accessor for $this->archivedIncidents
	 *  @var archived
	 */
	public function set_archivedIncidents($archived) {
		return $this -> archivedIncidents = $archived;

	}

	private function get_incStatusToShowStr() {
		return "'" . implode("', '", $this -> get_incStatusToShow()) . "'";
	}

	private function get_incStatusToShowMenuIdStr() {
		return implode(",", $this -> get_incStatusToShow());
	}

	/**
	 *  Get-accessor for $this->dateFormat
	 */
	public function get_dateFormat() {
		return $this -> dateFormat;
	}

	/**
	 *  Set-accessor for $this->dateFormat
	 *  @var dateFormat
	 */
	public function set_dateFormat($dateFormat) {
		$this -> dateFormat = $dateFormat;
	}

	/**
	 *  Get-accessor for dateTime format
	 */
	public function get_dateTimeFormat() {
		return $this -> dateFormat . ' ' . $this -> timeFormat;
	}

}
