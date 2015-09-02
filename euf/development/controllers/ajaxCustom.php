<?php

class ajaxCustom extends ControllerBase
{
    //This is the constructor for the custom controller. Do not modify anything within
    //this function.
    function __construct()
    {
        parent::__construct();
        parent::_setClickstreamMapping(array("sendForm" => "instAgent_submit", ));
    }

    /**
     * Sample function for ajaxCustom controller. This function can be called by sending
     * a request to /ci/ajaxCustom/ajaxFunctionHandler.
     */
    function ajaxFunctionHandler()
    {
        $postData = $this->input->post('post_data_name');
        //Perform logic on post data here
        echo $returnedInformation;
    }

    function sendForm()
    {
        AbuseDetection::check($this->input->post('f_tok'));
        $data = json_decode($this->input->post('form'));
        if (!$data)
        {
            writeContentWithLengthAndExit(json_encode(getMessage(JAVASCRIPT_ENABLED_FEATURE_MSG)));
        }
        $comp_id = $this->input->post('comp_id');

        // Go ahead and process redirected case data if it's there.
        $this->load->model('custom/redirectedCase_model');
        $this->redirectedCase_model->sendForm($data, $comp_id);

        // detect if we need to insert into directedReq$messages custom object based on msgText
        foreach ($data as $key => $obj)
        {
            if ($obj->table === 'directedReq$message')
            {
                $model = "dr_model";
                break;
            }
            else
            {
                $model = "instagent_model";
            }
        }

        if ($model == "instagent_model")// check to see if we have cbo fields
        {
			$this->load->model("custom/cbo_field_model");

			if($comp_id)
			{
				$dc_id = $this->cbo_field_model->getComplaint($comp_id);
			}
            // detect if we need to handle any cbo fields. If so, send entire set of data over for processing]
			//OSG PS 140228-000012 added support for a complaint ID
            foreach ($data as $key => $obj)
            {
                if (isset($obj->is_cbo))
                {
                    // this data is for a CBO, so treat appropriately
					if($dc_id)
					{
						$results = $this->cbo_field_model->sendForm($data,$dc_id);
					}
					else
					{
						$results = $this->cbo_field_model->sendForm($data);

					}
                    break;
                }
            }
        }

        $this->load->model("custom/$model");
        $results = $this->$model->sendForm($data, $comp_id);
		
		if($model == "instagent_model" && isset($comp_id))
		{	
			$this->cbo_field_model->checkAttachments($comp_id);
		}
		
        echo json_encode($results);
    }

    function getInstAgentGridData()
    {
        $this->load->model('custom/instagent_model');
        $this->load->helper('label_helper');
        if ($this->input->post('incstatus') == "old")
        {
            $incStatusToShow = getSettingOld('ARCHIVE_INC_STATUS');
        }
        elseif ($this->input->post('incstatus') == "cfpb")
        {
            $incStatusToShow = getSettingOld('UNDER_REVIEW_INC_STATUS');
        }
        else
        {
            $incStatusToShow = getSettingOld('ACTIVE_INC_STATUS');
        }
        $this->instagent_model->set_incStatusToShowMenuId($incStatusToShow, 'c$bank_statuses');
        $sortDir = intval($this->input->post('sortDir')) - 1;
        $results = $this->instagent_model->getOrgIncidentsForGrid($this->input->post('sortCol'), $sortDir);
        $results['report_id'] = 176;
        $results['total_num'] = count($results['data']);
        $results['per_page'] = count($results['data']);
        echo json_encode($results);
    }

    function getReportData()
    {
        // validate form session
        // AbuseDetection::check($this->input->post('f_tok'));
        $this->load->helper('config_helper');

        $filters = $this->input->post('filters');
        $filters = json_decode($filters);
        $filters = get_object_vars($filters);

        $reportID = $this->input->post('report_id');
        $reportToken = $this->input->post('r_tok');
        $format = $this->input->post('format');
        $format = get_object_vars(json_decode($format));
        $this->load->model('custom/Report_model2');
        if ($filters['search'] == 1)
            $this->Report_model2->updateSessionforSearch();
        $results = $this->Report_model2->getDataHTML($reportID, $reportToken, $filters, $format);
	

        // logmessage( $results );
        if ($reportID == getSetting('CASE_ACTIVE_REPORT_ID') || $reportID == getSetting('CASE_REVIEW_REPORT_ID') || $reportID == getSetting('CASE_ARCHIVE_REPORT_ID'))
        {
            // We need to force some fields to display.
            for ($counter = 0; $counter < count($results['data']); $counter++)
            {
                // account number.
                $account = $results['data'][$counter][2];
                if (strlen($account) > 0)
                {
                    // We want to hit the middle of the field, but intval will truncate a float if the length is an odd number. Overshoot by one to compensate.
                    $chunkLength = intval(strlen($account) / 2) + 1;
                    $valueToken = str_split($account, $chunkLength);
                    $account = sprintf('%s<span class="rn_Hidden"></span>%s', $valueToken[0], $valueToken[1]);
                    $results['data'][$counter][2] = $account;
                }
            }
        }

        /*
         * This request cannot be cached because not all rules that define how the page is rendered are in the POST data:
         * User search preferences, such as the number of results per page, are stored in the contacts table.
         * The Ask a Question tab may be hidden if the user has not searched enough times.
         * The user's profile is updated when they do a search.
         */
        echo json_encode($results);
    }

    /**
     * Function to handle custom login capability. Extends returned data to provide dynamic redirect location.
     * NOTE: With this function, the CP_CONTACT_LOGIN_REQUIRED config must be set to false.
     * @return
     */
   function doLogin()
    {

        AbuseDetection::check();
        $this->load->model('standard/Contact_model');
        $this->load->model('custom/ContactPermissions_model');
        $this->load->helper('config_helper');

        $userID = $this->input->post('login');
        $password = $this->input->post('password');
        $sessionID = $this->session->getSessionData('sessionID');
        $widgetID = $this->input->post('w_id');
        $url = $this->input->post('url');
        $result = $this->Contact_model->doLogin($userID, $password, $sessionID, $widgetID, $url);

        // We may need to override the url location, which is determined by user type.
        switch( $this->ContactPermissions_model->userType() )
        {
            case 'federal' :
                $result['url'] = getSetting('GOVERNMENT_PORTAL_FEDERAL_URL');
                break;
            case 'state' :
                $result['url'] = getSetting('GOVERNMENT_PORTAL_STATE_URL');
                break;
            case 'congressional':
                $result['url'] = getSetting('CONGRESSIONAL_PORTAL_URL');
                break;
            case 'company' :
                $result['url'] = getSetting('COMPANY_PORTAL_URL');
                break;
            case 'unauthorized' :
            default :
                $result['url'] = getSetting('ERROR_PAGE_URL');
                break;
        }
        
        // Congressional Portal needs to add a disclaimer the user must agree to. Pass the user type back as well.
        $result['user_type'] = $this->ContactPermissions_model->userType();

        echo json_encode($result);
    } 

    /*
    function doLogin()
    {

        AbuseDetection::check();
        $this->load->model('standard/Contact_model');
        $this->load->model('custom/ContactPermissions_model');
        $this->load->helper('config_helper');

        $userID = $this->input->post('login');
        $password = $this->input->post('password');
        $sessionID = $this->session->getSessionData('sessionID');
        $widgetID = $this->input->post('w_id');
        $url = $this->input->post('url');
		$userType = $this->ContactPermissions_model->getUserTypeByLogin($userID);
		$acceptAndContinue = $this->input->post('acceptAndContinue');
		
		if(($userType == 'congressional') && !$acceptAndContinue){
			$result = array('w_id' => $widgetID,
							'success' => 0,
							'url' => getSetting('ERROR_PAGE_URL'),
							'userType' => 'congressional',
							'message' => getSetting("CONGRESSIONAL_ACCEPT_AND_CONTINUE_ERROR"));

		}else{

			$result = $this->Contact_model->doLogin($userID, $password, $sessionID, $widgetID, $url);
			$result['userType'] = $this->ContactPermissions_model->userType();

			 // We may need to override the url location, which is determined by user type.
			switch( $result['userType'] )
			{
				case 'federal' :
					$result['url'] = getSetting('GOVERNMENT_PORTAL_FEDERAL_URL');
					break;
				case 'state' :
					$result['url'] = getSetting('GOVERNMENT_PORTAL_STATE_URL');
					break;
				case 'congressional':
					$result['url'] = getSetting('CONGRESSIONAL_PORTAL_URL');
					break;
				case 'company' :
					$result['url'] = getSetting('COMPANY_PORTAL_URL');
					break;
				case 'unauthorized' :
				default :
					$result['url'] = getSetting('ERROR_PAGE_URL');
					break;
			}
		}

        echo json_encode($result);
    }
    */
}
