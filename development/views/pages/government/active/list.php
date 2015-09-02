<rn:meta title="Case History" template="cfpb.php" clickstream="incident_list" login_required="true" />

<rn:widget path="custom/utils/AuthorizedUser" page_type="state" />

<div id="rn_PageTitle" class="rn_QuestionList">
</div>
<div id="rn_PageContent" class="rn_QuestionList">
  <rn:widget path="custom/instAgent/search/SearchCFPB" report_id="#rn:php:getSetting('GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID')#"
    hide_prod="#rn:php:getSetting('CREDIT_REPORTING_PROD_ID')#," hide_cat="#rn:php:getSetting('CREDIT_REPORTING_CAT_ID')#," />
  <div class="rn_Module">
    <div class="rn_Padding">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="custom/instAgent/reports/ResultInfo2" report_id="#rn:php:getSetting('GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID')#" add_params_to_url="p,c" per_page="25" />
        <rn:widget path="custom/instAgent/reports/Grid2Custom" label_caption="#rn:msg:SUPPORT_HISTORY_LBL#" report_id="#rn:php:getSetting('GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID')#" per_page="25" />
        <rn:widget path="custom/instAgent/reports/Paginator" report_id="#rn:php:getSetting('GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID')#" maximum_page_links="27" per_page="25"  />
        <rn:widget path="custom/utils/PopupLink" label_link="#rn:php:getLabel('TERMS_OF_SERVICE')#"
            popup_msg="#rn:php:getLabel('TERMS_OF_SERVICE_POPUP')#" class_name="tos" />
        <rn:widget path="custom/export/ReportSelector" export_type="Gov Portal - Active" />
    </div>
</div>
</div>
