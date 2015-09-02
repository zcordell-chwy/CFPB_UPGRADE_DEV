<rn:meta title="Report a Technical Issue" template="cfpb.php" clickstream="incident_create" login_required="true"/>

<div id="rn_PageTitle" class="rn_AskQuestion">
   <h6><?=getLabel('HELP_REPORT');?></h6>
</div>
<div id="rn_PageContent" class="rn_AskQuestion">
    <div class="rn_Padding">
        <form id="rn_QuestionSubmit2" method="post" action="" onsubmit="return false;">
          <div id="rn_ErrorLocation"></div>

          <rn:condition logged_in="false">
            <rn:widget path="input/FormInput" name="contacts.email" required="true" initial_focus="true"/>
          </rn:condition>
          
            <?/*<rn_:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$cc_co_name" required="true" label_input="#rn:php:getLabel('INSTITUTION_NAME_LBL')#"/>*/?>
            <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$operating_system" required="true" label_input="#rn:php:getLabel('OS_VERSION_LBL')#"/>
            <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.c$browser_name" required="true" label_input="#rn:php:getLabel('BROWSER_VERSION_LBL')#"/>
            <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.thread" required="true" label_input="#rn:php:getLabel('DESCRIBE_ISSUE')#" max_chars="4000"/>
            <div id="ps_DescribeIssue">
                <?=getLabel('DESCRIBE_ISSUE_SPECIFIC');?>
            </div>
            <br/>
            <rn:widget path="custom/instAgent/input/FileAttachmentUpload2" label_input="#rn:php:getLabel('ATTACH_SCREENSHOT')#"/>
          
          <div class="rn_Hidden">
            <rn:widget path="input/ProductCategoryInput" table="incidents" data_type="categories" default_value="#rn:php:getSetting('TECH_ISSUE_CAT_ID')#"/>
          </div>

            <rn:widget path="input/FormSubmit" label_button="#rn:php:getLabel('SUBMIT_CMD')#" on_success_url="/app/ask_confirm" error_location="rn_ErrorLocation" />

        </form>
        
        <rn:condition answers_viewed="2" searches_done="1">
        <rn:condition_else/>
            <rn:widget path="input/SmartAssistantDialog"/>
        </rn:condition>
    </div>
</div>
