<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define hooks to extend Customer Portal functionality. Hooks allow
| you to specify custom code that you wish to execute before and after many
| important events that occur within Customer Portal. This custom code can modify data,
| perform custom validation, and return customized error messages to display to your users.
|
| Hooks are defined by specifying the location where you wish the hook to run as the array index
| and setting that index to an array of 3 items, class, function, and filepath. The 'class' index
| is the case-sensitive name of the custom model you wish to use. The 'function' index is the name
| of the function within the 'class' you wish to call. Finally, the 'filepath' is the location to
| your model, which will automatically be prefixed by models/custom/. The 'filepath' index only
| needs a value if your model is contained within a subfolder
|
|-----------------
| Hook Locations
|-----------------
|
|     pre_allow_contact     - Called before allowing a contact to access content.
|     pre_login             - Called immediately before user becomes logged in
|     post_login            - Called immediately after user has been logged in
|     pre_logout            - Called immediately before user logs out
|     post_logout           - Called immediately after user logs out
|     pre_contact_create    - Called before Customer Portal validation and contact is created
|     post_contact_create   - Called immediately after contact has been created
|     pre_contact_update    - Called before Customer Portal validation and contact is updated
|     post_contact_update   - Called immediately after contact is updated
|     pre_incident_create   - Called before Customer Portal validation and incident is created
|     post_incident_create  - Called immediately after incident has been created
|     pre_incident_update   - Called before Customer Portal validation and incident is updated
|     post_incident_update  - Called immediately after incident is updated
|     pre_feedback_submit   - Called before both site and answer feedback
|     post_feedback_submit  - Called after both site and answer feedback is submitted
|     pre_login_redirect    - Called before user is redirected to a new page because they are not logged in
|     pre_pta_decode        - Called before PTA string is decoded and converted to pairdata
|     pre_pta_convert       - Called after PTA string has been decoded and converted into key/value pairs
|     pre_page_render       - Called before page content is sent to the browser
|     pre_report_get        - Called before a report is retrieved
|     pre_page_set_selection- Called before the user is redirected to a specific page set
|
|
| Please refer to the documentation for further information
|
|------------------
|Examples
|------------------
|
| Example 1 - Call the ImmediateIncidentFeedback (located at /models/custom/ImmediateIncidentFeedback.php)
|             model's sendFeedback function immediately after an incident is created.
|
| $rnHooks['post_incident_create'] = array(
|        'class' => 'ImmediateIncidentFeedback',
|        'function' => 'sendFeedback',
|        'filepath' => ''
|    );

|=========================================================================================================

| Example 2 - Call the CustomContactModel (located at /models/custom/contact/CustomContactModel.php)
|             model's copyLogin function immediately before contacts are created.
|
| $rnHooks['pre_contact_create'] = array(
|        'class' => 'CustomContactModel',
|        'function' => 'copyLogin',
|        'filepath' => 'contact'
|    );
|=========================================================================================================

| Example 3 - First call the MyFeedbackModel (located at /models/custom/feedback/MyFeedbackModel.php)
|             model's customValidation function and then call ImmediateIncidentFeedback (located at
|             /models/custom/ImmediateIncidentFeedback.php) model's sendFeedback function. Both of
|             which will be called before feedback has been submitted.
|
| $rnHooks['post_feedback_submit'][] = array(
|        'class' => 'MyFeedbackModel',
|        'function' => 'customValidation',
|        'filepath' => 'feedback'
|    );
| $rnHooks['post_feedback_submit'][] = array(
|        'class' => 'ImmediateIncidentFeedback',
|        'function' => 'sendFeedback',
|        'filepath' => ''
|    );
|=========================================================================================================
*/

/**
 * Apply report search criteria for org and bank_statuses
 */

$rnHooks['pre_report_get'] = array(
        'class' => 'report_model2',
        'function' => 'applyReportFilter',
        'filepath' => ''
);

/**
 * Before page is rendered, filter on case and support history detail page
 * to mark the incident as read by setting isunread custom field to 'No' 
 */
/*
$rnHooks['pre_page_render'][] = array(
	'class' => 'Splash_model',
	'function' => 'splash',
	'filepath' => ''
);
*/
$rnHooks['pre_page_render'][] = array(
        'class' => 'page_render_model',
        'function' => 'applyPageAttributes',
        'filepath' => ''
);