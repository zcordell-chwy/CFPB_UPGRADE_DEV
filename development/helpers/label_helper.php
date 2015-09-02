<?php
if(!defined('BASEPATH'))
	exit('No direct script access allowed');

function getOS()
{
	$os_platform = "Unknown OS Platform";

	$os_array = array(
		'/windows nt 6.3/i'     => 'Microsoft Windows 8.1',
		'/windows nt 6.2/i'     => 'Microsoft Windows 8',
		'/windows nt 6.1/i'     => 'Microsoft Windows 7',
		'/windows nt 6.0/i'     => 'Microsoft Windows Vista',
		'/windows nt 5.2/i'     => 'Microsoft Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     => 'Microsoft Windows XP',
		'/windows xp/i'         => 'Microsoft Windows XP',
		'/windows nt 5.0/i'     => 'Microsoft Windows 2000',
		'/windows me/i'         => 'Microsoft Windows ME',
		'/win98/i'              => 'Microsoft Windows 98',
		'/win95/i'              => 'Microsoft Windows 95',
		'/win16/i'              => 'Microsoft Windows 3.11',
		'/macintosh|mac os x/i' => 'Mac OS X',
		'/mac_powerpc/i'        => 'Mac OS 9',
		'/ubuntu/i'             => 'Ubuntu',
		'/iphone/i'             => 'iPhone',
		'/ipod/i'               => 'iPod',
		'/ipad/i'               => 'iPad',
		'/android/i'            => 'Android',
		'/blackberry/i'         => 'BlackBerry',
		'/webos/i'              => 'Mobile',
		'/linux/i'              => 'Linux',
		'/windows/i'            => 'Microsoft Windows (Other)',
		'/mac/i'                => 'Other Mac OS'
	);

	foreach ($os_array as $regex => $value)
	{
		if (preg_match($regex, $_SERVER["HTTP_USER_AGENT"]))
		{
			return $value;
		}
	}

	return $os_platform;
}

function getBrowser()
{
	$CI = get_instance();
	$CI->load->library('SlimBrowscap');
	return $CI->slimbrowscap->GetFriendlyBrowser();
}

function getLabel($label_def) {

	$labels = array(

	// Buttons/Commands
    'BACK_CMD' => 'Back',
		'SAVE_CMD' => 'Save',
		'CONTINUE_CMD' => 'Continue',
		'CHOOSE_CMD' => 'Choose...',
		'EDIT_CMD' => 'Edit',
    'SUBMIT_CMD' => 'Submit',

	// Complaint States
		'CC_STATUS_FILE_LBL' => '1. File',
		'CC_STATUS_BANK_LBL' => '2. Bank review',
		'CC_STATUS_CONSUMER_LBL' => '3. Consumer review',
		'CC_STATUS_FINAL_LBL' => '4. CFPB review',

	// Complaint Text
	// CC What Happened
		'CC_INTRO_TXT' => 'We\'ll forward your issue to your credit card company, give you a tracking number, and keep you updated on the status of your complaint.',
		'CC_WHAT_HAPPEN_TXT' => 'Describe what happened so we can understand the issue...',
		'CC_WHICH_CAT_TXT' => 'Which category best describes your complaint?',
	// CC Desired Resolution
		'CC_WHAT_RESOLUTION_TXT' => 'What do you think would be a fair resolution to your issue?',
	// CC My Info Section
		'CC_IS_SERVICE_DEP_TXT' => 'The cardholder is a servicemember or a dependent of a servicemember.',
	// CC Somone Else Info
		'WHAT_RELATION_TXT' => 'What is your relationship to this person?',

	// CC Info Section
		'CC_SAME_BILL_ADDR_TXT' => 'Property address is the same as mailing address.',

	// Complaint Review Section
		'CC_REVIEW_STATUS' => 'Status',
		'CC_REVIEW_WHAT_HEAD' => 'What happened',
		'CC_REVIEW_RES_HEAD' => 'Desired resolution',
    'CC_REVIEW_CO_RESP_HEAD' => 'Company Response',
		'CC_REVIEW_YOU_HEAD' => 'My information',
		'CC_REVIEW_CC_HEAD' => 'Credit card information',

	// Legal Consent Message
		'REVIEW_AFFIRM_MSG' => 'The information given is true to the best of my knowledge and belief. I understand that the CFPB cannot act as my lawyer, a court of law, or a financial advisor.',

	// Error Messages
		'CC_ERROR_WHAT_HAPPEN_MSG' => 'You must describe your issue',
		'CC_ERROR_WHICH_CAT_MSG' => 'You must categorize your complaint',
		'CC_ERROR_RESOLUTION_MSG' => 'You must describe your desired resolution',

	//mortgage specific labels
		'MORTGAGE_WHAT_HAPPEN_TXT' => 'Describe what happened so we can understand the issue...',
		'MORTGAGE_WHICH_CAT_TXT' => 'This is about',
		'MORTGAGE_FILING_ON_BEHALF' => "I am filing on behalf of",
		'MORTGAGE_WHICH_PART_RELATED' => "Which part of the mortgage process is your issue related to?",
		'MORTGAGE_WHICH_PART_RELATED_ERROR' => "You must select which part of the mortgage process your issue is related to",
		'MORTGAGE_IS_SERVICE_MEMBER' => 'The consumer is a servicemember or is a spouse or dependent of a servicemember.',
		'MORTGAGE_IS_SERVICE_MEMBER_ERROR' => 'You must select servicemember or is a spouse or dependent of a servicemember',

		'MORTGAGE_RELATED_APPLY_LBL' => 'Applying for the loan',
		'MORTGAGE_RELATED_OFFER_LBL' => 'Receiving a credit offer',
		'MORTGAGE_RELATED_AGREEMENT_LBL' => 'Signing the agreement',
		'MORTGAGE_RELATED_PAYMENTS_LBL' => 'Making payments',
		'MORTGAGE_RELATED_PROBLEMS_LBL' => 'Problems when you are unable to pay',

		'MORTGAGE_RELATED_APPLY_NOTE' => 'Application, originator, mortgage broker',
		'MORTGAGE_RELATED_OFFER_NOTE' => 'Credit decision/Underwriting',
		'MORTGAGE_RELATED_AGREEMENT_NOTE' => 'Settlement process and costs',
		'MORTGAGE_RELATED_PAYMENTS_NOTE' => 'Loan servicing, payments, escrow accounts',
		'MORTGAGE_RELATED_PROBLEMS_NOTE' => 'Loan modification, collection, forclosure',

		'MORTGAGE_OMB' => 'OMB #1505-0236',
		'MORTGAGE_OMB_POPUP' => 'An agency may not conduct or sponsor, and a person is not required to respond to, a collection of information unless the collection of information displays
        							a valid control number assigned by the Office of Management and Budget (OMB). The OMB control number for this collection is 1505&#8211;0236, expires 12/31/2011.',
		'MORTGAGE_CONSUMER_IS' => 'The consumer is a:',
		'PRODUCT_INFO' => 'Product information',

		'MORTGAGE_IS_DISCRIM_TXT' => 'Do you believe the issue involves discrimination?',
		'MORTGAGE_IS_DISCRIM_TXT_ERROR' => 'You must indicate the basis of discrimination',
		'MORTGAGE_DISCRIM_AGE' => 'Age',
		'MORTGAGE_DISCRIM_MARITAL' => 'Marital status',
		'MORTGAGE_DISCRIM_ORIGIN' => 'National origin',
		'MORTGAGE_DISCRIM_RACE' => 'Race',
		'MORTGAGE_DISCRIM_CCP_ACT' => 'Exercise of rights under the consumer credit protection act',
		'MORTGAGE_DISCRIM_PUBLIC_ASSIST' => 'Receipt of public assistance',
		'MORTGAGE_DISCRIM_RELIGION' => 'Religion',
		'MORTGAGE_DISCRIM_SEX' => 'Sex',
		'LOAN_NUM' => 'Loan number',
		'LOAN_NUMBER_NOTE' => 'For account identification only',

	//general labels
    'CASE_NO' => 'Case number',
    'CONSUMER_INFO' => 'Consumer Information',
		'INFO_ABOUT_COMPANY' => 'Information about the company',
		'REMAINING_CHARS_LBL' => 'characters remaining',
		'ON_THE_BASIS_OF' => 'On the basis of',
		'MAILING_ADDRESS' => 'Mailing address',
		'MY_CONTACT_INFO' => 'My contact information',
		'PRIVACY_ACT_LABEL' => 'Privacy act statement',
		'PRIVACY_ACT_STMT' => '<p>The information that you provide will permit the Consumer Financial Protection Bureau to respond to consumer complaints and inquiries regarding practices by banks and
						        other institutions supervised by the Consumer Financial Protection Bureau. The information may be disclosed:</p>
						        <ul>
						          <li>to an entity that is the subject of your complaint;</li>
						          <li>to a court, magistrate, or administrative tribunal in the course of a proceeding;</li>
						          <li>to third parties to the extent necessary to obtain information that is relevant to the resolution of a complaint;</li>
						          <li>for enforcement, statutory, and regulatory purposes;</li>
						          <li>to another federal or state agency or regulatory authority;</li>
						          <li>to a member of Congress; to the Department of Justice, a court, an adjudicative body or administrative tribunal, or a party in litigation; and</li>
						          <li>to contractors, agents, and others authorized by the Consumer Financial Protection Bureau to receive this information.</li>
						        </ul>
						        <p>This collection of information is authorized by 12 U.S.C. &sect; 5493.</p>
						        <p>You are not required to file a complaint or provide any identifying information, and you may withdraw your complaint at any time. However, if you do not provide the
						        	requested information, the Consumer Financial Protection Bureau may not be able to take action on your complaint fully.</p>',
		'CLOSE' => 'close',
		'FILING_ON_BEHALF_NOTICE' => 'Filing on behalf of someone else may require signed, written permission.',
		'UPLOAD_DOCS_LABEL' => 'Upload any supporting documents (Optional)',
		'UPLOAD_DOCS_EXAMPLES' => 'Mortgage statements, good faith estimates, loan origination documents, etc.',
		'SUPPORTING_DOCS' => 'Supporting Documents',
		'ATTACH_DOCS' => 'Attach documents',
		'DEPENDANT_INFO' => 'Dependent information',
		'SERVICE_MEMBER_INFO' => 'Servicemember information',
		'BILLING_ADDR' => 'Property address',
		'OPTIONAL' => '(Optional)',
		'SALUTATION' => 'Salutation',
		'STATE' => 'State',
		'ZIP_CODE' => 'ZIP code',
		'COUNTRY' => 'Country',
		'EMAIL' => 'Email',
		'MY_AGE_IS' => 'My age is',
		'YEARS' => 'years',
		'AGE' => 'age',
		'PHONE' => 'phone',
		'MYSELF' => 'Myself',
		'SOMEONE_ELSE' => 'Someone else',
		'FIRST_NAME' => 'First name',
		'LAST_NAME' => 'Last name',
		'ADDR1' => 'Address',
		'ADDR2' => 'Apartment, suite, building',
		'SVC_MEMBER' => 'Servicemember',
		'DEP_OR_SPOUSE_OF_SVC_MBM' => 'Dependent or spouse of a servicemember',
		'SVC_MEMBER_STATUS' => 'What is the servicemember\'s status?',
		'WHAT_SERVICE' => 'What is the service?',
		'SVC_MEMBER_RANK' => 'What is the servicemember\'s rank?',

	// Bank Portal
		'TERMS_OF_SERVICE' => 'Terms of service',
		'TERMS_OF_SERVICE_POPUP' => '<p>Your company must use the data provided for purposes consistent with resolving individual consumer complaints.  Your company will keep all consumer complaint data confidential and safeguarded against unintentional disclosures. In particular, your company will not disclose consumer complaint data to anyone other than your company\'s employees, consultants, and agents for the purpose of consumer complaint processing. If your company receives misaddressed or misrouted information, your company will immediately notify the CFPB and securely destroy the information. If you suspect that an unauthorized user has gained access to consumer complaint data or if you disclose it without authorization, your company will immediately notify the CFPB.  In addition, your company will promptly notify the CFPB should an authorized user no longer have authority to access this data and will ensure that such access is terminated. These requirements are not intended to take the place of your company\'s obligations under federal law to safeguard consumer information.</p>
            <p>While the CFPB makes reasonable efforts to inform third party complainants of the need to provide proof of third party authorization to your company, the CFPB is not responsible for determining the legal necessity, accuracy or validity of such authorization.</p>',
                'TERMS_OF_SERVICE_POPUP_CONGRESSIONAL' => '<p>Your office must use the data provided for purposes consistent with resolving individual consumer complaints. Your office will keep all consumer complaint data confidential and safeguarded against unintentional disclosures. In particular, your office will not disclose consumer complaint data to anyone other than your office\'s employees, consultants, and agents for the purpose of consumer complaint processing. If your office receives misaddressed or misrouted information, your office will immediately notify the CFPB and securely destroy the information. If you suspect that an unauthorized user has gained access to consumer complaint data or if you disclose it without authorization, your office will immediately notify the CFPB. In addition, your office will promptly notify the CFPB should an authorized user no longer have authority to access this data and will ensure that such access is terminated. These requirements are not intended to take the place of your office\'s obligations under federal law to safeguard consumer information.</p>',
    //zc - changed for business  'CFPB_STATUS_DESC' => '<strong>Update for review:</strong><br/>This information will be included as part of our Consumer Response Specialist\'s ongoing review of this case.',
    'CFPB_STATUS_DESC' => '<strong>Update for review:</strong><br/>',
    'DELINQUENT_RESPONSE' => 'Delinquent Response',
    'RELIEF' => 'Relief',
    'INITIAL_RESPONSE' => 'Initial Response',
    'DESCRIBE_RELIEF' => 'Describe the relief',
    'PROVIDE_A_RESPONSE' => 'Provide a response',
		'PROVIDE_A_RESPONSE_TXT' => 'Provide a response that includes, in the minimum, the following elements:',
		'PROVIDE_A_RESPONSE_SUBTXT' => '
            <ul>
            <li>any steps taken by you in response to the complaint;</li>
            <li>any communications received from the consumer by you in response to the above steps taken; and</li>
            <li>any follow up actions or planned follow up actions by you in further response to the complaint.</li>
            </ul>',
    'EXPLANATION_OF_CLOSURE' => 'Explanation of closure',
    'COMPANY_DISPUTE_FILED' => "Our company has no prior record of this consumer's dispute as submitted to the CFPB, nor has the respective furnisher reported any errors to our company regarding the disputed information.",
    'COMPANY_DISPUTE_FILED_NOTE' => "By checking the box you certify that after a full search of your file system you have not located any record of this consumer's dispute as submitted to the CFPB.",
    'AGENCY_NAME' => 'Agency name',
    'AGENCY_NAME_TXT' => 'Enter the name of the agency or regulator where this case should be sent for this product/issue',
    'EXPLANATION_OF_REDIRECT' => 'Explanation of redirect',
    'EXPLANATION_OF_REDIRECT_TXT' => 'Explanation of why this case should be redirected',
		'PROVIDE_INFORMATION_LBL' => 'CFPB request for additional information',
		'PROVIDE_INFORMATION_TXT' => 'Please provide the following information to aid us in further evaluating this complaint:<br/>
            <ul>
            <li>Credit Card Disclosures, Terms, and Agreements for this Account</li>
            <li>Consumer Payment History for this Account from [INSERT: Date Range]</li>
            <li>Consumer Payment History</li>
            </ul>
            <span>Please respond with the request items within ten business-days of the receipt of this letter so that we can address the consumer\'s concerns quickly.<br/>
            <br/>
            Thank you, in advance for your time and consideration regarding this matter. Should you have any questions and/or comments please contact:<br/>
            <br/>
            [INSERT: Name of Consumer Response Sepcialist]<br/>
            Consumer Response Specialist<br/>
            [INSERT: phone number]<br/>
            [INSERT: Consumer Response Sepcialist Email Address]<br/>',
    'COMPLAINT_UPDATE_SUCCESSFUL_MSG' => 'You have updated the status of this complaint.',
    'ASK_CONFIRM_MSG' => 'Thank you for submitting your ticket.
            <br/>Stakeholder Support will answer as quickly as possible.
            <br/>Your ticket # is ',
    'REPORT_ISSUE_CONFIRM_MSG' => 'Thank you for submitting your technical issue.
            <br/>Stakeholder Support will review your issue and respond as quickly as possible.
            <br/>Your ticket # is ',
    'THANK_YOU_CONFIRM_MSG' => 'You can review and track your tickets by clicking on the "Support History" link in the Portal at any time.
            <br/>Thank you, Consumer Response Stakeholder Support',
    'TRAINING_CENTER_LBL' => 'Training Center - CFPB Portal',

    // try not to use these since we will use the array below
		'CO_STATUS_CLOSED_W_RELIEF' => 'Closed with relief',
		'CO_STATUS_CLOSED_WO_RELIEF' => 'Closed without relief',
		'CO_STATUS_CLOSED_W_MONETARY_RELIEF' => 'Closed with monetary relief',
		'CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF' => 'Closed with non-monetary relief',
                'CO_STATUS_CLOSED_W_EXPLANATION' => 'Closed with explanation',
		'CO_STATUS_CLOSED' => 'Closed',
		'CO_STATUS_INFO_PROVIDED' => 'Information provided',
		'CO_STATUS_IN_PROGRESS' => 'In progress',
		'CO_STATUS_SENT_TO_COMPANY' => 'Sent to company',
		'CO_STATUS_PAST_DUE' => 'Past due',
		'CO_STATUS_ALERTED_CFPB' => 'Alerted CFPB',
		'CO_STATUS_NO_RESPONSE' => 'No response',
		'CO_STATUS_PENDING_INFO' => 'Pending info from company',
		'CO_STATUS_FULL_RESOLUTION' => 'Full resolution provided',
		'CO_STATUS_PARTIAL_RESOLUTION' => 'Partial resolution provided',
		'CO_STATUS_NO_RESOLUTION' => 'No resolution provided',
    'CO_STATUS_SENT_TO_REGULATOR' => 'Sent to regulator',

    'REDIRECT_CASE_TO' => 'Redirect to related company',
    'REDIRECT_CASE_TO_LBL' => 'To which company should this case be redirected?',
    'REDIRECT_CASE_REASON' => 'Reason case should be redirected',
    'REDIRECT_CASE_REASON_LBL' => 'Why should this case be redirected?',

    // try to use array of statuses so we only need to create one attribute for the widget logic to access
    'CO_STATUS_ARRAY' => Array(
		'CO_STATUS_CLOSED_W_RELIEF' => 'Closed with relief',
	    'CO_STATUS_CLOSED_WO_RELIEF' => 'Closed without relief',
        'CO_STATUS_CLOSED_W_MONETARY_RELIEF' => 'Closed with monetary relief',
        'CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF' => 'Closed with non-monetary relief',
        'CO_STATUS_CLOSED_W_EXPLANATION' => 'Closed with explanation',
        'CO_STATUS_CLOSED' => 'Closed',
        'CO_STATUS_INFO_PROVIDED' => 'Information provided',
        'CO_STATUS_IN_PROGRESS' => 'In progress',
        'CO_STATUS_INCORRECT_COMPANY' => 'Incorrect company',
        'CO_STATUS_SENT_TO_COMPANY' => 'Sent to company',
        'CO_STATUS_PAST_DUE' => 'Past due',
        'CO_STATUS_ALERTED_CFPB' => 'Alerted CFPB',
        'CO_STATUS_NO_RESPONSE' => 'No response',
        'CO_STATUS_PENDING_INFO' => 'Pending info from company',
        'CO_STATUS_FULL_RESOLUTION' => 'Full resolution provided',
        'CO_STATUS_PARTIAL_RESOLUTION' => 'Partial resolution provided',
        'CO_STATUS_NO_RESOLUTION' => 'No resolution provided',
        'CO_STATUS_DUPLICATE_CASE' => 'Duplicate CFPB case reported',
        'CO_STATUS_REDIRECTED' => 'Redirected to related company',
        'CO_STATUS_SENT_TO_REGULATOR' => 'Sent to regulator',
        'CO_STATUS_DELINQUENT_RESPONSE' => 'Delinquent response provided',
        'CO_STATUS_MISDIRECTED' => 'Misdirected'
    ),

		'CO_DESC_CLOSED_W_RELIEF' => 'Your final responsive explanation to consumer, indicating that the steps you have taken or will take have objective, measurable, and verifiable monetary value to the consumer',
		'CO_DESC_CLOSED_WO_RELIEF' => 'Your final responsive explanation to consumer, indicating that the steps you have taken or will take do not have objective, measurable, and verifiable monetary value to the consumer',
		'CO_DESC_IN_PROGRESS' => 'Your interim responsive explanation to consumer and the CFPB, indicating that the complaint could not be closed within 15 calendar days and that your final responsive explanation to consumer will be provided through this portal at a later date',
		'CO_DESC_INCORRECT_COMPANY' => 'Cannot take action because the complaint is not related to your company',
		'CO_DESC_MISDIRECTED' => 'Cannot take action because complaint needs alternative routing',
		'CO_DESC_ALERTED_CFPB' => 'Cannot take action for reasons such as suspected fraud, pending legal matter or because the complaint was filed by unauthorized third party',

    'CO_STATUS_DESC_ARRAY' => Array(
        'CO_DESC_CLOSED_W_MONETARY_RELIEF' => 'Your final responsive explanation to the consumer, indicating that the steps you have taken or will take include objective, measurable, and verifiable monetary relief to the consumer',
        'CO_DESC_CLOSED_W_NON_MONETARY_RELIEF' => 'Your final responsive explanation to the consumer, indicating that the steps you have taken or will take include other objective or verifiable relief to the consumer',
        'CO_DESC_CLOSED_W_EXPLANATION' => 'Your final responsive explanation to the consumer, indicating that you provided an explanation tailored to the individual consumer\'s complaint',
        'CO_DESC_CLOSED' => 'Your final response to the consumer, closing the complaint without relief or explanation',
        'CO_DESC_IN_PROGRESS' => 'Your interim responsive explanation to consumer and the CFPB, indicating that the complaint could not be closed within 15 calendar days and that your final responsive explanation to consumer will be provided through this portal at a later date',
        'CO_DESC_INCORRECT_COMPANY' => 'Cannot take action because the complaint is not related to your company',
        'CO_DESC_MISDIRECTED' => 'Cannot take action because complaint needs alternative routing',
        'CO_DESC_ALERTED_CFPB' => 'Cannot take action for reasons such as suspected fraud, pending legal matter or because the complaint was filed by unauthorized third party',
        'CO_DESC_DUPLICATE_CASE' => 'Cannot take action because complaint is a duplicate of a complaint you have already received from the CFPB and to which you have already responded via the portal',
        'CO_DESC_REDIRECTED' => 'Cannot take action because complaint needs to be routed to another company with which your company has a contractual relationship',
        'CO_DESC_SENT_TO_REGULATOR' => 'Cannot take action because complaint is about a product or issue that needs to be routed to another regulator'
    ),

    'DOLLAR_AMOUNT_LBL' => 'Dollar amount',
    'ENTER_ORIG_CASE_NO' => 'Enter the case # of the original CFPB case',
    'COMPANY_PORTAL_LBL' => 'CFPB Portal', // 'Company Portal',
    'CASE_HISTORY_LBL' => 'Case History',
    'MESSAGE_HISTORY_LBL' => 'Message History',
    'FILTER_BY_ISSUE_LBL' => 'Filter by issue',
    'FILTER_BY_PRODUCT_LBL' => 'Filter by product',
    'FAQ_LBL' => 'FAQs',
    'GET_ANSWERS_LBL' => 'Get Answers',
    'HOME_LBL' => 'Home',
    'HELP_LBL' => 'Help',
    'TRAINING_LBL' => 'Training',
    'NEWS_LBL' => 'News',
    'HELP_HDG' => 'Have a question?/Report an issue',
    'HELP_ASK' => 'Ask a question',
    'HELP_REPORT' => 'Submit a Ticket',
    'INSTITUTION_NAME_LBL' => 'Institutional name',
    'OS_VERSION_LBL' => 'Operating system and version',
    'BROWSER_VERSION_LBL' => 'Browser name and version',
    'DESCRIBE_ISSUE' => 'Describe the issue in detail',
    'DESCRIBE_ISSUE_SPECIFIC' => 'Please provide the specific steps you take when the issue occurs and print screens for each step.<br/>
        <ul>
        <li>Checked under review tab</li>
        <li>Checked each case</li>
        <li>Dates don\'t match</li>
        <li>etc</li>
        </ul>',
    'ATTACH_SCREENSHOT' => 'Attach print screens',
    'LATEST_NEWS_HDG' => 'Latest News',
    'SUBSCRIBE_MSG' => 'To receive automatic updates when new articles and announcements are posted to the CFPB Company Portal.',
    'UNSUBSCRIBE_MSG' => 'To stop receiving automatic updates when new articles and announcements are posted to the CFPB Company Portal.',

    // Export job manager labels.
    'EXPORT_JOBS_NAV_LBL' => 'Data Exports',
    'EXPORT_JOBS_HEADING' => 'Data Export Jobs Status',
    'EXPORT_JOBS_DETAIL_HEADING' => 'Data Export Job Detail',
    'EXPORT_JOBS_COMPONENT_HEADING' => 'Job Components',
    'EXPORT_PLURAL_JOBS_NOTIF_LBL' => 'You currently have %d data exports finished and waiting for your attention.',
    'EXPORT_SINGULAR_JOBS_NOTIF_LBL' => 'You currently have %d data export finished and waiting for your attention.',
    'EXP_JOB_CONTACT_LBL' => 'User:',
    'EXP_JOB_CREATED_LBL' => 'Created:',
    'EXP_JOB_STATUS_LBL' => 'Status:',
    'EXP_JOB_ATTACHMENTS_LBL' => 'Files',

    // Export case detail labels.
    'EXP_I_ID' => 'CASE ID',
    'EXP_PROD' => 'PRODUCT',
    'EXP_CAT' => 'ISSUE',
    'EXP_WHAT_HAPPENED' => 'WHAT HAPPENED',
    'EXP_MORTG_THIS_IS_ABOUT' => 'THIS IS ABOUT',
    'EXP_NBCCESTVALUE' => 'MONETARY LOSS',
    'EXP_DTCCISSUEHAPPEN' => 'DATE OF INCIDENT',
    'EXP_RCONTACTEDCCISSUER' => 'CONTACTED CC ISSUER',
    'EXP_CMQILLEGALDISCRIMINATION' => 'INVOLVES DISCRIMINATION',
    'EXP_DISCRIM_AGE' => 'AGE',
    'EXP_DISCRIM_MARITAL' => 'MARITAL STATUS',
    'EXP_DISCRIM_NATIONAL' => 'NATIONAL ORIGIN',
    'EXP_DISCRIM_RACE' => 'RACE',
    'EXP_DISCRIM_PUBASSIST' => 'RECEIPT OF PUBLIC ASSISTANCE',
    'EXP_DISCRIM_RELIGION' => 'RELIGION',
    'EXP_DISCRIM_SEX' => 'SEX',
    'EXP_DISCRIM_EXERCISE' => 'EXERCISE OF RIGHTS UNDER THE CONSUMER CREDIT PROTECTION ACT',
    'EXP_DISCRIM_OTHER' => 'OTHER',
    'EXP_CONCERN_ABOUT_FORECLOSURE' => 'CONCERNED ABOUT FORECLOSURE',
    'EXP_MISSED_PAYMENT' => 'MISSED PAYMENT OR DEFAULT',
    'EXP_IS_FORECLOSURE_SCHEDULED' => 'IS FORECLOSURE SCHEDULED',
    'EXP_DATE_SCHEDULED_FORECLOSURE' => 'FORECLOSURE DATE',
    'EXP_PAY_COMPANY_AVOID_FORECLOSURE' => 'PAY COMPANY AVOID FORECLOSURE',
    'EXP_FAIRRESOLUTION' => 'DESIRED RESOLUTION',
    'EXP_CONTACTS_FIRST_NAME' => 'FIRST NAME',
    'EXP_CONTACTS_LAST_NAME' => 'LAST NAME',
    'EXP_CONTACT_EMAIL' => 'E-MAIL ADDRESS',
    'EXP_CONTACT_PHONE' => 'PHONE',
    'EXP_CCMAIL_ADDR1' => 'MAIL STREET',
    'EXP_CCMAIL_CITY' => 'MAIL CITY',
    'EXP_CCMAIL_STATE' => 'MAIL STATE',
    'EXP_CCMAIL_ZIP' => 'MAIL ZIP',
    'EXP_ONBEHALF_MYSELF' => 'ON BEHALF OF MYSELF',
    'EXP_ONBEHALF_SOMEONE' => 'ON BEHALF OF SOMEONE ELSE',
    'EXP_AMWASSERVICEMEMBER' => 'I AM OR WAS A SERVICEMEMBER',
    'EXP_AMDEPENDENT' => 'I AM OR WAS A DEPENDENT OF A SERVICEMEMBER',
    'EXP_NAME_ON_CARD' => 'NAME ON ACCOUNT',
    'EXP_RCCNUMBER' => 'ACCOUNT/LOAN NUMBER',
    'EXP_CC_CO_NAME' => 'COMPANY NAME',
    'EXP_CCBILL_ADDR1' => 'BILLING STREET',
    'EXP_CCBILL_ADDR2' => 'BILLING APARTMENT, SUITE, BUILDING',
    'EXP_CCBILL_CITY' => 'BILLING CITY',
    'EXP_CCBILL_STATE' => 'BILLING STATE',
    'EXP_CCBILL_ZIP' => 'BILLING ZIP',
    'EXP_CC_ISSUER_ADDR1' => 'COMPANY STREET',
    'EXP_CC_ISSUER_ADDR2' => 'COMPANY APARTMENT, SUITE, BUILDING',
    'EXP_CC_ISSUER_CITY' => 'COMPANY CITY',
    'EXP_CC_ISSUER_STATE' => 'COMPANY STATE',
    'EXP_CC_ISSUER_ZIP' => 'COMPANY ZIP',
    'EXP_REF_NO' => 'CASE NUMBER',
    'EXP_BANK_STATUSES' => 'COMPANY STATUS',
    'EXP_SENT_BANK_DATE' => 'SENT TO COMPANY',
    'EXP_RESPONSE_DUE' => 'RESPOND BY',
    'EXP_COMP_EXPLATION_OF_CLOSURE' => 'EXPLANATION OF CLOSURE',
    'EXP_COMP_PROVIDE_A_RESPONSE' => 'INITIAL RESPONSE',
    'EXP_RELIEF_AMOUNT' => 'RELIEF AMOUNT',
    'EXP_COMP_DESCRIBE_RELIEF' => 'RELIEF DESCRIPTION',
    'EXP_REDIRECT_EXPLANATION' => 'EXPLANATION OF REDIRECT',
    'EXP_CFPB_EXPLANATION_OF_CLOSURE' => 'DELINQUENT EXPLANATION OF CLOSURE',
    'EXP_CFPB_PROVIDE_A_RESPONSE' => 'DELINQUENT RESPONSE',
    'EXP_CFPB_DESCRIBE_RELIEF' => 'DELINQUENT RELIEF DESCRIPTION',
    'EXP_CFPB_DOLLAR_AMOUNT' => 'DELINQUENT RELIEF AMOUNT',
    'EXP_DATE_RESOLVED' => 'INITIAL RESPONSE DATE',
    'EXP_DATE_SECOND_RESPONSE' => 'SECOND RESPONSE DATE',
    'EXP_MIDDLE_NAME' => 'MIDDLE NAME',
    'EXP_DATE_BIRTH' => 'DATE OF BIRTH',
    'EXP_SSN' => 'SOCIAL SECURITY NUMBER',
    'EXP_CONSUMER_FILED_DISPUTE' => 'CONSUMER FILED DISPUTE',
    'EXP_DISPUTE_NUMBER' => 'DISPUTE NUMBER',
    'EXP_COMPANY_NO_DISPUTE_FILED' => 'COMPANY INDICATES NO RECORD OF DISPUTE',
    'EXP_AGENCY_REFERRED' => 'REFERRED TO',
    // money trans column headers for exports
    'EXP_CONTACT_SALUTATION' => 'SALUTATION',
    'EXP_CONTACT_SUFFIX' => 'SUFFIX',
    'EXP_CCMAIL_ADDR2' => 'MAIL APARTMENT, SUITE, BUILDING',
    'EXP_CCMAIL_COUNTRY' => 'MAIL COUNTRY',
    'EXP_INCIDENT_CONTACT_AGE' => 'CONTACT AGE',
    'EXP_I_AM_THE' => 'I AM THE',
    'EXP_THIS_PERSON_IS_THE' => 'THIS PERSON IS THE',
    'EXP_CC_ISSUER_COUNTRY' => 'COMPANY COUNTRY',
    'EXP_TRMETHOD' => 'WHERE DID TRANSACTION TAKE PLACE',
    'EXP_SENDER_AGENT_CO' => 'SENDER AGENT NAME',
    'EXP_SENDER_AGENT_ADDR1' => 'SENDER AGENT STREET',
    'EXP_SENDER_AGENT_CITY' => 'SENDER AGENT CITY',
    'EXP_SENDER_AGENT_STATE' => 'SENDER AGENT STATE',
    'EXP_SENDER_AGENT_ZIP' => 'SENDER AGENT ZIP',
    'EXP_SENDER_AGENT_COUNTRY' => 'SENDER AGENT COUNTRY',
    'EXP_SENDER_AGENT_PHONE' => 'SENDER AGENT PHONE',
    'EXP_SENDER_AGENT_WEBSITE' => 'SENDER WEBSITE OR MOBILE APP',
    'EXP_SENDER_SALUTATION' => 'SENDER SALUTATION',
    'EXP_SENDER_FIRST_NAME' => 'SENDER FIRST NAME',
    'EXP_SENDER_MIDDLE_NAME' => 'SENDER MIDDLE NAME',
    'EXP_SENDER_LAST_NAME' => 'SENDER LAST NAME',
    'EXP_SENDER_SUFFIX' => 'SENDER SUFFIX',
    'EXP_SENDER_ADDR1' => 'SENDER ADDR1',
    'EXP_SENDER_ADDR2' => 'SENDER ADDR2',
    'EXP_SENDER_CITY' => 'SENDER CITY',
    'EXP_SENDER_STATE' => 'SENDER STATE',
    'EXP_SENDER_ZIP' => 'SENDER ZIP',
    'EXP_SENDER_COUNTRY' => 'SENDER COUNTRY',
    'EXP_SENDER_PHONE' => 'SENDER PHONE',
    'EXP_SENDER_EMAIL' => 'SENDER E-MAIL',
    'EXP_AMT_TRANSFERRED' => 'AMOUNT TRANSFERRED',
    'EXP_AMT_TRANSFERRED_CURRENCY' => 'AMOUNT TRANSFERRED CURRENCY',
    'EXP_DATE_TRANSFERRED' => 'DATE OF TRANSFER',
    'EXP_TRANSACTION_NUMBER' => 'TRANSACTION NUMBER',
    'EXP_FUNDS_PROMISED_DATE' => 'FUNDS_PROMISED_DATE',
    'EXP_AMT_ERROR' => 'AMOUNT OF ERROR',
    'EXP_AMT_ERROR_CURRENCY' => 'AMOUNT OF ERROR CURRENCY',
    'EXP_DATE_ERROR' => 'DATE OF ERROR',
    'EXP_RECEIPT_METHOD' => 'HOW WAS TRANSFER RECEIVED',
    'EXP_REC_AGENT_CO' => 'RECIPIENT AGENT NAME',
    'EXP_REC_AGENT_ADDR1' => 'RECIPIENT AGENT STREET',
    'EXP_REC_AGENT_CITY' => 'RECIPIENT AGENT CITY',
    'EXP_REC_AGENT_STATE' => 'RECIPIENT AGENT STATE',
    'EXP_REC_AGENT_ZIP' => 'RECIPIENT AGENT ZIP'	,
    'EXP_REC_AGENT_COUNTRY' => 'RECIPIENT AGENT COUNTRY',
    'EXP_REC_AGENT_WEBSITE' => 'RECIPIENT AGENT WEBSITE OR MOBILE APP',
    'EXP_REC_AGENT_ACCT_NUMBER' => 'RECIPIENT ACCOUNT NUMBER',
    'EXP_REC_SALUTATION' => 'RECIPIENT SALUTATION',
    'EXP_REC_FIRST_NAME' => 'RECIPIENT FIRST NAME',
    'EXP_REC_MIDDLE_NAME' => 'RECIPIENT MIDDLE NAME',
    'EXP_REC_LAST_NAME' => 'RECIPIENT LAST NAME',
    'EXP_REC_SUFFIX' => 'RECIPIENT SUFFIX',
    'EXP_REC_ADDR1' => 'RECIPIENT ADDR1',
    'EXP_REC_ADDR2' => 'RECIPIENT ADDR2',
    'EXP_REC_CITY' => 'RECIPIENT CITY',
    'EXP_REC_STATE' => 'RECIPIENT STATE',
    'EXP_REC_ZIP' => 'RECIPIENT ZIP',
    'EXP_REC_COUNTRY' => 'RECIPIENT COUNTRY',
    'EXP_REC_PHONE' => 'RECIPIENT PHONE',
    'EXP_REC_EMAIL' => 'RECIPIENT E-MAIL',

    // Export file attachment labels.
    'EXP_CONTENT_TYPE' => 'CONTENT TYPE',
    'EXP_CREATED_TIME' => 'CREATED TIME',
    'EXP_FILE_NAME' => 'FILE NAME',
    'EXP_ATTACH_ID' => 'ATTACHMENT ID',
    'EXP_SIZE' => 'SIZE',
    'EXP_UPDATED_TIME' => 'UPDATED TIME',
    'EXP_DESCRIPTION' => 'DESCRIPTION',

    // moved to config_helper
    'QUICK_LINK_REPORT_ID' => '100994',
    'LATEST_NEWS_REPORT_ID' => '100991',
    'FAQS_REPORT_ID' => '100997',
    'ANNOUNCEMENTS_REPORT_ID' => '100998',
    'CASE_ACTIVE_REPORT_ID' => '100977',
    'CASE_REVIEW_REPORT_ID' => '100986',
    'CASE_ARCHIVE_REPORT_ID' => '100987',
    'SUPPORT_HISTORY_REPORT_ID' => '101000',
    'CASE_EXPORT_REPORT_ID' => '100982',
    'TECH_ISSUE_CAT_ID' => '2669',
    'ASK_CAT_ID' => '2670',

    'MSG_PERMISSION_VIS_AGENT_ID' => '1',
    'MSG_PERMISSION_VIS_CONSUMER_ID' => '2',

    // AAQ redirected labels.
    'ORG_COMPLAINT_HANDLER_PARM' => "complaint == 'handler'",
    'ORG_COMPLAINT_HANDLER_PARM_VALUE' => 'handler',
    'ORG_COMPLAINT_HANDLER_SUBJECT' => 'Add/Remove Related Company',
    'ORG_COMPLAINT_HANDLER_QUESTION_LBL' => 'Details',
    'ATTACH_OFFICIAL_NOTE' => 'Upload documentation showing this is a valid relationship',

    // Abbreviated status labels for Support History incidents.
    'SUPPORT_HISTORY_INFO_PROVIDED_FULL_LBL' => 'Information provided by company',
    'SUPPORT_HISTORY_INFO_PROVIDED_ABRV_LBL' => 'Information provided',
    'SUPPORT_HISTORY_PENDING_INFO_FULL_LBL' => 'Pending company information',
    'SUPPORT_HISTORY_PENDING_INFO_ABRV_LBL' => 'Pending information',

    // Debt Collection
    'DEBT_COLLECTION_TXT' => 'debt collection',
    'DEBT_COLLECTOR_INFO_TXT' => 'Debt Collector Information',
    'CREDITOR_INFO_TXT' => 'Creditor Information',
    'SEND_TO_CO_TXT' => 'Send to Company',
    'DC_PHONE_CALLED_BY_CO' => 'What phone number was called by the company',
    'DC_LAST_4_SSN' => 'Last four digits of SSN',
    'DC_CONSENT_TO_FILE' => 'Consent to file debt collection complaint',
    'DC_RELATED_CASE_NUMBER' => 'Related Case Number',
    'DC_CONTACT_COMMANDING_OFC' => 'Did the debt collector contact your commanding officer',
    'DC_LEGAL_THREAT' => 'Did the debt collector threaten you with UCMJ or legal action',
    'DC_COMPANY_NAME' => 'Debt collector name',
    'DC_COMPANY_ADDR' => 'Debt collector address',
    'DC_COMPANY_PHONE1' => 'Debt collector phone 1',
    'DC_COMPANY_PHONE2' => 'Debt collector phone 2',
    'DC_COMPANY_REPRESENTATIVE' => 'Debt collector representative',
    'DC_COMPANY_ACCT_NUMBER' => 'Debt collector account number',
    'DC_CREDITOR_NAME' => 'Creditor name',
    'DC_CREDITOR_ADDR' => 'Creditor address',
    'DC_CREDITOR_PHONE1' => 'Creditor phone 1',
    'DC_CREDITOR_PHONE2' => 'Creditor phone 2',
    'DC_CREDITOR_REPRESENTATIVE' => 'Creditor representative',
    'DC_CREDITOR_ACCT_NUMBER' => 'Creditor account number',
    'DC_CREDITOR_SUBMIT_COMPLAINT' => 'Submit a complaint against creditor',
    'DC_CREDITOR_SEND_COMPLAINT' => 'Send complaint to creditor',
    'DC_COLLECTOR_AFFIRMS_RIGHT_TO_COLLECT' => 'Debt Collector affirms right to collect',
    'DC_CREDITOR_AFFIRMS_RIGHT_TO_COLLECT' => 'Creditor affirms right to collect',
    'DC_DEBT_SOLD' => 'Debt has been sold',
    'DC_DEBT_SOLD_TO' => 'Debt has been sold to',
    'DC_DEBT_SOLD_DATE' => 'Debt sold date',

    'DC_IS_CREDITOR_THREAD_NOTE' => 'AUTOMATED NOTE: This is a Debt Collection "Creditor" case.',
    'DC_IS_DEBT_OWNER_AFFIRMATION' => 'We affirm that our company has the authority to collect the debt in question and can provide appropriate documentation demonstrating this authority to the CFPB upon request.',

    // Payday Lending field labels.
    'PD_LOAN_ORIGINATION_LBL' => 'Where did you get the loan:',
    'PD_LOAN_STATE_LBL' => 'In which state is the store located:',
    'PD_LOAN_URL_LBL' => 'Website:',

    // Congressional Portal
    'COMPANY_RESPONSE_HDR' => 'Company Response',
    'CONSUMER_DISPUTE_HDR' => 'Consumer Dispute of Company Response',
   
    // Consent & Company Comment
    'CONSENT_STATUS_HEADER' => 'Consumer Consent',
    'CONSENT_STATUS' => 'Consent to publish granted:',
    'COMPANY_COMMENT_HEADER' => 'OPTIONAL COMPANY PUBLIC RESPONSE',
    'COMPANY_COMMENT' => 'Company Public Response',
    'COMPANY_COMMENT_INSTRUCTIONS' => 'Select the option that best describes your assessment of the complaint.<br>You have up to 180 days from when the complaint was sent to your company to provide a public response.<br><br>',

    // Submit a Ticket
    'POPULATED_OS_AND_BROWSER' => "Your current Operating System and Browser versions are populated below.  If you are submitting a ticket about an issue when using a different Operating System or Browser, you can update this information below.",

  );

	if(isset($labels[$label_def])) {
		return $labels[$label_def];
	} else {
		return null;
	}
}
// moved to separate config helper
function getSettingOld($config_def) {
	$config = array(//bank case list options

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
			'Past due'));

	if(isset($config[$config_def])) {
		return $config[$config_def];
	} else {
		return null;
	}
}
