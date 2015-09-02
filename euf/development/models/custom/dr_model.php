<?php
use RightNow\Connect\v1 as RNCPHP;

/**
 * This class provides functionality to allow private communication between institutional agents 
 * and cfpb representatives
 * touching to overwrite shiloh's changes in production
 *
 * @author frank.tsai
 *
 */
class dr_model extends Model {
	/**
	 * Used for debugging.  Examine for error information
	 *
	 * @var unknown_type
	 */
	private $errors = array();

	private $dateFormat = "m/d/Y";
	private $timeFormat = "H:i";

	function __construct() {
		require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
                initConnectAPI();
		parent::__construct();
	}

	/*********************************Public Methods*********************************/

     /**
     * Creates an empty instance of the directedReq$message middle layer object
     * @return Incident Empty directedReq$message object
     */
    function getBlank()
    {
        // TODO cache blank directedReq$messages
        /*
        $blankIncident = checkCache('incidentblank');
        if($blankIncident !== null)
            return $blankIncident;
        */

        $obj = new RNCPHP\directedReq\message();
        //setCache('incidentblank', $incident);
        
        return $obj;
    }

    /**
     * Return custom object metadata
     */
    function getMeta() {
        $meta = RNCPHP\directedReq\message::getMetadata();
        
        return $meta;
    }

    /**
	 * Public method to get the investigation object by incident id
	 *
	 * @param string $incId complaint object id
	 */
	public function getInvestigationByIncidentComplaint($incId, $compId) {
        $resultObjs = $this->getInvByIncId($incId, $compId);

        return $resultObjs;
	}

	/**
	 * Public method to get attachments from message
	 *
	 * @param fileAttachment object
	 */
	public function getAttachments($fattachList) {
        $resultObjs = $this->getMessageAttachments($fattachList);

        return $resultObjs;
	}

	/**
	 * Public method to get the investigation messages
	 *
	 * @param string $invIid investigation id
	 */
	public function getMessages($invId, $visId) {
        $resultObjs = $this->getInvMessages($invId, $visId);

        return $resultObjs;
	}
    
        /**
         * Function to retreive custom object fattach information from the database.
         *
         * @param $fileID int The id of the custom object fattach to retrieve.
         * @param $invID int The id of thie investigation being viewed.
         *
         * @return String The admin url to which the user should be redirected. Could be null if the user doesn't pass the security checks.
         */
        function getInvAttach( $fileID, $invID )
        {
            /*
             * Before we return the admin URL for a file attachment, 3 questions must be answered:
             *
             * 1. Is the requested file part of the passed-in investigation?
             * 2. Is the contact logged in and authorized?
             * 3. Is the investigation attached to an incident that is routed to the same org as the logged-in contact?
             */
            initConnectAPI('priv_msg_addin', 'v7Sv1T0hj@cf');

            $CI = get_instance();
            $CI->load->model( 'custom/ContactPermissions_model' );
            $contactOrgID = $CI->ContactPermissions_model->getProfileOrganizationID();

            if( !isset( $contactOrgID ) )
            {
                // User wasn't logged in and part of an organization.
                logmessage( 'User was either not logged in or not affiliated with an organization.' );
                return false;
            }

            // Now that we've established the user is logged in, is the attachment part of the passed in investigation?
            try
            {
                $investigation = RNCPHP\CO_Invest\Investigation::fetch( $invID );
                $file = null; // RNCPHP\FileAttachment::fetch( $fileID );

                $roql = sprintf( 
                    "SELECT directedReq.message 
                     FROM directedReq.message 
                     WHERE directedReq.message.FileAttachments.ID = %d",
                     $fileID
                );
                $resultSet = RNCPHP\ROQL::queryObject( $roql );
                if( $result = $resultSet->next() )
                {
                    while( $message = $result->next() )
                    {
                        if( $message->investigation_id->ID !== $investigation->ID )
                        {
                            // The message to which this file is attached is not part of the investigation being viewed. Kick 'em out with extreme prejudice.
                            return false;
                        }
                        else
                        {
                            // We need to collect the file while we're here.
                            foreach( $message->FileAttachments as $fattach )
                            {
                                if( $fattach->ID == $fileID )
                                {
                                    $file = $fattach;
                                    break;
                                }
                            }
                        }
                    }
                }
                else
                {
                    // This is an error condition. Redirect the user to a permission denied page.
                    return false;
                }

                // Is the logged in user attached to the same org to which the incident is routed?
                $complaint = RNCPHP\CO\ComplaintAgainstOrg::first( sprintf( 'Incident.ID = %d', $investigation->incident_id->ID ) );
                if( $complaint->Organization->ID !== $contactOrgID )
                {
                    // Nope. Get 'em out of here.
                    return false;
                }
                return $file->getAdminURL();
            }
            catch( RNCPHP\ConnectAPIErrorBase $err )
            {
                logmessage( $err->getMessage() );
                return false;
            }
        }

	/**
	 * Used to process form innput.
	 *
	 * @param unknown_type $data
	 * @param unknown_type $comp_id
	 */
	public function sendForm($data, $comp_id) {
		// Test the security synchronization token to ensure that
		// it matches with the source side.  Return the error
		// condition indicating a token error.
		$formToken = $this->input->post('f_tok');
		if(isValidSecurityToken($formToken, 0) === false) {
			return array('status' => '-1', 'sessionParm' => sessionParm());
		}
        $status = array();
        $messageFields = array();
        foreach($data as $field) {
            switch ($field->table) {
                case 'directedReq$message' :
                    $messageFields[$field -> name] = $field;
                    break;
            }
        }

        if(count($messageFields) > 0) {
			$result = $this->insertMessage($messageFields, $comp_id);
			if($result === true) {
				$status['message'] = "Directed request updated";
				$status['status'] = 1;
			} else {
				$status['message'] = "Unable to update directed request";
			}
		} else {
			$status['message'] = "No Data to Update";
		}

		//If the current token being used will no longer be valid on the next submit, pass the new token back
		//to the client so they can resubmit
		if($newFormToken !== '') {
			$result['newFormToken'] = $newFormToken;
		}
		$status['sessionParm'] = sessionParm();

		return $status;

	}

	/*********************************Private Methods*********************************/

	/**
	 * Create a new directed request message entry
	 *
	 * @param int $investigation_id
	 * @param int $acct_id
	 * @param int $c_id
	 * @param array $data
	 */
	private function insertMessage($data, $comp_id) {
		try {
			initConnectAPI();
		} catch (Exception $err) {
			print($err->getMessage());
			return false;
		}
        
        $CI = get_instance();
        $CI->load->model('custom/instagent_model');

        // lets get the complaint object so we can find the incident
		$compObj = $CI->instagent_model->getComplaint($comp_id);
		if($compObj === false) {
			return false;
		}
		$incident = $compObj->Incident;
		if($incident === false) {
			return false;
		}

        // we need to set the incident status to "Information provided by company"
        $incident->CustomFields->bank_statuses = new RNCPHP\NamedIDLabel();
        $incident->CustomFields->bank_statuses->LookupName = "Information provided";
        //$incident->StatusWithType->Status = new RNCPHP\NamedIDOptList();
        //$incident->StatusWithType->Status->LookupName = "Information provided by company";
        //$incident->save();

        // now lets find the investigation object associated with this incident
        $investigation = $this->getInvByIncId($incident->ID, $comp_id);
        if($investigation === false) {
            return false;
        }
        
		//get agent name
		$nameAccesible = true;
		try {
			$contact = RNCPHP\Contact::fetch($CI->session->getProfileData('c_id'));
		} catch (Exception $e) {
			$this->add_error("Unable to access db", __LINE__);
			$nameAccesible = false;
		}
		$agentName = $nameAccesible ? $contact->LookupName : '';

		$threadIntroduction = "********** Response from ";
		$threadIntroduction .= $compObj->Organization->LookupName;
		// if(strlen($agentName) > 0) {
		// $threadIntroduction .= ' agent ' . $agentName;
		// }
		$threadIntroduction .= " **********\n\n";

        try {
            // setup new directedReq$message object.
	    	$message = $this->getBlank();
		    foreach($data as $field) {
    			switch ($field->name) {
    				case 'msgText':
    					$message->msgText = $threadIntroduction . sprintf('%s', $field->value);
    					break;
                    case 'fattach':
                        $message->FileAttachments = new RNCPHP\FileAttachmentArray();
			    		foreach($field->value as $attachment) {
				    		$fattach = new RNCPHP\FileAttachment();
    						$fattach->ContentType = $attachment->content_type;
	    					$fattach->FileName = $attachment->userfname;
		    				$fileLocation = '/tmp/' . $attachment->localfname;
			    			$fattach->setFile($fileLocation);
				        	$message->FileAttachments[] = $fattach;
    					}
                        break;
                }
            }
            // set message visibility to Institutional Agent (1)
            $messagePermissions = RNCPHP\directedReq\messagePermissions::fetch(1);
		    $message->visibility = $messagePermissions;
            // set message agent id
            $message->c_id = $contact;
            // set message investigation
            $message->investigation_id = $investigation;
			$message->save();
            
            $incident->save();
			
            RNCPHP\ConnectAPI::commit();
		} catch (Exception $e) {
			$this->add_error($e->getMessage(), __LINE__);
			return false;
		}
		return true;
	}

	/**
	 * Used to retrieve a investigation custom object by incident id.  
     * This method provides security functionality and
	 * is the only appropriate way to retrieve this data.
	 *
	 * @param int $incId
	 */
	private function getInvByIncId($incId, $compId) {
    
        if(is_null($incId) || !is_numeric($incId) || $incId < 1) {
			return false;
		}
        
        $roql = sprintf('
            SELECT CO_Invest.Investigation
            FROM CO_Invest.Investigation
            WHERE CO_Invest.Investigation.incident_id.ID = %d', $incId);
		try {
			$invObj = RNCPHP\ROQL::queryObject($roql)->next()->next();
            $compObj = RNCPHP\CO\ComplaintAgainstOrg::fetch($compId);
		} catch (Exception $e) {
			$this->add_error($e->getMessage(), __LINE__);
			return false;
		}
        
        $CI = get_instance();
        $CI->load->model('custom/instagent_model');
        // only require investigation when case is under review (bank_statuses: 'No response', 'Pending info from company')
        // get incident bank status
        $bank_status = $CI->instagent_model->getBusinessObjectField('incidents', 'c$bank_statuses', $compId)->value;
        if ($bank_status === "Pending info from company") { 
            if ($invObj->ID === null) {
	    		header("Location: /app/error/error_id/4" . sessionParm());
                $this->add_error("Investigation does not exist", __LINE__);
                return false;
            }
        }

        if(!$CI->instagent_model->verifyAgentAccessAllowed($compObj->Organization->ID)) {
			header("Location: /app/error/error_id/4" . sessionParm());
			$this->add_error("No Agent Credentials", __LINE__);
			return false;
		}
		if(!$CI->instagent_model->validateAgentIncAccess($incId)) {
			header("Location: /app/error/error_id/4" . sessionParm());
			$this->add_error("Access Denied", __LINE__);
			return false;
		}
		return $invObj;
	}

	/**
	 * Used to retrieve a investigation custom object.  This method provides security functionality and
	 * is the only appropriate way to retrieve this data.
	 *
	 * @param int $invId
	 */
	private function getInv($invId) {

        if(is_null($invId) || !is_numeric($invId) || $invId < 1) {
			return false;
		}
		try {
			$invObj = RNCPHP\CO_Invest\Investigation::fetch($invId);
		} catch (Exception $e) {
			$this->add_error($e->getMessage(), __LINE__);
			return false;
		}
        
		return $invObj;
	}

	/**
	 * Retrieves all attachments from private messages
	 */
	private function getMessageAttachments($fattachList) {
        $resultObjs = array();

        foreach($fattachList as $i => $fattach) {
            try {
                initConnectAPI('priv_msg_addin', 'v7Sv1T0hj@cf');
                $url = $fattach->getAdminURL();
            } catch (Exception $e) {
                $this->add_error($e->getMessage(), __LINE__);
                return false;
            }
            $resultObjs[$i]['id'] = $fattach->ID;
            $resultObjs[$i]['url'] = $url;
            $resultObjs[$i]['filename'] = $fattach->FileName;
            $resultObjs[$i]['type'] = $fattach->ContentType;
            $resultObjs[$i]['icon'] = getIcon($fattach->FileName);
            $resultObjs[$i]['size'] = getReadableFileSize($fattach->Size);
        }
        
        return $resultObjs;
	}

    /**
	 * Retrieves all private messages associated with an investigation by investigation_id
	 */
	private function getInvMessages($invId, $visId) {

        if(is_null($invId) || !is_numeric($invId) || $invId < 1) {
            return false;
        }
        
		//setup and error handling
		$resultObjs = array();
		
        $roql = sprintf('
            SELECT directedReq.message
			FROM directedReq.message
            WHERE directedReq.message.investigation_id = %d
              AND directedReq.message.visibility = %d
            ', $invId, $visId);
		try {
			$result = RNCPHP\ROQL::queryObject($roql)->next();
		} catch (Exception $e) {
			$this->add_error($e->getMessage(), __LINE__);
			return false;
		}
		while($cao = $result->next()) {
			$resultObjs[] = $cao;
		}
		
        return $resultObjs;

	}


	/**
	 *  Set-accessor for $this->errors
	 *  @var errors
	 */
	public function add_error($error, $line) {
		$this->errors[] = 'line: ' . $line . ' Error: ' . $error;
	}

	/**
	 *  Get-accessor for $this->dateFormat
	 */
	public function get_dateFormat() {
		return $this->dateFormat;
	}

	/**
	 *  Set-accessor for $this->dateFormat
	 *  @var dateFormat
	 */
	public function set_dateFormat($dateFormat) {
		$this->dateFormat = $dateFormat;
	}

	/**
	 *  Get-accessor for dateTime format
	 */
	public function get_dateTimeFormat() {
		return $this->dateFormat . ' ' . $this->timeFormat;
	}

}
