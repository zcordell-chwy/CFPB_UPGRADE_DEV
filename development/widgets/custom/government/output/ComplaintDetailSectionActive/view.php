<rn:meta controller_path="custom/government/output/ComplaintDetailSectionActive"/>

      <div id="rn_AdditionalInfo">

	<!-- ++++++++++ What Happpened? ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('CC_REVIEW_WHAT_HEAD');?></h3>

        <? if(!$this->data['mt']['display_money_trans_fields']) { ?>
              <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.prod" label="Product:" show_both_levels="true" />
        <? } else { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.prod" label="Product:" show_both_levels="false" />
        <? } ?>

        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.cat" label="Issue:" show_both_levels="true" />

	<? if($this->data['mt']['display_money_trans_fields']) { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$transfer_number" label="Transaction ID/Number"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$amount_transferred" label="Amount transferred"
              append_additional_field_value_for="incidents.c$currency_type_transferred"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$transfer_date" label="Date of transfer" hide_time="true"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$error_amount" label="Amount of error"
              append_additional_field_value_for="incidents.c$currency_type_error"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$date_issue_occurred" label="Date of error" hide_time="true"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$funds_date" label="Funds promised date" hide_time="true"/>
        <? } ?>

        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$what_happened" label="Describe what happened:" />

	<? if(!$this->data['mt']['display_money_trans_fields'] && !$this->data['dc']['display_dc_fields']) { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$mortg_this_is_about" label="This is about:" />
          <?/* Forclosure info */?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$concern_about_foreclosure"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$missed_payment_or_default"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$is_date_foreclosure_scheduled"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$date_scheduled_foreclosure"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$pay_company_avoid_foreclosure"/>

          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$nbccestvalue" label="Monetary loss:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$dtccissuehappen" label="Date of incident:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$rcontactedccissuer" label="Contacted CC issuer:" />
	<? } ?>



	<!-- ++++++++++ Attachments ++++++++++ -->
	<script>
	    $(document).ready(function() {
                // hide attachments section if no attachments
	        if(!$('#attach_header').find('.rn_DataValue').html())
        	    $('#attach_header').addClass('rn_Hidden');
	    });
	</script>

        <div id="attach_header">
          <h3 class="rn_HeadingBar">Attachment</h3>
          <? if($this->data['userType'] == 'state' && in_array($this->data['comp_status_archive'], array(1427, 1428, 1429, 1430))): // Closed with monetary relief, Closed with non-monetary relief, Closed with explanation, Closed ?>
              <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.fattach" label_input="#rn:msg:FILE_ATTACHMENTS_LBL#" initial_attachments_only="false" />
          <? else: ?>
              <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.fattach" label_input="#rn:msg:FILE_ATTACHMENTS_LBL#" initial_attachments_only="true" />
          <? endif; ?>
          <?//CFPB decided to not show attachments when company status is  "Incorrect compan", "Misdirected", "Alerted CFPB"*/?>
          <?/*<rn_:widget path="custom/instAgent/output/GenericFileAttachments" table="CO_Invest$Investigation" lookup="comp_id" lookup_id="#rn:php:getUrlParm('comp_id')#" />*/?>
        </div>
        <br/>

        <div class="ps_reviewGroup">
            <rn:widget path="custom/congressional/output/InboundReferralList" incident_id="#rn:php:$this->data['i_id']#" />
        </div>


        <!-- ++++++++++ Resolution ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('CC_REVIEW_RES_HEAD');?></h3>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$fairresolution" label="Desired resolution:" />
        <br/>

        <!-- ++++++++++ Company Response ++++++++++ -->
        <? if($this->data['userType'] == 'state' && in_array($this->data['comp_status_archive'], array(1427, 1428, 1429, 1430))): // Closed with monetary relief, Closed with non-monetary relief, Closed with explanation, Closed ?>
          <h3 class="rn_HeadingBar"><?=getLabel('CC_REVIEW_CO_RESP_HEAD');?></h3>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_explanation_of_closure" label="Explanation of Closure:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_describe_relief" label="Relief:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_dollar_amount" label="Dollar Amount:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_provide_a_response" label="Response:"/>
        <? endif; ?>
        <br/>

	<!-- ++++++++++ Consumer Info ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('CONSUMER_INFO');?></h3>
	<? if($this->data['mt']['display_money_trans_fields'] || $this->data['dc']['display_dc_fields']) { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="contacts.c$salutation" label="Salutation:"/>
        <? } ?>

        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="contacts.first_name" label="First name:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="contacts.c$middle_name" label="Middle name:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="contacts.last_name" label="Last name:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="contacts.c$suffix" label="Suffix:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.contact_email" label="#rn:msg:EMAIL_ADDR_LBL#:" />
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="contacts.ph_mobile" label="Phone:" />
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccmail_addr1" label="Street:"/>
	<rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccmail_addr2" label="Apartment, Suite, Building:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccmail_city" label="City:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccmail_state" label="State:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccmail_zip" label="ZIP Code:"/>
	<rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccmail_country" label="Country:"/>

	<? if($this->data['mt']['display_money_trans_fields']) { ?>
          <br/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$sender_recipient" label="I am (the):"/>
        <? } ?>

	<? if($this->data['dc']['display_dc_fields']) { ?>
          <br/>
          <? /* spec says don't show last 4
          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="Complainant's last four digits of SSN" name="LastFourSSN" table="DebtCollection" namespace="Complaints"/>
           */ ?>

          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="Consent to file complaint" name="ConsentToFile" table="DebtCollection" namespace="Complaints"/>
          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="Phone number called by company" name="ComplainantPhoneCalledByComp" table="DebtCollection" namespace="Complaints"/>
          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel('SEND_TO_CO_TXT')#" name="c$send_to_company" table="incidents" />
          <br/>
         <? } ?>

        <?/* Need to add contact address fields*/?>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_myself" label="On behalf of myself:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_someone" label="On behalf of someone else:"/>

	<? if($this->data['showOnBehalfFields']) { ?>
          <br/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$relationship" label="Relationship:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_salutation" label="Salutation:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_first" label="First Name:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_mi" label="Middle Name:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_last" label="Last Name:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_suffix" label="Suffix:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_addr1" label="Mailing address:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_addr2" label="Apartment, suite, building:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_city" label="City:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_state" label="State:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_zip" label="ZIP code:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$onbehalf_country" label="Country"/>
          <br/>
        <? } ?>

	<? if($this->data['mt']['display_money_trans_fields']) { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$sender_recipient_3rdparty" label="This person is the:"/>
        <? } ?>

        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$amwasservicemember" label="I am or was a servicemember:"/>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$amdependent" label="I am or was a dependent of a servicemember:"/>
	<br/>
        <? /* not displayed according to spec revision 5/31
        <rn:widget path="custom/output/FormReviewFieldDisplay4" label="Did the debt collector contact your commanding officer?" name="DCContactedCommOfficer"
            table="DebtCollection" namespace="Complaints"/>

        <rn:widget path="custom/output/FormReviewFieldDisplay4" label="Did the debt collector threaten you with UCMJ or legal action?" name="DCThreatUMCJLegalAction"
            table="DebtCollection" namespace="Complaints"/>
         */ ?>

        <br/>



	<!-- ++++++++++ Product Info ++++++++++ -->
        <h3 class="rn_HeadingBar"><?=getLabel('PRODUCT_INFO');?></h3>

	<? if($this->data['mt']['display_money_trans_fields']) { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_co_name" label="Company name:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_addr1" label="Street:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_addr2" label="Apartment, suite, building:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_city" label="City:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_state" label="State:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_zip" label="ZIP code:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_country" label="Country:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$rccnumber" label="Account number:" mask_ssn_all="true"/>
          <br/>
          <table id="mt_sender_recipient_table">
            <tbody>
              <tr>
                <td><div class="rn_HeadingBar_subhead">SENDER INFORMATION</div></td>
                <td><div class="rn_HeadingBar_subhead">RECIPIENT INFORMATION</div></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$salutation_sender" label="Salutation:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$salutation_recipient" label="Salutation:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$sender_first" label="First name:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$recipient_first" label="First name:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$sender_mi" label="Middle name:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$recipient_mi" label="Middle name:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$sender_last" label="Last name:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$recipient_last" label="Last name:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$sender_suffix" label="Suffix:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$suffix_recipient" label="Suffix:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$email_sender" label="#rn:msg:EMAIL_ADDR_LBL#:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$email_recipient" label="#rn:msg:EMAIL_ADDR_LBL#:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$phone_sender" label="Phone:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$phone_recipient" label="Phone:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$addr1_sender" label="Street:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$addr1_recipient" label="Street:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$addr2_sender" label="Apartment, suite, building:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$addr2_recipient" label="Apartment, suite, building:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$city_sender" label="City:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$city_recipient" label="City:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$state_sender" label="State:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$state_recipient" label="State:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$zip_sender" label="ZIP Code:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$zip_recipient" label="ZIP Code:"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$country_sender" label="Country:"/></td>
                <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$country_recipient" label="Country:"/></td>
              </tr>
	      <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <? if($showTransMethod){ ?>
                  <table>
                    <tbody>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$transfer_method" label="Where did the transaction take place?"/></td>
                      </tr>
		      <? if($showTransMethodSendAgent){ ?>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$company_sender_agent" label="Company name:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$addr1_sender_agent" label="Company Address:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$city_sender_agent" label="City:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$state_sender_agent" label="State:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$zip_sender_agent" label="ZIP code:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$country_sender_agent" label="Country:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$phone_sender_agent" label="Phone:"/></td>
                      </tr>
		      <? } ?>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$sending_website_mobile_app" label="Website or mobile app name:"/></td>
                      </tr>
                  </table>
                  <? } ?>
                </td>
                <td>
                  <? if($showReceiptMethod){ ?>
                  <table>
                    <tbody>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$receipt_method" label="Receipt Method"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$company_receiving_agent" label="Company name:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$addr1_receiving_agent" label="Company Address:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$city_receiving_agent" label="City:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$state_receiving_agent" label="State:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$zip_receiving_agent" label="ZIP code:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$country_receiving_agent" label="Country:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$receiving_website_mobile_app" label="Website or mobile app name:"/></td>
                      </tr>
                      <tr>
                        <td><rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$recipient_account_number" label="Account number:" mask_ssn_all="true"/></td>
                      </tr>
                    </tbody>
                  </table>
                  <? } ?>
                </td>
              </tr>
            </tbody>
          </table>
	<? } else if($this->data['dc']['display_dc_fields']) { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_co_name" label="Company name:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_addr1" label="Street:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_addr2" label="Apartment, suite, building:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_city" label="City:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_state" label="State:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_zip" label="ZIP code:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_country" label="Country:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$rccnumber" label="Account number:" break_value="true"/>

          <? /* Additional Debt Collection fields added as part of Payday Lending project. */ ?>
          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_ORIGINATION_LBL' )#" name="HowWasLoanObtained" table="DebtCollection" namespace="Complaints" />
          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_STATE_LBL' )#" name="LoanStoreState" table="DebtCollection" namespace="Complaints" />
          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_URL_LBL' )#" name="LoanSiteURL" table="DebtCollection" namespace="Complaints" />

          <br/>
          <table id="mt_sender_recipient_table">
            <tbody>
              <tr>
                <td><div class="rn_HeadingBar_subhead">DEBT COLLECTOR INFORMATION</div></td>
                <td><div class="rn_HeadingBar_subhead">CREDITOR INFORMATION</div></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Company name" name="DCCompanyName" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Company name" name="CreditorCompanyName" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Address 1" name="DCCompanyAddr1" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Address 1" name="CreditorCompanyAddr1" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Address 2" name="DCCompanyAddr2" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Address 2" name="CreditorCompanyAddr2" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="City" name="DCCompanyCity" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="City" name="CreditorCompanyCity" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="State" name="DCCompanyState" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="State" name="CreditorCompanyState" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Zip Code" name="DCCompanyZip" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Zip Code" name="CreditorCompanyZip" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Country" name="DCCompanyCountry" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Country" name="CreditorCompanyCountry" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Phone 1" name="DCOutboundPhone1" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Phone 1" name="CreditorOutboundPhone1" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Phone 2" name="DCOutboundPhone2" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Phone 2" name="CreditorOutboundPhone2" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Representative Name" name="DCRepName" table="DebtCollection" namespace="Complaints"/></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Representative Name" name="CreditorRepName" table="DebtCollection" namespace="Complaints"/></td>
              </tr>
              <tr>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Account Number" name="DCAcctNumber" table="DebtCollection" namespace="Complaints"  mask_ssn_all="true" /></td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Account Number" name="CreditorAcctNumber" table="DebtCollection" namespace="Complaints"  mask_ssn_all="true" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td><rn:widget path="custom/output/FormReviewFieldDisplay4" label="Submit a complaint against this company?" name="FileComplaintAgainstCreditor"
                        table="DebtCollection" namespace="Complaints"/></td>
              </tr>
            </tbody>
          </table>
        <? } else { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$name_on_card" label="Name on account:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$rccnumber" label="Account/Loan number:" mask_data="true" num_digits_to_mask="9" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$consumer_filed_dispute" label="Dispute filed by consumer:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$dispute_number" label="Dispute number:" />
          <rn:widget path="custom/instAgent/output/CompanyNameDisplay" label="Company name:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccbill_addr1" label="Street:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccbill_addr2" label="Apartment, suite, building:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccbill_city" label="City:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccbill_state" label="State:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$ccbill_zip" label="ZIP code:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_addr1" label="Street:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_addr2" label="Apartment, suite, building:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_city" label="City:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_state" label="State:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_zip" label="ZIP code:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_country" label="Country:" />
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cc_issuer_phone" label="Phone:" />

          <? if( $this->data['pd']['display_pd_fields'] ){ ?>
                <rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_ORIGINATION_LBL' )#" name="HowWasLoanObtained" table="Payday" namespace="Complaints" />
                <rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_STATE_LBL' )#" name="LoanStoreState" table="Payday" namespace="Complaints" />
                <rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_URL_LBL' )#" name="LoanSiteURL" table="Payday" namespace="Complaints" />
	  <? } else if($this->data['cl']['display_cl_fields']) { ?>
		<rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_ORIGINATION_LBL' )#" name="HowWasLoanObtained" table="ConsumerLoan" namespace="Complaints" />
		<rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_STATE_LBL' )#" name="LoanStoreState" table="ConsumerLoan" namespace="Complaints" full_state_name="true" />
		<rn:widget path="custom/output/FormReviewFieldDisplay4" label="#rn:php:getLabel( 'PD_LOAN_URL_LBL' )#" name="LoanSiteURL" table="ConsumerLoan" namespace="Complaints" />
          <? } ?>
	<? } ?>
        <br/>



	<!-- ++++++++++ Case Details ++++++++++ -->
        <h3 class="rn_HeadingBar">Case Details</h3>
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.ref_no" label="Case number:" />
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$added_to_case" label="Added to Case:" />
        <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$referred_to" label="Referred to:" suppress_field_if_value_equals="FTC" />
	    <? if($this->data['dc']['display_dc_fields']) { ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$related_case" label="Related Case Number"/>
          <rn:widget path="custom/output/FormReviewFieldDisplay4" label="Debt collector affirms the right to collect" name="IsDebtOwner" table="DebtCollection" namespace="Complaints"/>
        <? } ?>
      </div>
      <br>
