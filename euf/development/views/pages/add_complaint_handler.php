<rn:meta title="Add/Remove related company" template="cfpb.php" clickstream="incident_create"/>

<div id="rn_PageTitle" class="rn_AskQuestion">
   <h6><?=getLabel('ORG_COMPLAINT_HANDLER_SUBJECT');?></h6>
</div>
<div id="rn_PageContent" class="rn_AskQuestion">
    <div class="rn_Padding">
        <p>"Redirected to related company" is an administrative response category you would use when you cannot take action because the complaint needs alternative routing to another company with whom your company has a contractual relationship.</p>
        <p>This response can only be used if your company has provided justification for the need to redirect cases and a list of the appropriate companies to Stakeholder Management for approval.</p>
        <p>The response you provide to support this selection includes the company where the case should be routed, a detailed explanation of why, and a copy of the note proving the legal agreement as an attachment. The system will reroute the complaint to the designated company's portal, removing it from your company's portal.</p>
        <p>If the company that receives the complaint feels it was routed in error and redirects the complaint, a Consumer Response Specialist will review the complaint and either route the case to the new company indicated or route the complaint back to your company for resolution. However, neither the response nor the category selection is forwarded to the consumer or displayed in the consumer portal.</p>

        <form id="rn_QuestionSubmit2" method="post" action="" onsubmit="return false;">
          <div id="rn_ErrorLocation"></div>
          
          <rn:condition logged_in="false">
            <rn:widget path="input/FormInput" name="contacts.email" required="true" initial_focus="true"/>
          </rn:condition>

          <? /*
          <div class="rn_Hidden">
            <rn:widget path="input/FormInput" name="incidents.subject" default_value="#rn:php:getLabel('ORG_COMPLAINT_HANDLER_SUBJECT')#" />
          </div>
          */ ?>

          <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.thread" required="true" label_input="#rn:php:getLabel('ORG_COMPLAINT_HANDLER_QUESTION_LBL')#" max_chars="4000"/>
          
          <div class="rn_Hidden">
            <rn:widget path="input/ProductCategoryInput" table="incidents" data_type="categories"  default_value="#rn:php:getSetting('RELATED_COMPANY_CAT_ID')#"/>
          </div>

          <br/>
          <rn:widget path="custom/instAgent/input/FileAttachmentUpload2" label_input="#rn:php:getLabel('ATTACH_OFFICIAL_NOTE')#"/>

          
          <rn:widget path="input/FormSubmit" label_button="#rn:php:getLabel('SUBMIT_CMD')#" on_success_url="/app/ask_confirm" error_location="rn_ErrorLocation" />

        </form>
        
        <?
        /**
        <rn:condition answers_viewed="2" searches_done="1">
        <rn:condition_else/>
            <rn:widget path="input/SmartAssistantDialog"/>
        </rn:condition>
        */
        ?>
    </div>
</div>
