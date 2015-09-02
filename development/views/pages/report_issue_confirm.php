<rn:meta title="#rn:msg:ASK_QUESTION_HDG#" template="cfpb.php" clickstream="incident_confirm"/>

<div id="rn_PageTitle" class="rn_AskQuestion">
    <h1>#rn:msg:QUESTION_SUBMITTED_HDG#</h1>
</div>

<div id="rn_PageContent" class="rn_AskQuestion">
    <div class="rn_Padding">
        <p>
            <?=getLabel('REPORT_ISSUE_CONFIRM_MSG');?>
            <b>
                <rn:condition url_parameter_check="i_id == null">
                    #rn:url_param_value:refno#
                <rn:condition_else/>
                    <rn:field name="incidents.ref_no" />
                </rn:condition>
            </b>
        </p>
        <p>
            <?=getLabel('THANK_YOU_CONFIRM_MSG');?>
        </p>
    </div>
</div>

