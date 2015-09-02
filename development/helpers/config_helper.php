<?php
if(!defined('BASEPATH'))
	exit('No direct script access allowed');

function getSetting($config_def) {
	$config = array(

    //bank case list options -- deprecated
		'ARCHIVE_INC_STATUS' => Array('Closed with relief',
			'Closed without relief',
			'Alerted CFPB',
			'Information provided',
			'Full resolution provided',
			'Partial resolution provided',
			'No resolution provided'),
		'UNDER_REVIEW_INC_STATUS' => array('No response',
			'Pending info from company'),
		'ACTIVE_INC_STATUS' => array('Sent to company',
			'In progress',
			'Past due'),

    // custom object message permission menu id's
    'MSG_PERMISSION_VIS_AGENT_ID' => '1',
    'MSG_PERMISSION_VIS_CONSUMER_ID' => '2',

    // Credit reporting category IDs requiring additional yes/no question.
    'CRDT_INCORRECT_INFO_CAT_ID' => 2687,
    'CRDT_COMPANY_INVESTIGATION_CAT_ID' => 2694,

    // successful login locations
    'COMPANY_PORTAL_URL' => '/app/instAgent/list_active',
    'GOVERNMENT_PORTAL_STATE_URL' => '/app/government/active/list',
    'GOVERNMENT_PORTAL_FEDERAL_URL' => '/app/government/closed/list',
    'CONGRESSIONAL_PORTAL_URL' => '/app/government/referral/active/list',
    'ERROR_PAGE_URL' => '/app/error/error_id/4',

    // government portal
    'GOVERNMENT_PORTAL_ORG_TYPE' => 'Regulatory Agency',
    'CONGRESSIONAL_PORTAL_ORG_TYPE' => 'Referring Office',
    'FINANCIAL_INSTITUTION_ORG_TYPE' => 'Financial Institution',
    'OTHER_COMPANY_ORG_TYPE' => 'Other Company',
    'GOVERNMENT_PORTAL_STATE_FILTER_NAME' => 'consumer_state',
    'GOVERNMENT_PORTAL_STATE_FILTER_ID' => 9,
    'GOVERNMENT_PORTAL_MENU_FILTER_NAME' => 'billing_state',
    'GOVERNMENT_PORTAL_STATE_AGENT_USER' => 'state',
    'GOVERNMENT_PORTAL_FEDERAL_AGENT_USER' => 'federal',
    'PENDING_COMPANY_MATCH_ORG_ID' => 28,
    'GOVERNMENT_PORTAL_DATE_RANGE_FILTER' => 'month_of_cases',
    'GOVERNMENT_PORTAL_MIN_CASE_CREATED_TIME' => strtotime( '2011-07-01' ),

    // Congressional Portal
    'ACTIVE_INBOUND_REFERRAL' => 'Active',
    'CONGRESSIONAL_PORTAL_CONSUMER_STATUS_FILTER_NAME' => 'Status',
    'SERVICE_PRODUCT_EXCLUSION_LIST'=>array(
        // vangent__july__jag
        267,  // Credit Reporting
        2966, // Debt Collection > Medical
    ),
    'CONGRESSIONAL_ACCEPT_AND_CONTINUE'=>'Congressional offices have shared visibility into those complaints they submitted with consumer consent.  Consumer complaints are shared with authorized Federal, State, and local agencies to further law enforcement efforts.',
    'CONGRESSIONAL_ACCEPT_AND_CONTINUE_CHECKBOX'=>'I agree with the disclaimer.',
    'CONGRESSIONAL_ACCEPT_AND_CONTINUE_ERROR' => 'You must agree with the disclaimer to continue.',

    // report id's
    'QUICK_LINK_REPORT_ID' => '101602',
    'LATEST_NEWS_REPORT_ID' => '101605',
    //'FAQS_REPORT_ID' => '101604',
    'ANNOUNCEMENTS_REPORT_ID' => '101606',
    'SUPPORT_HISTORY_REPORT_ID' => '101617',
    'FAQS_REPORT_ID' => '138242',
    'NEWS_REPORT_ID' => '138241',
    'TRAINING_REPORT_ID' => '138245',
    'TRAINING_DOCS_REPORT_ID' => '138244',

    // report column definitions for export report
    'EXPORT_INCIDENT_PROD_COL' => 1,
    'EXPORT_INCIDENT_CAT_COL' => 2,
    'EXPORT_CONTACT_FIRST_NAME_COL' => 24,
    'EXPORT_CONTACT_LAST_NAME_COL' => 25,
    'EXPORT_CONTACT_EMAIL_COL' => 26,
    'EXPORT_CONTACT_MOBILE_COL' => 27,
    'EXPORT_CONTACT_MIDDLE_NAME_COL' => 51,
    'EXPORT_CONTACT_SUFFIX_COL' => 54,
    'EXPORT_CONTACT_SALUTATION_COL' => 53,

    /* pro site */
    /*
    'EXPORT_JOBS_REPORT_ID' => 113799,
    'EXPORT_JOBS_COMPONENT_REPORT_ID' => 113800,
    'CONGRESSIONAL_PORTAL_CASE_ACTIVE_REPORT_ID' => 121085,
    'CONGRESSIONAL_PORTAL_CASE_CLOSED_REPORT_ID' => 121086,
    'CFPBFI_DOMAIN' => 'cfpbfi--july.custhelp.com',
    */

    /* tst site */
    /*
    'EXPORT_JOBS_REPORT_ID' => 114423,
    'EXPORT_JOBS_COMPONENT_REPORT_ID' => 114471,,
    'CONGRESSIONAL_PORTAL_CASE_ACTIVE_REPORT_ID' => 121093,
    'CONGRESSIONAL_PORTAL_CASE_CLOSED_REPORT_ID' => 121094,
    'CFPBFI_DOMAIN' => 'cfpbfi--july.custhelp.com',
    */
    
    /* pro/tst */
    /*
    'CASE_ACTIVE_REPORT_ID' => '101614',
    'CASE_REVIEW_REPORT_ID' => '101616',
    'CASE_ARCHIVE_REPORT_ID' => '101615',
    'GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID' => 106750,
    'GOVERNMENT_PORTAL_CASE_CLOSED_REPORT_ID' => 106751,
    'CONGRESSIONAL_PORTAL_CASE_ACTIVE_REPORT_ID' => 121085,
    'CONGRESSIONAL_PORTAL_CASE_CLOSED_REPORT_ID' => 121086,
    'CFPBFI_DOMAIN' => 'cfpbfi--july.custhelp.com',
    */

    /* vangent__dev site */
    /*
    'EXPORT_JOBS_REPORT_ID' => 118181,
    'EXPORT_JOBS_COMPONENT_REPORT_ID' => 118180,
    'CASE_ACTIVE_REPORT_ID' => '118273',
    'CASE_REVIEW_REPORT_ID' => '118275',
    'CASE_ARCHIVE_REPORT_ID' => '118274',
    'GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID' => 118271,
    'GOVERNMENT_PORTAL_CASE_CLOSED_REPORT_ID' => 118272,
    'CONGRESSIONAL_PORTAL_CASE_ACTIVE_REPORT_ID' => 124144,
    'CONGRESSIONAL_PORTAL_CASE_CLOSED_REPORT_ID' => 124145,
    'CFPBFI_DOMAIN' => 'cfpbfi--dev.custhelp.com',
    */

    /* production site */
    'EXPORT_JOBS_REPORT_ID' => 118181,
    'EXPORT_JOBS_COMPONENT_REPORT_ID' => 118180,
    'CASE_ACTIVE_REPORT_ID' => '118273',
    'CASE_REVIEW_REPORT_ID' => '118275',
    'CASE_ARCHIVE_REPORT_ID' => '118274',
    'GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID' => 118271,
    'GOVERNMENT_PORTAL_CASE_CLOSED_REPORT_ID' => 118272,
    'CONGRESSIONAL_PORTAL_CASE_ACTIVE_REPORT_ID' => 124477,
    'CONGRESSIONAL_PORTAL_CASE_CLOSED_REPORT_ID' => 124478,
    'CFPBFI_DOMAIN' => 'secure.consumerfinance.gov',

    // product id's
    'COMPANY_PORTAL_PROD_ID' => '2673',
    'CREDIT_REPORTING_PROD_ID' => '267',

    // category id's
    'TECH_ISSUE_CAT_ID' => '2667',
    'ASK_CAT_ID' => '2666',
    'NEWS_CAT_ID' => '2668',
    'FAQ_CAT_ID' => '2671',
    'QUICK_LINKS_CAT_ID' => '2672',
    'RELATED_COMPANY_CAT_ID' => '2682',
    'CREDIT_REPORTING_CAT_ID' => '51',

    // RedirectedCase Model settings.
    'REDIRECTED_CASE_HISTORY_REPORT_ID' => '101980',
    'REDIRECTED_CASE_HISTORY_REPORT_FILTER_ID' => '1',
    'REDIRECTED_CASE_HISTORY_REPORT_FILTER_NAME' => 'Incident ID',
    'REDIRECTED_COMPLAINT_HANDLER_REPORT_ID' => '101979',
    'REDIRECTED_COMPLAINT_HANDLER_REPORT_FILTER_ID' => '1',
    'REDIRECTED_COMPLAINT_HANDLER_REPORT_FILTER_NAME' => 'Organization ID',

    // Money Tranfers
    'MONEY_TRANS_TRANS_METHOD_IN_PERSON_MENU_ID' => '1522',

    // Case data export settings.
    'CASE_DATA_EXPORT_REPORT_FILTER_ID' => 2,
    'CASE_DATA_EXPORT_REPORT_FILTER_NAME' => 'organization',
    'CASE_DATA_EXPORT_DEFAULT_PAGE' => 1,
    'CASE_DATA_EXPORT_NUM_PER_PAGE' => 1000,
    'CASE_DATA_EXPORT_STATE_FILTER_ID' => 5,
    'CASE_DATA_EXPORT_STATE_FILTER_NAME' => 'consumer_state',
    'CASE_DATA_EXPORT_PRODUCT_FILTER_ID' => 6,
    'CASE_DATA_EXPORT_PRODUCT_FILTER_NAME' => 'prod',
    'CASE_DATA_EXPORT_CATEGORY_FILTER_ID' => 7,
    'CASE_DATA_EXPORT_CATEGORY_FILTER_NAME' => 'cat',
    'CASE_DATA_EXPORT_NAME_ACCOUNT_FILTER_ID' => 8,
    'CASE_DATA_EXPORT_NAME_ACCOUNT_FILTER_NAME' => 'Name on account',
    'CASE_DATA_EXPORT_ACCOUNT_NUMBER_FILTER_ID' => 9,
    'CASE_DATA_EXPORT_ACCOUNT_NUMBER_FILTER_NAME' => 'Account/Loan Number',
    'CASE_DATA_EXPORT_COMPANY_NAME_FILTER_ID' => 10,
    'CASE_DATA_EXPORT_COMPANY_NAME_FILTER_NAME' => 'Company Name',
    'CASE_DATA_EXPORT_CASE_NUMBER_FILTER_NAME' => 'Case number',
    'CASE_DATA_EXPORT_SEARCH_TYPE_FILTER_NAME' => 'searchType',
    'CASE_DATA_EXPORT_SEARCH_TYPE_FILTER_ID' => 12,
    'CASE_DATA_EXPORT_KEYWORD_FILTER_NAME' => 'keyword',
    'CASE_DATA_EXPORT_KEYWORD_FILTER_ID' => null,

    // debt collection
    'DEBT_COLLECTIONS_PRODUCT_ID' => '270',

    // Case data export job manager settings.
    'CASE_DATA_EXPORT_COMPANY_PORTAL_ACTIVE_TYPE' => 'Company Portal - Active',
    'CASE_DATA_EXPORT_COMPANY_PORTAL_UNDER_REVIEW_TYPE' => 'Company Portal - Under Review',
    'CASE_DATA_EXPORT_COMPANY_PORTAL_ARCHIVE_TYPE' => 'Company Portal - Archive',
    'CASE_DATA_EXPORT_GOVERNMENT_PORTAL_ACTIVE_TYPE' => 'Government Portal - Active',
    'CASE_DATA_EXPORT_GOVERNMENT_PORTAL_CLOSED_TYPE' => 'Government Portal - Closed',
    'CASE_DATA_EXPORT_JOB_STATUS_URL' => '/app/account/exports/list',
    'CASE_DATA_EXPORT_JOB_DETAIL_URL' => '/app/account/exports/detail',
    'CASE_DATA_EXPORT_JOB_KICKOFF_URL' => '/cgi-bin/cfpbfi.cfg/php/custom/dataExport/processor.php',
    'CASE_DATA_EXPORT_CATCH_ALL_PRODUCT_IDS' => array( '1199', '257', '268', '1', '264', '266', '251', '250' ),

    // Submit a Ticket category IDs
    'SUBMIT_A_TICKET_CAT_IDS' => '3104,3105,3106,3107',

    'DUMMY_END' => ''

  );

	if(isset($config[$config_def])) {
		return $config[$config_def];
	} else {
		return null;
	}

}
