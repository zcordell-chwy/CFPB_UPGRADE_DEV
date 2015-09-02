<?

use RightNow\Connect\v1 as RNCPHP;

class ContactPermissions_model extends Model
{
    private $CI;

    function __construct()
    {
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI();
	parent::__construct();

        $this->CI = get_instance();

        $this->load->helper( 'config_helper' );
    }

    // Public functions

    /**
     * Function that returns the service products to which the logged-in contact may access.
     *
     * @return  ARRAY   2-D array of product IDs for which the contact has access. Will be an empty array if no limitation placed upon contact.
     */
    public function userProductAccess()
    {
        $contactID = $this->getProfileContactID();
        $products = array();

        // Is this person logged in? Bail out here if the answer is no.
        if( is_null( $contactID ) || !is_numeric( $contactID ) )
        {
            $products = false;
        }
        else
        {
            try
            {
                $contact = RNCPHP\Contact::fetch( $contactID );
                $productRole = isset( $contact->CustomFields->product_role ) ? explode( ';', $contact->CustomFields->product_role ) : array();
                foreach( $productRole as $role )
                {
                    $products[] = explode( ',', $role );
                }
            }
            catch( RNCPHP\ConnectApiErrorBase $err )
            {
                // Invalidate the array returned value and continue.
                $products = false;
            }
        }

        return $products;
    }

    /**
     * Function that returns the type of user currently logged into CP
     *
     * @return  STRING  Returns one of four string values: 'federal', 'state', 'company', or 'unauthorized'.
     */
    public function userType(){ 
        $userType = 'unauthorized';
        $organizationID = $this->getProfileOrganizationID();
        $contactID = $this->getProfileContactID();
		
		return $this->userTypeByContactIDandOrganizationID($contactID, $organizationID);
    }

    /**
     * Function that returns the state for which the logged in user has jurisdiction. Returns an empty string upon error.
     *
     * @return  STRING  The state jurisdiction of the logged-in contact's organization.
     */
    public function stateJurisdiction()
    {
        $this->CI->load->model( 'custom/Report_model2' );

        $organizationID = $this->getProfileOrganizationID();
        $state = '';

        // Is this person logged in? If so, do they part of an organization?
        if( is_null( $organizationID ) || !is_numeric( $organizationID ) )
        {
            // The identified organization ID is not something we can use in CPHP. Silently continue.
            ;
        }
        else
        {
            try
            {
                $organization = RNCPHP\Organization::fetch( intval( $organizationID ) );
                $state = $organization->CustomFields->state_jurisdiction->LookupName;
            }
            catch ( RNCPHP\ConnectApiErrorBase $err )
            {
                // Silently continue. If there's no organization or if there was an error, we won't return anything.
                ;
            }
        }

        return $state;
    }

    /**
     * Function on pre_report_get hook to set the state filter if a state portal user executing a government portal search report.
     */
    public function setStateFilter( $args )
    {

        switch($args['data']['reportId'])
        {
            case getSetting( 'GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID' ):
            case getSetting( 'GOVERNMENT_PORTAL_CASE_CLOSED_REPORT_ID' ):

                $state = $this->stateJurisdiction();
                if( $state != '' )
                {
                    ; // logmessage( $args );
                }

            break;
            default:
            break;
        }

    }

    // Private helper functions

    /**
     * Helper function that grabs the ID of the associated organization of a user logged in via CP.
     *
     * @return  INT The organization ID of the logged-in user. NULL is returned if the user is not logged in or if no organization is associated with the user.
     */
    function getProfileOrganizationID()
    {
        $organizationID = $this->CI->session->getProfileData( 'org_id' );
        if( is_null( $organizationID ) || !is_numeric( $organizationID ) )
        {
            $organizationID = null;
        }

        return $organizationID;
    }

    /**
     * Helper function that grabs the ID of the contact logged in via CP.
     *
     * @return  INT The contact ID of the logged-in user. NULL is returned if the user is not logged in.
     */
    function getProfileContactID()
    {
        $contactID = $this->CI->session->getProfileData( 'c_id' );
        if( is_null( $contactID ) || !is_numeric( $contactID ) )
        {
            $contactID = null;
        }

        return $contactID;
    }
	
	function getUserTypeByLogin($login_str=''){
		$contact = RNCPHP\Contact::first("login = '".$login_str."'");
		if($contact->Organization->ID){
			$organization = RNCPHP\Organization::fetch($contact->Organization->ID);
		}
		
		$contactId = empty($contact->ID)?0:$contact->ID;
		$organizationId = empty($organization->ID)?0:$organization->ID;
		
		return $this->userTypeByContactIDandOrganizationID($contactId, $organizationId);
	}
	
	/**
     * Function that returns the type of user currently logged into CP
     *
     * @return  STRING  Returns one of four string values: 'federal', 'state', 'company', or 'unauthorized'.
     */
    public function userTypeByContactIDandOrganizationID($contactId, $organizationId){
		$userType = 'unauthorized';
		$contactId = (int) $contactId;
		$organizationId = (int) $organizationId;

		// Is this person logged in? If so, do they part of an organization? Bail out here if the answer to either question is no.
		if(empty($organizationId) || empty($contactId)){
			return $userType;
		}

		// Now that we know they're logged in and have an organization associated with them, what type of user is this?
		try{
			$contact = RNCPHP\Contact::fetch( intval( $contactId ) );
			$organization = RNCPHP\Organization::fetch( intval( $organizationId ) );

			if( $contact->CustomFields->is_inst_agent &&
				$organization->CustomFields->org_type->LookupName == getSetting( 'GOVERNMENT_PORTAL_ORG_TYPE' ) &&
				$organization->CustomFields->state_jurisdiction->LookupName != '' )
			{
				$userType = 'state';

			}else if(
				$contact->CustomFields->is_inst_agent &&
				$organization->CustomFields->org_type->LookupName == getSetting( 'GOVERNMENT_PORTAL_ORG_TYPE' ) &&
				$organization->CustomFields->state_jurisdiction->LookupName == '' )
			{
				$userType = 'federal';

			}else if(
				$contact->CustomFields->is_inst_agent &&
				$organization->CustomFields->org_type->LookupName == getSetting( 'CONGRESSIONAL_PORTAL_ORG_TYPE' ) )
			{
				$userType = 'congressional';

			}else if( 
				$contact->CustomFields->is_inst_agent && (
				$organization->CustomFields->org_type->LookupName == getSetting( 'FINANCIAL_INSTITUTION_ORG_TYPE' ) ||
				$organization->CustomFields->org_type->LookupName == getSetting( 'OTHER_COMPANY_ORG_TYPE' ))
			){
				$userType = 'company';
			}

		}catch( RNCPHP\ConnectApiErrorBase $err ){

		}

		return $userType;
    }
}
