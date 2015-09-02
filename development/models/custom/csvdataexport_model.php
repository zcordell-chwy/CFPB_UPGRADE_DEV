<?

use RightNow\Connect\v1 as RNCPHP;
require_once( 'dataexportbase_model.php' );

class CsvDataExport_model extends DataExportBase_model
{
    protected $fh;
    protected $userType;

    protected $contactCache = array();

    function __construct()
    {
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        parent::__construct();

        $this->fileType = 'CSV';
        //$this->CI =& get_instance();
        $this->load->model( 'custom/Report_model2' );
        $this->load->model( 'custom/ContactPermissions_model' );
        $this->fh = null;
        $this->load->helper( 'config_helper' );
        $this->load->helper( 'label_helper' );
    }

    /**
     * Private function to make sure everything is fully initialized.
     *
     * @return  BOOL    Boolean indicating whether or not the export path has been propperly initialized.
     */
    private function _isInitialized()
    {
        // Not checking timestamp and file location since they're set in the base model's controller.
        // File name and data type will be set in individual export functions.
        if( isset( $this->fileType ) && isset( $this->organizationID ) && isset( $this->worksheetName ) )
            return true;
        else
            return false;
    }

    /**
     * Private function to identify which report should be used to generate the export.
     *
     * @return  INT The ID of the report to be used.
     */
    private function _reportID()
    {
        /*
         * 9/21/2012: Company portal will require different reports, so we need to revise this somewhat.
         *            The report ID will now be passed in, and this function will validate that it's a legal
         *            report for the type of user. If it's valid, we let them through. Otherwise, stop everything
         *            now.
         */

        if( $this->ContactPermissions_model->userType() == 'company' && $this->reportID == getSetting( 'CASE_EXPORT_REPORT_ID' ) )
        {
            return getSetting( 'CASE_EXPORT_REPORT_ID' );
        }
        else if( $this->ContactPermissions_model->userType() == 'state' && $this->reportID == getSetting( 'GOVERNMENT_PORTAL_ACTIVE_CASE_EXPORT_REPORT_ID' ) )
        {
            return getSetting( 'GOVERNMENT_PORTAL_ACTIVE_CASE_EXPORT_REPORT_ID' );
        }
        else if( ( $this->ContactPermissions_model->userType() == 'state' || $this->ContactPermissions_model->userType() == 'federal' ) && $this->reportID == getSetting( 'GOVERNMENT_PORTAL_CLOSED_CASE_EXPORT_REPORT_ID' ) )
        {
            return getSetting( 'GOVERNMENT_PORTAL_CLOSED_CASE_EXPORT_REPORT_ID' );
        }
        else
        {
            throw new Exception( sprintf( 'Invalid report ID. (%s)', $this->reportID ) );
        }
    }

    /**
     * Since we're on an 11.2 site, we have to use the report model to execute a report. Upgrades will require
     * this section to be switched out for the correct CPHP call.
     *
     * @return  ARRAY   Returns the output of the executed report.
     */
    private function _executeReport()
    {
        $reportFilterArray = array();
        $reportFilterArray['page'] = $this->pageNumber; // getSetting( 'CASE_DATA_EXPORT_DEFAULT_PAGE' );
        $reportFilterArray['per_page'] = getSetting( 'CASE_DATA_EXPORT_NUM_PER_PAGE' );
        $reportID = $this->_reportID(); // getSetting( 'CASE_EXPORT_REPORT_ID' );

        // dynamically get filters
        $searchFilters = $this->Report_model2->getRuntimeFilters($reportID);
        // echo"searchFilters<pre>";print_r($searchFilters);echo"</pre>";exit;

        // Loop through all the report filters, applying filters to the report.
        if( $this->ContactPermissions_model->userType() == 'company' )
        {
            $filterInfo = $this->Report_model2->getSearchFilterInfo($searchFilters, 'CO$ComplaintAgainstOrg.Organization');
            $reportFilter = $this->Report_model2->createSearchFilter(
                $reportID,
                $filterInfo['name'],
                $filterInfo['fltr_id'],
                $this->organizationID,
                'customSearch',
                OPER_EQ
            );
            $reportFilterArray[$filterInfo['name']] = $reportFilter;
        }
        else
        {
            // Government Portal users don't filter by org, but could filter by state.
            $filterInfo = $this->Report_model2->getSearchFilterInfo($searchFilters, 'incidents.c$consumer_state');
            $reportFilter = $this->Report_model2->createSearchFilter(
                $reportID,
                $filterInfo['name'],
                $filterInfo['fltr_id'],
                $this->consumer_state,
                'filterDropdown',
                OPER_EQ
            );
            $reportFilterArray[$filterInfo['name']] = $reportFilter;
        }

        // Product and category filters.
        if( isset( $this->product ) )
        {
            $filterInfo = $this->Report_model2->getSearchFilterInfo($searchFilters, 'incidents.prod_hierarchy');
            $productFilter = explode( ',', $this->product );
            // logmessage( sprintf( "Product filter info (%s) (%s): %s", $this->product, print_r( $productFilter, true ), print_r( $filterInfo, true ) ) );
            $reportFilterArray[$filterInfo['name']] = $this->Report_model2->createSearchFilter(
                $reportID,
                $filterInfo['name'],
                $filterInfo['fltr_id'],
                $productFilter, // $this->product,
                'menufilter',
                OPER_LIST
            );

            // Update this filter so it's data is under the '0' parameter instead of the 'val' parameter.
            $reportFilterArray[$filterInfo['name']]->filters->data->{0} = $reportFilterArray[$filterInfo['name']]->filters->data->val;
            unset( $reportFilterArray[$filterInfo['name']]->filters->data->val );
        }
        if( isset( $this->category ) )
        {
            $filterInfo = $this->Report_model2->getSearchFilterInfo($searchFilters, 'incidents.cat_hierarchy');
            $reportFilterArray[$filterInfo['name']] = $this->Report_model2->createSearchFilter(
                $reportID,
                $filterInfo['name'],
                $filterInfo['fltr_id'],
                $this->category,
                'menufilter',
                OPER_LIST
            );
        }
        if( isset( $this->month_of_cases ) )
        {
            $filterInfo = $this->Report_model2->getSearchFilterInfo($searchFilters, 'incidents.created');
            $reportFilterArray[$filterInfo['name']] = $this->Report_model2->createSearchFilter(
                $reportID,
                $filterInfo['name'],
                $filterInfo['fltr_id'],
                $this->month_of_cases,
                'customSearch',
                $filterInfo['oper_id']
            );
            // echo sprintf( "Month of cases set. <br />%s", print_r( $filterInfo, true ) );
        }

        // Search type and keyword filters.
        /*
        if( isset( $this->searchType ) )
        {
            $reportFilterArray[ getSetting( 'CASE_DATA_EXPORT_SEARCH_TYPE_FILTER_NAME' ) ] = $this->Report_model2->createSearchFilter(
                $reportID,
                getSetting( 'CASE_DATA_EXPORT_SEARCH_TYPE_FILTER_NAME' ),
                getSetting( 'CASE_DATA_EXPORT_SEARCH_TYPE_FILTER_ID' ),
                $this->searchType,
                'searchType',
                OPER_EQ
            );
        }
        if( isset( $this->keyword ) )
        {
            $reportFilterArray[ getSetting( 'CASE_DATA_EXPORT_KEYWORD_FILTER_NAME' ) ] = $this->Report_model2->createSearchFilter(
                $reportID,
                getSetting( 'CASE_DATA_EXPORT_KEYWORD_FILTER_NAME' ),
                getSetting( 'CASE_DATA_EXPORT_KEYWORD_FILTER_ID' ),
                $this->keyword,
                'keyword',
                OPER_EQ
            );
        }
        */

        // The keyword filter will be the value applied to one of several filters, depending upon the value of the searchType filter.
        /*
        switch( $this->searchType )
        {
            case getSetting( 'CASE_DATA_EXPORT_NAME_ACCOUNT_FILTER_NAME' ):
                $reportFilterArray[ getSetting( 'CASE_DATA_EXPORT_NAME_ACCOUNT_FILTER_NAME' ) ] = $this->Report_model2->createSearchfilter(
                    $reportID,
                    getSetting( 'CASE_DATA_EXPORT_NAME_ACCOUNT_FILTER_NAME' ),
                    getSetting( 'CASE_DATA_EXPORT_NAME_ACCOUNT_FILTER_ID' ),
                    $this->keyword,
                    OPER_REGEXIZE
                );
            break;
            case getSetting( 'CASE_DATA_EXPORT_ACCOUNT_NUMBER_FILTER_NAME' ):
                $reportFilterArray[ getSetting( 'CASE_DATA_EXPORT_ACCOUNT_NUMBER_FILTER_NAME' ) ] = $this->Report_model2->createSearchFilter(
                    $reportID,
                    getSetting( 'CASE_DATA_EXPORT_ACCOUNT_NUMBER_FILTER_NAME' ),
                    getSetting( 'CASE_DATA_EXPORT_ACCOUNT_NUMBER_FILTER_ID' ),
                    $this->keyword,
                    OPER_LIKE
                );
            break;
            case getSetting( 'CASE_DATA_EXPORT_COMPANY_NAME_FILTER_NAME' ):
                $reportFilterArray[ getSetting( 'CASE_DATA_EXPORT_COMPANY_NAME_FILTER_NAME' ) ] = $this->Report_model2->createSearchFilter(
                    $reportID,
                    getSetting( 'CASE_DATA_EXPORT_COMPANY_NAME_FILTER_NAME' ),
                    getSetting( 'CASE_DATA_EXPORT_COMPANY_NAME_FILTER_ID' ),
                    $this->keyword,
                    OPER_REGEXIZE
                );
            break;
            default:
            break;
        }
        */

        // Allowing searchType to be dynamic
        if( isset( $this->searchType ))
        {
            $filterInfo = $this->Report_model2->getSearchFilterInfo($searchFilters, $this->searchType);
            $reportFilterArray[$filterInfo['name']] = $this->Report_model2->createSearchFilter(
                $reportID,
                $filterInfo['name'],
                $filterInfo['fltr_id'],
                $this->keyword,
                'searchType',
                $filterInfo['oper_id']
            );
        }

        //echo"<pre>";print_r($reportFilterArray);var_dump($reportFilterArray);echo"</pre>";
        // logmessage( $reportFilterArray );
        $reportToken = createToken( $reportID );
        $results = $this->Report_model2->getDataHTML( $reportID, $reportToken, $reportFilterArray, array() );
        //echo sprintf( "<pre>Num results: %d</pre>", count( $results['data'] ) );exit;

        return $results;
    }

    private function _populateIncidentDataArray( $incident )
    {
        $data = array();
        $data[] = $incident->ID;
        // Incidents will only get one product, one category.
        $data[] = $incident->Product->Name;
        $data[] = $incident->Category->Name;
        $data[] = $incident->CustomFields->what_happened;
        $data[] = $incident->CustomFields->mort_this_is_about;
        $data[] = $incident->CustomFields->nbccestvalue;
        $data[] = $incident->CustomFields->dtccissuehappen;
        $data[] = $incident->CustomFields->rcontactedccissuer;
        $data[] = $incident->CustomFields->cmqillegaldiscrimination;
        $data[] = $incident->CustomFields->discrim_age;
        $data[] = $incident->CustomFields->discrim_marital;
        $data[] = $incident->CustomFields->discrim_national;
        $data[] = $incident->CustomFields->discrim_race;
        $data[] = $incident->CustomFields->discrim_pubassist;
        $data[] = $incident->CustomFields->discrim_religion;
        $data[] = $incident->CustomFields->discrim_sex;
        $data[] = $incident->CustomFields->discrim_exercise;
        $data[] = $incident->CustomFields->discrim_other;
        $data[] = $incident->CustomFields->fairresolution;
        $data[] = $incident->PrimaryContact->Name->First;
        $data[] = $incident->PrimaryContact->Name->Last;
        $data[] = isset( $incident->PrimaryContact->Emails[0] ) ? $incident->PrimaryContact->Emails[0]->Address : '';
        $data[] = $incident->CustomFields->ccmail_addr1;
        $data[] = $incident->CustomFields->ccmail_city;
        $data[] = $incident->CustomFields->ccmail_state->LookupName;
        $data[] = $incident->CustomFields->ccmail_zip;
        $data[] = $incident->CustomFields->onbehalf_myself;
        $data[] = $incident->CustomFields->onbehalf_someone;
        $data[] = $incident->CustomFields->amwasservicemember;
        $data[] = $incident->CustomFields->amdependent;
        $data[] = $incident->CustomFields->name_on_card;
        $data[] = $incident->CustomFields->rccnumber;
        $data[] = $incident->CustomFields->cc_co_name;
        $data[] = $incident->CustomFields->ccbill_addr1;
        $data[] = $incident->CustomFields->ccbill_addr2;
        $data[] = $incident->CustomFields->ccbill_city;
        $data[] = $incident->CustomFields->ccbill_state->LookupName;
        $data[] = $incident->CustomFields->ccbill_zip;
        $data[] = $incident->CustomFields->cc_issuer_addr1;
        $data[] = $incident->CustomFields->cc_issuer_addr2;
        $data[] = $incident->CustomFields->cc_issuer_city;
        $data[] = $incident->CustomFields->cc_issuer_state->LookupName;
        $data[] = $incident->CustomFields->cc_issuer_zip;
        $data[] = $incident->ReferenceNumber;
        $data[] = $incident->CustomFields->bank_statuses->LookupName;
        $data[] = $incident->CustomFields->sent_bank_date;
        $data[] = $incident->CustomFields->response_due;
        $data[] = $incident->CustomFields->bank_resolution;

        return $data;
    }

    private function _populateIncidentThreadDataArray( $incident, $thread )
    {
        $data = array();
        $data[] = $incident->ID;
        $data[] = $thread->Account->Name->First;
        $data[] = $thread->Account->Name->Last;
        $data[] = $thread->Channel->LookupName;
        $data[] = $thread->Contact->Name->First;
        $data[] = $thread->Contact->Name->Last;
        $data[] = isset( $thread->Contact->Emails[0] ) ? $thread->Contact->Emails[0]->Address : '';
        $data[] = $thread->CreatedTime;
        $data[] = $thread->DisplayOrder;
        $data[] = $thread->EntryType->LookupName;
        $data[] = $thread->Text;

        return $data;
    }

    private function _populateIncidentAttachmentDataArray( $incident, $attachment )
    {
        $data = array();
        $data[] = $incident->ID;
        $data[] = $attachment->ContentType;
        $data[] = strftime( '%m/%d/%Y %T', $attachment->CreatedTime );
        $data[] = $attachment->FileName;
        $data[] = $attachment->ID;
        $data[] = $attachment->Size;
        $data[] = strftime( '%m/%d/%Y %T', $attachment->UpdatedTime );
        $data[] = $attachment->Description;

        return $data;
    }

    private function _incidentHeaderRow()
    {
        $headers = array();
        $headers[] = getLabel( 'EXP_I_ID' );
        $headers[] = getLabel( 'EXP_PROD' );
        $headers[] = getLabel( 'EXP_CAT' );
        $headers[] = getLabel( 'EXP_WHAT_HAPPENED' );
        $headers[] = getLabel( 'EXP_MORTG_THIS_IS_ABOUT' );
        $headers[] = getLabel( 'EXP_NBCCESTVALUE' );
        $headers[] = getLabel( 'EXP_DTCCISSUEHAPPEN' );
        $headers[] = getLabel( 'EXP_RCONTACTEDCCISSUER' );
        $headers[] = getLabel( 'EXP_CMQILLEGALDISCRIMINATION' );
        $headers[] = getLabel( 'EXP_DISCRIM_AGE' );
        $headers[] = getLabel( 'EXP_DISCRIM_MARITAL' );
        $headers[] = getLabel( 'EXP_DISCRIM_NATIONAL' );
        $headers[] = getLabel( 'EXP_DISCRIM_RACE' );
        $headers[] = getLabel( 'EXP_DISCRIM_PUBASSIST' );
        $headers[] = getLabel( 'EXP_DISCRIM_RELIGION' );
        $headers[] = getLabel( 'EXP_DISCRIM_SEX' );
        $headers[] = getLabel( 'EXP_DISCRIM_EXERCISE' );
        $headers[] = getLabel( 'EXP_DISCRIM_OTHER' );
        $headers[] = getLabel( 'EXP_CONCERN_ABOUT_FORECLOSURE' );
        $headers[] = getLabel( 'EXP_MISSED_PAYMENT' );
        $headers[] = getLabel( 'EXP_IS_FORECLOSURE_SCHEDULED' );
        $headers[] = getLabel( 'EXP_DATE_SCHEDULED_FORECLOSURE' );
        $headers[] = getLabel( 'EXP_PAY_COMPANY_AVOID_FORECLOSURE' );
        $headers[] = getLabel( 'EXP_FAIRRESOLUTION' );
        //we need to concatenate columns on the report due to the 127 hard limit # of columns
	    //$headers[] = getLabel( 'EXP_CONTACT_SALUTATION');
        $headers[] = getLabel( 'EXP_CONTACTS_FIRST_NAME' ).'_MIDDLE_NAME';
        //$headers[] = getLabel( 'EXP_MIDDLE_NAME' );
        $headers[] = getLabel( 'EXP_CONTACTS_LAST_NAME' );
    	//$headers[] = getLabel( 'EXP_CONTACT_SUFFIX');
        $headers[] = getLabel( 'EXP_CONTACT_EMAIL' );
        $headers[] = getLabel( 'EXP_CONTACT_PHONE' );
        $headers[] = getLabel( 'EXP_CCMAIL_ADDR1' );
        $headers[] = getLabel( 'EXP_CCMAIL_CITY' );
        $headers[] = getLabel( 'EXP_CCMAIL_STATE' );
        $headers[] = getLabel( 'EXP_CCMAIL_ZIP' );
        $headers[] = getLabel( 'EXP_ONBEHALF_MYSELF' );
        $headers[] = getLabel( 'EXP_ONBEHALF_SOMEONE' );
        $headers[] = getLabel( 'EXP_AMWASSERVICEMEMBER' );
        $headers[] = getLabel( 'EXP_AMDEPENDENT' );
        $headers[] = getLabel( 'EXP_NAME_ON_CARD' );
        $headers[] = getLabel( 'EXP_RCCNUMBER' );
        $headers[] = getLabel( 'EXP_CC_CO_NAME' );
        $headers[] = getLabel( 'EXP_CCBILL_ADDR1' );
        $headers[] = getLabel( 'EXP_CCBILL_ADDR2' );
        $headers[] = getLabel( 'EXP_CCBILL_CITY' );
        $headers[] = getLabel( 'EXP_CCBILL_STATE' );
        $headers[] = getLabel( 'EXP_CCBILL_ZIP' );
        $headers[] = getLabel( 'EXP_CC_ISSUER_ADDR1' );
        $headers[] = getLabel( 'EXP_CC_ISSUER_ADDR2' );
        $headers[] = getLabel( 'EXP_CC_ISSUER_CITY' );
        $headers[] = getLabel( 'EXP_CC_ISSUER_STATE' );
        $headers[] = getLabel( 'EXP_CC_ISSUER_ZIP' );
        $headers[] = getLabel( 'EXP_REF_NO' );
        // End headers in all exports.

        if( in_array( $this->_reportID(), array( getSetting( 'CASE_EXPORT_REPORT_ID' ), getSetting( 'GOVERNMENT_PORTAL_CLOSED_CASE_EXPORT_REPORT_ID' ) ) ) )
            $headers[] = getLabel( 'EXP_BANK_STATUSES' );

        if( $this->_reportID() == getSetting( 'CASE_EXPORT_REPORT_ID' ) )
        {
            $headers[] = getLabel( 'EXP_SENT_BANK_DATE' );
            $headers[] = getLabel( 'EXP_RESPONSE_DUE' );
            $headers[] = getLabel( 'EXP_COMP_DESCRIBE_RELIEF' );
            $headers[] = getLabel( 'EXP_RELIEF_AMOUNT' );
            $headers[] = getLabel( 'EXP_COMP_PROVIDE_A_RESPONSE' );
            $headers[] = getLabel( 'EXP_COMP_EXPLATION_OF_CLOSURE' );
            $headers[] = getLabel( 'EXP_REDIRECT_EXPLANATION' );
            $headers[] = getLabel( 'EXP_CFPB_DESCRIBE_RELIEF' );
            $headers[] = getLabel( 'EXP_CFPB_DOLLAR_AMOUNT' );
            $headers[] = getLabel( 'EXP_CFPB_PROVIDE_A_RESPONSE' );
            $headers[] = getLabel( 'EXP_CFPB_EXPLANATION_OF_CLOSURE' );
            $headers[] = getLabel( 'EXP_DATE_RESOLVED' );
            $headers[] = getLabel( 'EXP_DATE_SECOND_RESPONSE' );
        }

        if( $this->_reportID() == getSetting( 'CASE_EXPORT_REPORT_ID' ) )
        {
            $headers[] = getLabel( 'EXP_DATE_BIRTH' );
            $headers[] = getLabel( 'EXP_SSN' );

            // 11/8/2012 (T. Woodham): These three columns should only be in the company portal export (per CR 322).
            $headers[] = getLabel( 'EXP_CONSUMER_FILED_DISPUTE' );
            $headers[] = getLabel( 'EXP_DISPUTE_NUMBER' );
            $headers[] = getLabel( 'EXP_COMPANY_NO_DISPUTE_FILED' );
        }

        if( $this->_reportID() == getSetting( 'GOVERNMENT_PORTAL_CLOSED_CASE_EXPORT_REPORT_ID' ) )
            $headers[] = getLabel( 'EXP_AGENCY_REFERRED' );

	// $TRANS FIELDS - added 11/16/2012 (A. Durrans)
	$headers[] = getLabel('EXP_CCMAIL_ADDR2');
	$headers[] = getLabel('EXP_CCMAIL_COUNTRY');
	$headers[] = getLabel('EXP_INCIDENT_CONTACT_AGE');
	$headers[] = getLabel('EXP_I_AM_THE');
	$headers[] = getLabel('EXP_THIS_PERSON_IS_THE');
	$headers[] = getLabel('EXP_CC_ISSUER_COUNTRY');
	$headers[] = getLabel('EXP_TRMETHOD');
	$headers[] = getLabel('EXP_SENDER_AGENT_CO');
	$headers[] = getLabel('EXP_SENDER_AGENT_ADDR1');
	$headers[] = getLabel('EXP_SENDER_AGENT_CITY');
	$headers[] = getLabel('EXP_SENDER_AGENT_STATE');
	$headers[] = getLabel('EXP_SENDER_AGENT_ZIP');
	$headers[] = getLabel('EXP_SENDER_AGENT_COUNTRY');
	$headers[] = getLabel('EXP_SENDER_AGENT_PHONE');
	$headers[] = getLabel('EXP_SENDER_AGENT_WEBSITE');
	//$headers[] = getLabel('EXP_SENDER_SALUTATION');
	$headers[] = getLabel('EXP_SENDER_FIRST_NAME').'_MIDDLE_NAME';
	//$headers[] = getLabel('EXP_SENDER_MIDDLE_NAME');
	$headers[] = getLabel('EXP_SENDER_LAST_NAME');
	//$headers[] = getLabel('EXP_SENDER_SUFFIX');
	$headers[] = getLabel('EXP_SENDER_ADDR1');
	$headers[] = getLabel('EXP_SENDER_ADDR2');
	$headers[] = getLabel('EXP_SENDER_CITY');
	$headers[] = getLabel('EXP_SENDER_STATE');
	$headers[] = getLabel('EXP_SENDER_ZIP');
	$headers[] = getLabel('EXP_SENDER_COUNTRY');
	$headers[] = getLabel('EXP_SENDER_PHONE');
	$headers[] = getLabel('EXP_SENDER_EMAIL');
	$headers[] = getLabel('EXP_AMT_TRANSFERRED');
	$headers[] = getLabel('EXP_AMT_TRANSFERRED_CURRENCY');
	$headers[] = getLabel('EXP_DATE_TRANSFERRED');
	$headers[] = getLabel('EXP_TRANSACTION_NUMBER');
	$headers[] = getLabel('EXP_FUNDS_PROMISED_DATE');
	$headers[] = getLabel('EXP_AMT_ERROR');
	$headers[] = getLabel('EXP_AMT_ERROR_CURRENCY');
	$headers[] = getLabel('EXP_DATE_ERROR');
	$headers[] = getLabel('EXP_RECEIPT_METHOD');
	$headers[] = getLabel('EXP_REC_AGENT_CO');
	$headers[] = getLabel('EXP_REC_AGENT_ADDR1');
	$headers[] = getLabel('EXP_REC_AGENT_CITY');
	$headers[] = getLabel('EXP_REC_AGENT_STATE');
	$headers[] = getLabel('EXP_REC_AGENT_ZIP');
	$headers[] = getLabel('EXP_REC_AGENT_COUNTRY');
	$headers[] = getLabel('EXP_REC_AGENT_WEBSITE');
	$headers[] = getLabel('EXP_REC_AGENT_ACCT_NUMBER');
	//$headers[] = getLabel('EXP_REC_SALUTATION');
	$headers[] = getLabel('EXP_REC_FIRST_NAME').'_MIDDLE_NAME';
	//$headers[] = getLabel('EXP_REC_MIDDLE_NAME');
	$headers[] = getLabel('EXP_REC_LAST_NAME');
	//$headers[] = getLabel('EXP_REC_SUFFIX');
	$headers[] = getLabel('EXP_REC_ADDR1');
	$headers[] = getLabel('EXP_REC_ADDR2');
	$headers[] = getLabel('EXP_REC_CITY');
	$headers[] = getLabel('EXP_REC_STATE');
	$headers[] = getLabel('EXP_REC_ZIP');
	$headers[] = getLabel('EXP_REC_COUNTRY');
	$headers[] = getLabel('EXP_REC_PHONE');
	$headers[] = getLabel('EXP_REC_EMAIL');

        return $headers;
    }

    private function _threadHeaderRow()
    {
        return array(
            'incidents.i_id',
            'accounts.first_name',
            'accounts.last_name',
            'threads.channel',
            'contacts.first_name',
            'contacts.last_name',
            'contacts.email',
            'threads.created_time',
            'threads.display_order',
            'threads.entry_type',
            'threads.text'
        );
    }

    private function _attachmentHeaderRow()
    {
        return array(
            getLabel( 'EXP_I_ID' ),
            getLabel( 'EXP_CONTENT_TYPE' ),
            getLabel( 'EXP_CREATED_TIME' ),
            getLabel( 'EXP_FILE_NAME' ),
            getLabel( 'EXP_ATTACH_ID' ),
            getLabel( 'EXP_SIZE' ),
            getLabel( 'EXP_UPDATED_TIME' ),
            getLabel( 'EXP_DESCRIPTION' )
        );
    }

    // get contact info from db
    private function _getContact($c_id)
    {
        if (array_key_exists($c_id, $this->contactCache))
        {
            // pull contact from memory if already seen
            $contact = $this->contactCache[$c_id];
        }
        else
        {
          try
          {
            //$contact = RNCPHP\Contact::fetch($c_id);
            // use tabular query for performance optimization
            $roql = sprintf("SELECT Name.First as first_name, Name.Last as last_name,
                Emails.Address as email, Phones.Number as phone,
                c\$middle_name as middle_name, c\$salutation.Name as salutation, c\$suffix.Name as suffix
                FROM Contact
                WHERE ID = %d
                    AND Phones.PhoneType.LookupName = 'Mobile Phone'
                    AND Emails.AddressType.LookupName = 'Email - Primary'", $c_id);
            $contact = RNCPHP\ROQL::query($roql)->next()->next();
            // insert contact into memory
            $this->contactCache[$c_id] = $contact;
          }
          catch (Exception $err)
          {
            echo sprintf( "Error case: %s<br />", $err->getMessage() );exit;
            return false;
          }
        }
        //echo"<pre>c_id: $c_id";print_r($contact);echo"</pre>yo";exit;
        return $contact;
    }

    // get contact menu field value (email, phones)
    // i.e. $menu = $contact->Emails
    // $lookupField = Address
    // $lookupTypeName = AddressType
    // $lookupTypeValue = Primary
    private function _getContactMenu($menu, $lookupField, $lookupTypeName, $lookupTypeValue)
    {
        foreach ($menu as $field)
        {
            if ($field->$lookupTypeName->LookupName === $lookupTypeValue)
                return $field->$lookupField;
        }
        return null;
    }

    // store product/cat hierachy in memory
    // use report to define key and prod/cat hier values
    private function _getProds($reportID)
    {
        $reportToken = createToken( $reportID );
        $results = $this->Report_model2->getDataHTML( $reportID, $reportToken, $reportFilterArray, array() );
        foreach ($results['data'] as $prodArray)
        {
            $prodCache[$prodArray[0]] = $prodArray[1];
        }

        return $prodCache;
    }

    // store the org name id relationship in memory
    // eventually cache the org hash to disk if necessary
    private function _getOrgs()
    {
        $roql = "SELECT O.ID, O.Name FROM Organization O";
        try
        {
            $result = RNCPHP\ROQL::query($roql)->next();
            while ($org = $result->next())
            {
                $orgCache[$org['ID']] = $org['Name'];
            }
            return $orgCache;
        }
        catch (Exception $err)
        {
            echo sprintf( "Error case: %s<br />", $err->getMessage() );
            return false;
        }
    }

    function activeIncidents()
    {
        $this->dataType = 'Incident';
        $this->fileName = 'Active';

        if( !$this->_isInitialized() )
        {
            throw new Exception( 'CSV data export not fully initialized.' );
        }

        // get product cache
        $prods = $this->_getProds(getSetting('PRODUCTS_REPORT_ID'));
        // get category cache
        $cats = $this->_getProds(getSetting('CATEGORIES_REPORT_ID'));
        // get org cache
        $orgs = $this->_getOrgs();

        // Is there a cache file available?
        $cache = $this->cacheFileAvailable();
        if( $cache )
            return $cache;

        $incidents = $this->_executeReport();
        //echo count($incidents['data'])." <pre>";print_r($incidents['data']);echo"</pre>";exit;
        $this->writeDataRow( $this->_incidentHeaderRow() );
        foreach( $incidents['data'] as $incident )
        {
            //hack to get around max join error
            //we are hard coding the columns because they have to be hard coded in this order anyways
            //we are getting prod from a secondary report with key product values (to do for categories)
            $incident[getSetting('EXPORT_INCIDENT_PROD_COL')] = $prods[$incident[getSetting('EXPORT_INCIDENT_PROD_COL')]];
            $incident[getSetting('EXPORT_INCIDENT_CAT_COL')] = $cats[$incident[getSetting('EXPORT_INCIDENT_CAT_COL')]];
            //we are getting org_id for row 38 so replace it with the org name from the org cache
            //$incident[38] = $orgs[$incident[38]];
            //since we need to search on org name, it would be easier to keep the org table join
            //so we will remove the contact join, however since we cannot keep all contacts in memory,
            //we will have no choice but to perform a db hit each time.
            //to maximize performance we will cache unique contacts into memory to prevent unecessary db hits.
            //The export reports first_name will be replaced with the contact.c_id
            // and the last_name, email, mobile phone, and middle names will be set by the lookup from their respective fixed columns
            unset($contact);
            $contact = $this->_getContact($incident[getSetting('EXPORT_CONTACT_FIRST_NAME_COL')]);
            $incident[getSetting('EXPORT_CONTACT_FIRST_NAME_COL')]= trim($contact['salutation'] .' '. $contact['first_name'] .' '. $contact['middle_name']);
            $incident[getSetting('EXPORT_CONTACT_LAST_NAME_COL')] = trim($contact['last_name'] .' '. $contact['suffix']);
            $incident[getSetting('EXPORT_CONTACT_EMAIL_COL')] = $contact['email'];
            $incident[getSetting('EXPORT_CONTACT_MOBILE_COL')] = $contact['phone'];

          // we are stuck with hard coding the column id's and since not all reports have the same columns we have to hack it
          // we are now concatenating these fields with first and last name
          /*
          switch( $this->reportID )
          {
            case getSetting('GOVERNMENT_PORTAL_CLOSED_CASE_EXPORT_REPORT_ID'):
                $incident[getSetting('EXPORT_CONTACT_MIDDLE_NAME_COL')] = $contact['middle_name'];
                $incident[getSetting('EXPORT_CONTACT_SALUTATION_COL')] = $contact['salutation'];
                $incident[getSetting('EXPORT_CONTACT_SUFFIX_COL')] = $contact['suffix'];
                break;
            case getSetting('GOVERNMENT_PORTAL_ACTIVE_CASE_EXPORT_REPORT_ID'):
                $incident[getSetting('EXPORT_CONTACT_MIDDLE_NAME_COL')-1] = $contact['middle_name'];
                $incident[getSetting('EXPORT_CONTACT_SALUTATION_COL')-2] = $contact['salutation'];
                $incident[getSetting('EXPORT_CONTACT_SUFFIX_COL')-2] = $contact['suffix'];
                break;
            case getSetting('CASE_EXPORT_REPORT_ID'):
                $incident[getSetting('EXPORT_CONTACT_MIDDLE_NAME_COL')-1] = $contact['middle_name'];
                $incident[getSetting('EXPORT_CONTACT_SALUTATION_COL')+3] = $contact['salutation'];
                $incident[getSetting('EXPORT_CONTACT_SUFFIX_COL')+3] = $contact['suffix'];
                break;
          }
          */

            //echo"<pre>";print_r($incident);echo"</pre>";exit;
            $this->writeDataRow( $incident );
        }

        fclose( $this->fh );

        return $this->getFileName();
    }

    function incidentThreads()
    {
        $this->dataType = 'CommunicationHistory';
        $this->fileName = 'Active';

        if( !$this->_isInitialized() )
            throw new Exception( 'CSV data export not fully initialized.' );

        // Is there a cache file available?
        $cache = $this->cacheFileAvailable();
        if( $cache )
            return $cache;

        $incidents = $this->_executeReport();
        $this->writeDataRow( $this->_threadHeaderRow() );

        foreach( $incidents['data'] as $i_id )
        {
            try
            {
                $incident = RNCPHP\Incident::fetch( intval( $i_id[0] ) );
                foreach( $incident->Threads as $thread )
                {
                    // Exclude private notes.
                    if( $thread->EntryType->ID != 1 )
                        $this->writeDataRow( $this->_populateIncidentThreadDataArray( $incident, $thread ) );
                }
            }
            catch( RNCPHP\ConnectApiErrorBase $err )
            {
                echo sprintf( "Error case: %s<br />", $err->getMessage() );
                exit;
            }
        }

        fclose( $this->fh );

        return $this->getFileName();
    }

    function incidentFileAttachments()
    {
        $this->userType = $this->ContactPermissions_model->userType();
        $this->dataType = 'FileAttachments';
        $this->fileName = 'Active';

        if( !$this->_isInitialized() )
            throw new Exception( 'CSV data export not fully initialized.' );

        // Is there a cache file available?
        $cache = $this->cacheFileAvailable();
        if( $cache )
            return $cache;

        $incidents = $this->_executeReport();
        $this->writeDataRow( $this->_attachmentHeaderRow() );

        foreach( $incidents['data'] as $i_id )
        {
            try
            {
                $incident = RNCPHP\Incident::fetch( intval( $i_id[0] ) );

                foreach( $incident->FileAttachments as $attachment )
                {
                    if( $this->userType !== 'company' )
                    {
                        // Only display the attachments that were created at the same time the incident was created for Government Portal.
                        if( $attachment->CreatedTime !== $incident->CreatedTime )
                        {
                            continue;
                        }
                    }

                    // Only show public attachments.
                    if( !$attachment->Private )
                        $this->writeDataRow( $this->_populateIncidentAttachmentDataArray( $incident, $attachment ) );
                }
            }
            catch( RNCPNP\ConnectApiErrorBase $err )
            {
                echo sprintf( "Error case: %s<br />", $err->getMessage() );
                exit;
            }
        }

        fclose( $this->fh );

        return $this->getFileName();
    }

    function writeDataRow( $row )
    {
        if( !$this->fh )
        {
            $this->fh = fopen( $this->getFileName(), 'w' );
        }

        // '\"' needs to be replaced with '"'
        $row = str_replace('\"', '"', $row);

        fputcsv( $this->fh, $row );
    }
}
