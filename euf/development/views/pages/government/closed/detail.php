<rn:meta title="Complaint Detail" template="cfpb.php" login_required="true" clickstream="incident_view" />

<rn:widget path="custom/utils/AuthorizedUser" page_type="federal,state" />

<div id="rn_PageContent" class="rn_QuestionDetail">
	<div class="rn_Padding">

    <button onclick="window.history.go(-1);return false;" class="abutton ps_noprint">Back</button>

    <br /><br />
    
    <rn:widget path="custom/government/output/ComplaintDetailSectionClosed"/>

  </div>
</div>

<div class="rn_Padding">
    <div id="rn_DetailTools">
	    <rn:widget path="custom/utils/PrintPageLink" />
    </div>
</div>

