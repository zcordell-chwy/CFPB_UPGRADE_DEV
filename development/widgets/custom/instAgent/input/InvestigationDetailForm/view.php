<rn:meta controller_path="custom/instAgent/input/InvestigationDetailForm"
    js_path="custom/instAgent/input/InvestigationDetailForm"
    base_css="custom/instAgent/input/InvestigationDetailForm"
    compatibility_set="November '09+"/>

    <form id="rn_UpdateQuestion" method="post" action="" onsubmit="return false;">
        <div id="rn_ErrorLocation"></div>
        <br/>

<? $co_status = $this->data['attrs']['co_status']; ?>

<? switch ($this->data['js']['bank_statuses']) {
    // need to establish which selection menu will trigger show/hide behavior
    // (mirrors logic in logic.js file that shows/hides menu)

     case $co_status['CO_STATUS_NO_RESPONSE']:      
        $form_field_to_watch = 'incidents.c$cfpb_status';
        break;
        
    case $co_status['CO_STATUS_IN_PROGRESS']:
    case $co_status['CO_STATUS_SENT_TO_COMPANY']:
    case $co_status['CO_STATUS_PAST_DUE']:
    case $co_status['CO_STATUS_REDIRECTED']:
        if ($this->data['js']['is_response'])
            $form_field_to_watch = 'incidents.c$company_status_2';
        else
            $form_field_to_watch = 'incidents.c$company_status_1';
        break;
                
    default:      
        $form_field_to_watch = 'incidents.c$cfpb_status';
        break;
    }
        
?>

<? switch ($this->data['js']['bank_statuses']) {
    // show archive
    case $co_status['CO_STATUS_CLOSED_W_RELIEF']:
    case $co_status['CO_STATUS_CLOSED_WO_RELIEF']:
    case $co_status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']:
    case $co_status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF']:
    case $co_status['CO_STATUS_CLOSED_W_EXPLANATION']:
    case $co_status['CO_STATUS_CLOSED']:
    case $co_status['CO_STATUS_INFO_PROVIDED']:
    case $co_status['CO_STATUS_ALERTED_CFPB']:
  	case $co_status['CO_STATUS_FULL_RESOLUTION']:
	case $co_status['CO_STATUS_PARTIAL_RESOLUTION']:
	case $co_status['CO_STATUS_NO_RESOLUTION']:
    case $co_status['CO_STATUS_SENT_TO_REGULATOR']:
    case $co_status['CO_STATUS_DUPLICATE_CASE']:
    case $co_status['CO_STATUS_MISDIRECTED']:
    case $co_status['CO_STATUS_REDIRECTED']:

    // show under review
    case $co_status['CO_STATUS_NO_RESPONSE']:

    // show active
    case $co_status['CO_STATUS_IN_PROGRESS']:
    case $co_status['CO_STATUS_SENT_TO_COMPANY']:
    case $co_status['CO_STATUS_PAST_DUE']:
?>
    <div id="update_fields">
        <div id="step1_comp" class="rn_Hidden">
            <rn:widget path="custom/instAgent/input/SelectionLogicInput" name="incidents.c$company_status_1"
                label_nothing_selected="#rn:php:getLabel('CHOOSE_CMD')#"
                remove_menu_items="#rn:php:$co_status['CO_STATUS_CLOSED_W_RELIEF']#,#rn:php:$co_status['CO_STATUS_CLOSED_WO_RELIEF']#,#rn:php:$co_status['CO_STATUS_MISDIRECTED']#"/>
        </div>
               
        <div id="step1_comp_update" class="rn_Hidden">
            <rn:widget path="custom/instAgent/input/SelectionLogicInput" name="incidents.c$company_status_2"
                label_nothing_selected="#rn:php:getLabel('CHOOSE_CMD')#"
                remove_menu_items="#rn:php:$co_status['CO_STATUS_CLOSED_W_RELIEF']#,#rn:php:$co_status['CO_STATUS_CLOSED_WO_RELIEF']#,#rn:php:$co_status['CO_STATUS_MISDIRECTED']#"/>
        </div>
        
        <div id="step1_cfpb" class="rn_Hidden">
            <rn:widget path="custom/instAgent/input/SelectionLogicInput" name="incidents.c$cfpb_status"
                label_nothing_selected="#rn:php:getLabel('CHOOSE_CMD')#"
                remove_menu_items="#rn:php:$co_status['CO_STATUS_CLOSED_W_RELIEF']#,#rn:php:$co_status['CO_STATUS_CLOSED_WO_RELIEF']#,#rn:php:$co_status['CO_STATUS_MISDIRECTED']#,#rn:php:$co_status['CO_STATUS_REDIRECTED']#"
                label_input="#rn:php:getLabel('CFPB_STATUS_DESC')#" />
        </div>
               
        <div id="step2" class="rn_Hidden">
            <br/>
                <div id="step2_desc"></div>
            <br/>

            <!-- Alerted CFPB Reason (all products) -->
            <rn:widget path="custom/utils/hideElement" control_element="al_cfpb" form_field_name="#rn:php:$form_field_to_watch#"
                form_field_value="#rn:php:$co_status['CO_STATUS_ALERTED_CFPB']#">
            <div id="al_cfpb">
                <? /* need optional required attribute based on visibility, so switching to SelectionLogicInput3 
                <rn:widget path="custom/instAgent/input/SelectionLogicInput" name="incidents.c$alerted_cfpb_reason" label_nothing_selected="#rn:php:getLabel('CHOOSE_CMD')#">
                */ ?>
                 <rn:widget path="custom/input/SelectionLogicInput3" name="incidents.c$alerted_cfpb_reason" 
                    label_input="Reason for alerting CFPB"
                    label_nothing_selected="#rn:php:getLabel('CHOOSE_CMD')#"
                    optional_on_hide="true"
                    required="true" 
                    is_checkbox="false"
                    label_required="Reason for alerting CFPB must be selected">
               
                 <br>
            </div> 
        
            <div id="step2_duplicate" class="rn_Hidden">
                <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$added_to_case"
                    label_input="#rn:php:getLabel('ENTER_ORIG_CASE_NO')#" />
                    
            </div>

            <div id="step2_comp_dispute_filed" class="rn_Hidden">
                <? if( $this->data['js']['display_credit_reporting_field'] ): ?>
                  <span class="note"><?= getLabel('COMPANY_DISPUTE_FILED_NOTE'); ?></span>
                  <rn:widget path="custom/instAgent/input/SelectionLogicInput" name="incidents.c$company_no_dispute_filed"
                      label_input="#rn:php:getLabel('COMPANY_DISPUTE_FILED')#"
                      label_style="left:5px; top:-2px;"
                      submit_positive_value_only=true /> <? /* style_custom="height:10px;" */ ?>
                <? endif; ?>
            </div>

            <div id="step2_comp" class="rn_Hidden">
                <div id="step2_comp_relief" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$comp_describe_relief"
                    label_input="#rn:php:getLabel('DESCRIBE_RELIEF')#" max_chars="3900" />
                </div>
                <div id="step2_comp_relief_amount" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$comp_dollar_amount"
                    label_input="#rn:php:getLabel('DOLLAR_AMOUNT_LBL')#" label_before_input='$' />
                </div>
                <div id="step2_comp_redirect" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/ComplaintHandlerInput" name="orgcomplainthandler.authorizedcomplainthandler"
                    label_input="#rn:php:getLabel('REDIRECT_CASE_TO_LBL')#"
                    error_msg="#rn:php:getLabel('REDIRECT_CASE_NOTHING_SELECTED_LBL')#" />
                    <div class="instructions"><a href="/app/add_complaint_handler"><?= getLabel('ORG_COMPLAINT_HANDLER_SUBJECT'); ?></a></div>
                </div>
                <div id="step2_comp_info" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$comp_provide_a_response"
                    label_input="#rn:php:getLabel('PROVIDE_A_RESPONSE_TXT')#" max_chars="3900"
                    label_input_sub="#rn:php:getLabel('PROVIDE_A_RESPONSE_SUBTXT')#" />
                </div>
                <div id="step2_comp_explain" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$comp_explanation_of_closure"
                    label_input="#rn:php:getLabel('EXPLANATION_OF_CLOSURE')#" max_chars="3900" />
                </div>

            </div>
            
            <div id="step2_cfpb" class="rn_Hidden">
                <div id="step2_cfpb_relief" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$cfpb_describe_relief"
                    label_input="#rn:php:getLabel('DESCRIBE_RELIEF')#" max_chars="3900" />
                </div>
                <div id="step2_cfpb_relief_amount" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$cfpb_dollar_amount"
                    label_input="#rn:php:getLabel('DOLLAR_AMOUNT_LBL')#" label_before_input='$' />
                </div>
                <div id="step2_cfpb_info" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$cfpb_provide_a_response"
                    label_input="#rn:php:getLabel('PROVIDE_A_RESPONSE_TXT')#" max_chars="3900"
                    label_input_sub="#rn:php:getLabel('PROVIDE_A_RESPONSE_SUBTXT')#" />
                </div>
                <div id="step2_cfpb_explain" class="rn_Hidden">
                    <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$cfpb_explanation_of_closure"
                    label_input="#rn:php:getLabel('EXPLANATION_OF_CLOSURE')#" max_chars="3900" />
                </div>
 

             </div>
    
            <div id="step2_regulator" class="rn_Hidden">
                <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$agency_name"
                    label_input="#rn:php:getLabel('AGENCY_NAME_TXT')#" />
            </div>
            <div id="step2_redirect_explain" class="rn_Hidden">
                <rn:widget path="custom/instAgent/input/TextLogicInput2" name="incidents.c$redirect_explanation"
                    label_input="#rn:php:getLabel('EXPLANATION_OF_REDIRECT_TXT')#" max_chars="3900" />
            </div>


            <div id="debt_collection">
                <!-- -------------------- :: Debt Collections Only :: -------------------- -->
                <? /* the DEBT COLLECTOR fields are the ones that show up in this state */ ?>
                <?if($this->data['dc']['isDebtCollection'] && $this->data['dc']['showDCDebtOwnerFields']){?>
                    <br>
                    <!-- DC: Do you own the debt? -->
                    <? /* show this for any selection other than Choose... */ ?>
                    <rn:widget path="custom/utils/hideElement" control_element="dcowndebt" form_field_name="#rn:php:$form_field_to_watch#"
                        form_field_value="0" inverse_compare="true">
                
                    <div id="dcowndebt">
                       <rn:widget path="custom/input/SelectionLogicInput3" name="Complaints.DebtCollection.DCIsDebtOwner" label_input="#rn:php:getLabel('DC_IS_DEBT_OWNER_AFFIRMATION')#" is_checkbox="false" >
                    </div>
                    <br>
    
                    <!-- DC: Did you sell the debt? -->
                    <rn:widget path="custom/utils/hideElement" control_element="dcselldebt" form_field_name="#rn:php:$form_field_to_watch#"
                        form_field_value="#rn:php:$co_status['CO_STATUS_CLOSED_W_EXPLANATION']#">
                        
                    <div id="dcselldebt">
                       <rn:widget path="custom/input/SelectionLogicInput3" name="Complaints.DebtCollection.DCWasDebtSold" label_input="#rn:php:getLabel('DC_DEBT_SOLD')#" is_checkbox="false" >
    
                        <!-- DC: Name of purchase / date sold -->
                        <rn:widget path="custom/utils/hideElement" control_element="dcnamedatedebt" form_field_name="DebtCollection.DCWasDebtSold" form_field_value="1">
                    
                        <div id="dcnamedatedebt">
                           <rn:widget path="custom/input/TextLogicInput3" name="Complaints.DebtCollection.DCDebtPurchaserName" label_input="#rn:php:getLabel('DC_DEBT_SOLD_TO')#">
                            <br>
                            <rn:widget path="custom/input/DateLogicInput3" name="Complaints.DebtCollection.DCDebtSoldDate" label_input="#rn:php:getLabel('DC_DEBT_SOLD_DATE')# " hide_hours_mins="true" maxdate="true" >
                        </div>
                        <br>
                    </div>
                    <br>

                <? } ?>
   
                <? /* the CREDITOR fields are the ones that show up in this state */ ?>
                <?if($this->data['dc']['isDebtCollection'] && !$this->data['dc']['showDCDebtOwnerFields']){?>
                   <br>
                    <!-- Creditor: Do you own the debt? -->
                    <? /* show this for any selection other than Choose... */ ?>
                    <rn:widget path="custom/utils/hideElement" control_element="owndebt" form_field_name="#rn:php:$form_field_to_watch#"
                        form_field_value="0" inverse_compare="true">
                                      
                    <div id="owndebt">
                       <rn:widget path="custom/input/SelectionLogicInput3" name="Complaints.DebtCollection.CreditorIsDebtOwner" label_input="#rn:php:getLabel('DC_IS_DEBT_OWNER_AFFIRMATION')#" is_checkbox="false">
                    </div>
                    <br>
                
                    <!-- Creditor: Did you sell the debt? -->
                    <rn:widget path="custom/utils/hideElement" control_element="selldebt" form_field_name="#rn:php:$form_field_to_watch#"
                        form_field_value="#rn:php:$co_status['CO_STATUS_CLOSED_W_EXPLANATION']#">
                
                    <div id="selldebt">
                        <rn:widget path="custom/input/SelectionLogicInput3" name="Complaints.DebtCollection.CreditorWasDebtSold" label_input="#rn:php:getLabel('DC_DEBT_SOLD')#" is_checkbox="false">

                    <!-- Creditor: Name of purchase / date sold -->
                    <rn:widget path="custom/utils/hideElement" control_element="namedatedebt" form_field_name="DebtCollection.CreditorWasDebtSold" form_field_value="1">                
                        <div id="namedatedebt">
                           <rn:widget path="custom/input/TextLogicInput3" name="Complaints.DebtCollection.CreditorDebtPurchaserName" label_input="#rn:php:getLabel('DC_DEBT_SOLD_TO')#">
                            <br>
                            <rn:widget path="custom/input/DateLogicInput3" name="Complaints.DebtCollection.CreditorDebtSoldDate" label_input="#rn:php:getLabel('DC_DEBT_SOLD_DATE')# " hide_hours_mins="true">
                        </div>
                        
                    </div>
                    <br>

                <? } ?>
               <!-- -------------------- :: Debt Collections Only :: -------------------- -->     
            </div>

            <rn:widget path="custom/instAgent/input/FileAttachmentUpload2" label_input="Upload a Document"/>
            <br/>
            <? if (!$this->data['js']['display_company_comment_form']) { // Eric Gottesman - 2015.01.02 - CANNOT have more than 1 submit active, even if hidden, so skip this code if showing company comment form ?>
            <rn:widget path="custom/instAgent/input/FormSubmit"
                on_success_url="/app/instAgent/list/comp_id/#rn:php:getUrlParm('comp_id')#/incstatus/#rn:php:getUrlParm('incstatus')#" label_button="Send"
                label_confirm_dialog="#rn:php:getLabel('COMPLAINT_UPDATE_SUCCESSFUL_MSG')#" error_location="rn_ErrorLocation" />
            <? } ?>

                
                
          </div><? /*END step 2*/ ?>
        </div><? /*END update_fields*/ ?>

        <? /*********** Misdirected custom fields ********************
     <rn:widget path="custom/utils/hideElement"
     control_element="redirect_case"
     form_field_name="incidents.c$company_status_1"
     form_field_value="#rn:php:$co_status['CO_STATUS_REDIRECTED']#" />
     <rn:widget path="custom/utils/hideElement"
     control_element="redirect_case"
     form_field_name="incidents.c$company_status_2"
     form_field_value="#rn:php:$co_status['CO_STATUS_REDIRECTED']#" />

     <div id="redirect_case" class="rn_Hidden">
     <rn:widget path="custom/instAgent/input/ComplaintHandlerInput" name="orgcomplainthandler.authorizedcomplainthandler"
     label_input="#rn:php:getLabel('REDIRECT_CASE_TO_LBL')#" />
     <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$cfpb_explanation_of_closure"
     label_input="#rn:php:getLabel('REDIRECT_CASE_REASON_LBL')#" />
     </div>
     ********* End misdirected custom fields ******************/
 ?>

    <?
    break;

    // show directed requests
    case $co_status['CO_STATUS_PENDING_INFO']:
    ?>
          <h5><?= getLabel('PROVIDE_INFORMATION_LBL'); ?></h5>
          <span>
            <rn:widget path="custom/instAgent/reports/invMessagesDisplay" show_last_from_investigation="true"/>
          </span>
          <br/><br/>
          <rn:widget path="custom/instAgent/input/DirectedMessageInput" name="directedReq$message.msgText" label_input=""
              error_validation_label="Response" required="true" max_chars="1250"/>
          <rn:widget path="custom/instAgent/input/FileAttachmentUpload2DirectedRequests" label_input="Upload a Document" table='directedReq$message' />
          <rn:widget path="custom/instAgent/input/FormSubmit"
                on_success_url="/app/instAgent/list/comp_id/#rn:php:getUrlParm('comp_id')#/incstatus/#rn:php:getUrlParm('incstatus')#" label_button="Send"
                label_confirm_dialog="You have updated the status of this complaint." error_location="rn_ErrorLocation" />
    <?
    break;
    }
 ?>

      <div id="rn_AdditionalInfo">
        <?// Delinquent header ?>
        <div id="div_delinquent" class="rn_Hidden">
          <h3 class="rn_HeadingBar"><?= getLabel('DELINQUENT_RESPONSE'); ?></h3>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cfpb_explanation_of_closure"
              label="#rn:php:getLabel('EXPLANATION_OF_CLOSURE')#:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cfpb_describe_relief"
              label="#rn:php:getLabel('DESCRIBE_RELIEF')#:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cfpb_dollar_amount"
              label="#rn:php:getLabel('DOLLAR_AMOUNT_LBL')#:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$cfpb_provide_a_response"
              label="#rn:php:getLabel('PROVIDE_A_RESPONSE')#:"/>
        </div>

        <?// Explanation header ?>
        <div id="div_explanation" class="rn_Hidden">
          <h3 class="rn_HeadingBar"><?= getLabel('EXPLANATION_OF_CLOSURE'); ?></h3>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_explanation_of_closure"
              label="#rn:php:getLabel('EXPLANATION_OF_CLOSURE')#:"/>
          <?// adding new Sent to regulator fields ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$agency_name"
              label="#rn:php:getLabel('AGENCY_NAME')#:"/>
              
          <? /* if debt collection, and closed with alerted CFPB, need to show that info */ ?>
          <?if($this->data['dc']['isDebtCollection'] && ($this->data['js']['bank_statuses'] == $co_status['CO_STATUS_ALERTED_CFPB'])){?>            
              <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$alerted_cfpb_reason"
              label="Alerted CFPB Reason:"/>             
          <? } ?>

        </div>

        <?// Relief header ?>
        <div id="div_relief" class="rn_Hidden">
          <h3 class="rn_HeadingBar"><?= getLabel('RELIEF'); ?></h3>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_describe_relief"
              label="#rn:php:getLabel('DESCRIBE_RELIEF')#:"/>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_dollar_amount"
              label="#rn:php:getLabel('DOLLAR_AMOUNT_LBL')#:"/>
        </div>

        <?// Company Public Comment (and Consent) ?>
        <? $dcc = $this->data['js']['display_company_comment'] ? TRUE: FALSE;
           $dccf = $this->data['js']['display_company_comment_form'] ? TRUE: FALSE;
           $rtp = $this->data['js']['return_to_page'];
        ?>
        <rn:widget path="custom/instAgent/input/CompanyPublicComment" 
                    display_consent_status="false" 
                    display_company_comment="#rn:php:$dcc#" 
                    display_company_comment_form="#rn:php:$dccf#"
                    return_to_page="#rn:php:$rtp#" />
  
        <? /* echo "<hr>" . $this->data['js']['debug'] . "<hr>"; */ ?>
            
        <?// Response header ?>
        <div id="div_response" class="rn_Hidden">
          <h3 class="rn_HeadingBar"><?= getLabel('INITIAL_RESPONSE'); ?></h3>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$comp_provide_a_response"
              label="#rn:php:getLabel('PROVIDE_A_RESPONSE')#:"/>

          <?
        // 2013.06.03 T. Woodham (CR 348): Explanation of redirect is being made to stand on it's own and not under the "Explanation of Closure" heading.
          ?>
          <rn:widget path="custom/instAgent/output/DataDisplayIA" name="incidents.c$redirect_explanation"
              label="#rn:php:getLabel('EXPLANATION_OF_REDIRECT')#:"/>
        </div>
      </div>
    </form>