<rn:meta title="Data Export Jobs Status" template="cfpb.php" clickstream="incident_list" login_required="true" />
<div id="rn_PageTitle" class="rn_QuestionList">
</div>
<div id="rn_PageContent" class="rn_QuestionList">
  <div class="rn_Module">
    <div class="rn_Padding">
        <h2 class="rn_ScreenReaderOnly">#rn:php:getLabel('EXPORT_JOBS_HEADING')#</h2>
        <rn:widget path="reports/ResultInfo2" report_id="#rn:php:getSetting('EXPORT_JOBS_REPORT_ID')#" />
        <rn:widget path="reports/Grid2" label_caption="#rn:php:getLabel('EXPORT_JOBS_HEADING')#" report_id="#rn:php:getSetting('EXPORT_JOBS_REPORT_ID')#" />
        <rn:widget path="reports/Paginator" report_id="#rn:php:getSetting('EXPORT_JOBS_REPORT_ID')#" maximum_page_links="27" />
    </div>
  </div>
</div>
