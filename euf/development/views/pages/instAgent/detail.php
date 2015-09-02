<rn:meta title="Complaint Detail" template="cfpb.php" login_required="true" clickstream="incident_view" />

<rn:widget path="custom/utils/AuthorizedUser" page_type="company" />

<div id="rn_PageContent" class="rn_QuestionDetail">
	<div class="rn_Padding">

    <button onclick="window.history.go(-1);return false;" class="abutton ps_noprint">Back</button>

    <rn:widget path="custom/instAgent/output/ComplaintDetailSection" />

    </div>
</div>

<div class="rn_Padding">
    <div id="rn_DetailTools">
	    <rn:widget path="custom/utils/PrintPageLink" />
    </div>
</div>

