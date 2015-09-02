<rn:meta title="#rn:msg:SUPPORT_HISTORY_LBL#" template="cfpb.php" clickstream="incident_list" login_required="true" />
<div id="rn_PageTitle" class="rn_QuestionList">
</div>
<div id="rn_PageContent" class="rn_QuestionList">
  <rn:widget path="custom/instAgent/search/SearchCFPB" report_id="#rn:php:getSetting('SUPPORT_HISTORY_REPORT_ID')#" search_prod="false" search_cat="false" search_state="false" />
  <div class="rn_Module">
    <div class="rn_Padding">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="reports/ResultInfo2" report_id="#rn:php:getSetting('SUPPORT_HISTORY_REPORT_ID')#" add_params_to_url="p,c"/>
        <rn:widget path="reports/Grid2" label_caption="#rn:msg:SUPPORT_HISTORY_LBL#" report_id="#rn:php:getSetting('SUPPORT_HISTORY_REPORT_ID')#"/>
        <rn:widget path="reports/Paginator" report_id="#rn:php:getSetting('SUPPORT_HISTORY_REPORT_ID')#" maximum_page_links="27"/>
    </div>
  </div>
</div>
