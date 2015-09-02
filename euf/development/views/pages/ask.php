<rn:meta title="Submit a Ticket" template="cfpb.php" clickstream="incident_create" login_required="true"/>

<div id="rn_PageTitle" class="rn_AskQuestion">
   <h6><?=getMessage(ASK_QUESTION_HDG);?></h6>
</div>  
<div id="rn_PageContent" class="rn_AskQuestion">
    <div class="rn_Padding">
        <form id="rn_QuestionSubmit2" method="post" action="" onsubmit="return false;">
          <div id="rn_ErrorLocation"></div>
          
          <rn:condition logged_in="false">
            <rn:widget path="input/FormInput" name="contacts.email" required="true" initial_focus="true"/>
          </rn:condition>

           <rn:widget path="custom/input/ProductCategoryInput2" table="incidents" label_input="#rn:php:getMessage(CATEGORY_LBL)#" label_nothing_selected="#rn:php:getMessage(SELECT_A_CATEGORY_LBL)#" label_set_button="#rn:php:getMessage(CATEGORIES_LBL)#" data_type="categories" required_lvl="1" restrict_to_ids="#rn:php:getSetting('SUBMIT_A_TICKET_CAT_IDS')#" show_description_in="ps_SelectedCatDescription"/>

           <span class='note'><div id="ps_SelectedCatDescription"></div><br /></span>


           <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.thread" required="true" label_input="#rn:php:getLabel('DESCRIBE_ISSUE')#" max_chars="4000"/>
           <div class="rn_Hidden">
            <div id="ps_DescribeIssue">
                <?=getLabel('DESCRIBE_ISSUE_SPECIFIC');?>
            </div>
           </div>

            <div><br /><?=getLabel('POPULATED_OS_AND_BROWSER');?><br /><br /></div>

            <div><?=getLabel('OS_VERSION_LBL');?></div>
            <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$operating_system" required="true" label_input="#rn:php:getLabel('OS_VERSION_LBL')#" default_value="#rn:php:getOS()#"/>
            <div><?=getLabel('BROWSER_VERSION_LBL');?></div>
            <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$browser_name" required="true" label_input="#rn:php:getLabel('BROWSER_VERSION_LBL')#" default_value="#rn:php:getBrowser()#"/>

          <rn:widget path="/custom/instAgent/input/FileAttachmentUpload2"/>

          
          <rn:widget path="input/FormSubmit" label_button="#rn:php:getLabel('SUBMIT_CMD')#" on_success_url="/app/ask_confirm" error_location="rn_ErrorLocation" />

        </form>
        
        <rn:condition answers_viewed="2" searches_done="1">
        <rn:condition_else/>
            <rn:widget path="input/SmartAssistantDialog"/>
        </rn:condition>
    </div>
</div>
