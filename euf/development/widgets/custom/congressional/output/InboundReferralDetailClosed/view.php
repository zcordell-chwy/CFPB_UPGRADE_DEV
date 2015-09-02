<rn:meta controller_path="custom/congressional/output/InboundReferralDetailClosed" />
      <div id="rn_AdditionalInfo">

	<!-- ++++++++++ What Happpened? ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('CC_REVIEW_WHAT_HEAD');?></h3>

        <? if(!$this->data['mt']['display_money_trans_fields']) { ?>
              <rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.prod" label="Product:" show_both_levels="true" />
        <? } else { ?>
          <rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.prod" label="Product:" show_both_levels="false" />
        <? } ?>

        <rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.cat" label="Issue:" show_both_levels="true" />
        <rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.c$what_happened" label="Describe what happened:" />

	<!-- ++++++++++ Attachments ++++++++++ -->
        <div id="attach_header" class="rn_Hidden">
          <h3 class="rn_HeadingBar">Attachment</h3>
          <rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.fattach" label_input="#rn:msg:FILE_ATTACHMENTS_LBL#" initial_attachments_only="true" />
          <?//CFPB decided to not show attachments when company status is  "Incorrect compan", "Misdirected", "Alerted CFPB"*/?>
          <?/*<rn_:widget path="custom/instAgent/output/GenericFileAttachments" table="CO_Invest$Investigation" lookup="comp_id" lookup_id="#rn:php:getUrlParm('comp_id')#" />*/?>
        </div>
		<script>
			$(document).ready(function() {
				// hide attachments section if no attachments
				if($('#attach_header').find('.rn_DataValue').html())
					$('#attach_header').removeClass('rn_Hidden');
			});
		</script>

        <br/>

	<!-- ++++++++++ Referrals ++++++++++ -->
        <rn:widget path="custom/congressional/output/InboundReferralList" />


	<!-- ++++++++++ Resolution ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('CC_REVIEW_RES_HEAD');?></h3>
        <rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.c$fairresolution" label="Desired resolution:" />
        <br/>

	<!-- ++++++++++ Consumer Info ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('CONSUMER_INFO');?></h3>

        <rn:widget path="custom/congressional/output/DataDisplayIA" name="contacts.first_name" label="First name:"/>
        <rn:widget path="custom/congressional/output/DataDisplayIA" name="contacts.last_name" label="Last name:"/>
        <rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.contact_email" label="#rn:msg:EMAIL_ADDR_LBL#:" />
	<br>
	<h3 class="rn_HeadingBar">Case Details</h3>
	<rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.ref_no" label="Case number:" />
	<rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.c$consumer_statuses" label="Case Status:" />
	<rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.c$cc_co_name" label="Company name:" />
	<rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.c$referred_to" label="Referred to:" />
        <br>

	<!-- ++++++++++ Consumer Dispute ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('CONSUMER_DISPUTE_HDR');?></h3>

		<rn:widget path="custom/congressional/output/DataDisplayIA" name="incidents.c$dispute_reason" label="Dispute?" />

        <br/>
      </div>
      <br>
