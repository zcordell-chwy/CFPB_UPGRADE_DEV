<rn:meta title="Data Export Job Detail" template="cfpb.php" clickstream="incident_detail" login_required="true" />
<div id="rn_PageTitle" class="rn_QuestionList">
</div>
<div id="rn_PageContent" class="rn_QuestionList">
  <div class="rn_Module">
    <div class="rn_Padding">
        <h2><?= getLabel('EXPORT_JOBS_DETAIL_HEADING') ?></h2>

        <rn:widget path="custom/export/JobDetail" label_caption="#rn:php:getLabel('EXPORT_JOBS_DETAIL_HEADING')#" report_id="#rn:php:getSetting('EXPORT_JOBS_COMPONENT_REPORT_ID')#" />
    </div>
  </div>
</div>
