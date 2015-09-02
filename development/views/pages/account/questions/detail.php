<?/* Remove PII from title. CR339 */?>
<rn:meta title="Update this Question" template="cfpb.php" login_required="true" clickstream="incident_view"/>
<div id="rn_PageTitle" class="rn_Account">
    <h6><rn:field name="incidents.subject" highlight="true"/></h6>
</div>
<div id="rn_PageContent" class="rn_QuestionDetail">
    <div class="rn_Padding">
        
            <rn:condition incident_reopen_deadline_hours="0">
                <?php echo $foo = (IncidentStatusId() != 160) ? "<div class=rn_Hidden>" : "";  ?>
                <h2 class="rn_HeadingBar">#rn:msg:UPDATE_THIS_QUESTION_CMD#</h2>
                <div id="rn_ErrorLocation"></div>
                <form id="rn_UpdateQuestion" method="post" action="" onsubmit="return false;">
                        <rn:widget path="custom/instAgent/input/TextLogicInput" name="incidents.thread" 
                            label_input="#rn:msg:ADD_ADDTL_INFORMATION_QUESTION_CMD#" initial_focus="true" required="true" max_chars="4000"/>
                        <rn:widget path="instAgent/input/FileAttachmentUpload2" label_input="#rn:msg:ATTACH_ADDTL_DOCUMENTS_QUESTION_LBL#"/>
                        <div class="rn_Hidden">
                            <rn:widget path="input/FormInput" name="incidents.status" label_input="#rn:msg:DO_YOU_WANT_A_RESPONSE_MSG#" default_value="0" />
                        </div>
                        <rn:widget path="input/FormSubmit" on_success_url="/app/account/questions/list" error_location="rn_ErrorLocation"/>
                </form>
                <?php echo "</div>"?>
            <rn:condition_else/>
                <h2 class="rn_HeadingBar">#rn:msg:INC_REOPENED_UPD_FURTHER_ASST_PLS_MSG#</h2>
            </rn:condition>
            <br/>
          

        <h2 class="rn_HeadingBar"><?=getLabel('MESSAGE_HISTORY_LBL');?></h2>
        <div id="rn_QuestionThread">
            <rn:widget path="output/DataDisplay" name="incidents.thread" label=""/>
        </div>
<p>
                        <div id="rn_FileAttach">
                            <rn:widget path="output/DataDisplay" name="incidents.fattach" label_input="#rn:msg:FILE_ATTACHMENTS_LBL#"/>
                        </div>
</p>

        <h2 class="rn_HeadingBar">#rn:msg:ADDITIONAL_DETAILS_LBL#</h2>
        <div id="rn_AdditionalInfo">
            <rn:widget path="output/DataDisplay" name="incidents.contact_email" label="#rn:msg:EMAIL_ADDR_LBL#" />
            <rn:widget path="output/DataDisplay" name="incidents.ref_no" />
            <rn:widget path="output/DataDisplay" name="incidents.subject" />
            <rn:widget path="output/DataDisplay" name="incidents.prod"  />
            <rn:widget path="output/DataDisplay" name="incidents.cat" />
            <rn:widget path="output/DataDisplay" name="incidents.c$cc_co_name" />
            <rn:widget path="output/DataDisplay" name="incidents.c$operating_system" />
            <rn:widget path="output/DataDisplay" name="incidents.c$browser_name" />
            <rn:widget path="custom/instAgent/output/StatusDisplay" name="incidents.status" />
            <rn:widget path="output/DataDisplay" name="incidents.created" label="#rn:msg:CREATED_LBL#" />
            <rn:widget path="output/DataDisplay" name="incidents.updated" />
        </div>

        <div id="rn_DetailTools">
            <rn:widget path="utils/PrintPageLink" />
        </div>

    </div>
</div>
