<?php

use RightNow\Connect\v1 as RNCPHP;
require_once( 'instagent_model.php' );

class InboundReferral_model extends InstAgent_model
{
    function __construct()
    {
        parent::__construct();

        // CPHP initialization.
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI( 'priv_msg_addin', 'v7Sv1T0hj@cf' );

        $this->load->model( 'custom/ContactPermissions_model' );
        $this->load->helper( 'label_helper' );
    }

    /**
     * Function to return the incident related to a passed-in InboundReferral ID.
     *
     * @param int $refer_id
     * @return object
     */
    public function getIncident( $refer_id )
    {
        if( !$this->validObjectIdFormat( $refer_id ) )
            return false;

        try
        {
            $incident = null;
            $inboundReferral = RNCPHP\Portal\InboundReferral::fetch( $refer_id );
            logmessage( sprintf( "InboundReferral ID: %s", $inboundReferral->ID ) );

            if( $inboundReferral && $inboundReferral->Incident )
                $incident = $inboundReferral->Incident;
        }
        catch( RNCPHP\ConnectAPIErrorBase $err )
        {
            $this->add_error( $err->getMessage(), __LINE__ );
            return false;
        }

        return $incident;
    }

    /**
     * Function to determine if the case is an admin status.
     *
     * @param int $refer_id
     * $return bool
     */
    public function isIncidentInAdminResponse( $refer_id )
    {
        if( !$this->validObjectIdFormat( $refer_id ) )
            return true;

        $adminResponse = false;

        try
        {
            $incident = $this->getIncident( $refer_id );
            if( $incident )
            {
                logmessage( sprintf( "comp_status_archive value: '%s'", $incident->CustomFields->comp_status_archive->LookupName ) );
                $statusArray = getLabel( 'CO_STATUS_ARRAY' );

                switch( $incident->CustomFields->comp_status_archive->LookupName )
                {
                    case $statusArray['CO_STATUS_INCORRECT_COMPANY']:
                    case $statusArray['CO_STATUS_DUPLICATE_CASE']:
                    case $statusArray['CO_STATUS_SENT_TO_REGULATOR']:
                    case $statusArray['CO_STATUS_ALERTED_CFPB']:
                    case $statusArray['CO_STATUS_REDIRECTED']:
                        $adminResponse = true;
                    break;
                    default:
                        $adminResponse = false;
                    break;
                }
            }
        }
        catch( RNCPHP\ConnectAPIErrorBase $err )
        {
            $this->add_error( $err->getMessage(), __LINE__ );
            logmessage( $err->getMessage() );
            return true;
        }

        return $adminResponse;
    }

    /**
     * Function to determine if the currently logged-in user is allowed to view the identified InboundReferral object.
     *
     * @param int $comp_id
     * @return boolean
     */
    public function isAuthorized( $refer_id )
    {
		if( !$this->validObjectIdFormat( $refer_id ) )
			return false;

        try
        {
            $authorized = false;
            $orgId = $this->ContactPermissions_model->getProfileOrganizationID();

            if( $orgId )
            {
                $inboundReferral = RNCPHP\Portal\InboundReferral::fetch( $refer_id );

                if(
                    $inboundReferral->Status->LookupName === getSetting( 'ACTIVE_INBOUND_REFERRAL') &&
                    $inboundReferral->Organization->ID === $orgId
                )
                {
                    $authorized = true;
                }
            }
        }
        catch( RNCPHP\ConnectAPIErrorBase $err )
        {
            $this->add_error( $err->getMessage(), __LINE__ );
            return false;
        }

        return $authorized;
    }

    /**
     * Function to return all InboundReferral objects associated with a given incident. Objects returned must be in an Active/Open status and
     * related to a valid Organization object.
     *
     * @param int $incident_id
     * @return array
     */
    public function getActiveReferrals( $incident_id )
    {
        $activeReferrals = array();

        if( !$this->validObjectIdFormat( $incident_id ) )
            return $activeReferrals;

        try
        {
            $incident = RNCPHP\Incident::fetch( $incident_id );
            $activeReferrals = RNCPHP\Portal\InboundReferral::find( sprintf( "Incident.ID = %d AND Status.LookupName = '%s' AND Organization.ID > 0", $incident->ID, getSetting( 'ACTIVE_INBOUND_REFERRAL' ) ) );
        }
        catch( RNCPHP\ConnectAPIErrorBase $err )
        {
            $this->add_error( $err->getMessage(), __LINE__ );
            return false;
        }

        return $activeReferrals;
    }

	/**
	 * Retrieves all attachments from Job objects. Borrowed from dr_model.
	 */
	public function getAttachments( $fattachList )
    {
        $resultObjs = array();

        try
        {
            foreach( $fattachList as $i => $fattach )
            {
                $url = $fattach->getAdminURL();
                $resultObjs[$i]['url'] = $url;
                $resultObjs[$i]['filename'] = $fattach->FileName;
                $resultObjs[$i]['type'] = $fattach->ContentType;
                $resultObjs[$i]['icon'] = getIcon($fattach->FileName);
                $resultObjs[$i]['size'] = getReadableFileSize($fattach->Size);
            }
        }
        catch (Exception $e)
        {
            // Silently continue. We'll return an empty array.
            ;
        }

        return $resultObjs;
	}

	/**
	 * Over-ridden function from InstAgent_model. Used to retrieve a complaint custom object through the new Portal/InboundReferral object.
	 *
	 * @param int $comp_id
	 */
    protected function get_compObj( $refer_id )
    {
		if( !$this->validObjectIdFormat( $refer_id ) )
			return false;

		try
        {
			$inboundReferralObj = RNCPHP\Portal\InboundReferral::fetch( $refer_id );

            if ( !$this->isAuthorized( $inboundReferralObj->ID ) )
            {
                logmessage( "User not authorized." );
                header( sprintf( "Location: /app/error/error_id/4%s", sessionParm() ) );
                $this->add_error( "Not authorized", __LINE__ );
                return false;
            }
		}
        catch( Exception $e )
        {
			$this->add_error( $e->getMessage(), __LINE__ );
			return false;
		}

		return $inboundReferralObj;
    }

    /**
     * Helper function to validate if a passed-in value is of the correct format to be a RNT business object ID.
     *
     * @param int $obj_id
     * @return boolean
     */
    private function validObjectIdFormat( $obj_id )
    {
		if( is_null( $obj_id ) || !is_numeric( $obj_id ) || $obj_id < 1 )
        {
            $this->add_error( "Invalid object ID format.", __LINE__ );
			return false;
		}
        else
        {
            return true;
        }
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
			try {
				$compObj = RNCPHP\Portal\InboundReferral::fetch($comp_id);

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
}
