<rn:meta title="Complaint Detail" template="cfpb_lite.php" login_required="true" clickstream="incident_view" />

<rn:widget path="custom/utils/AuthorizedUser" page_type="federal,state" />

<div id="rn_PageContent" class="rn_QuestionDetail">
  <div class="rn_Padding">

    <br/><br/>

    <rn:widget path="custom/instAgent/output/ComplaintDetailSection"/>

  </div>
</div>

<script>
  $(document).ready(function() {
    window.print();
    window.close();
  } );
</script>
