<rn:meta title="#rn:msg:ASK_QUESTION_HDG#" template="mobile.php" clickstream="incident_confirm"/>

<section id="rn_PageTitle" class="rn_AskConfirm">
    <h1>#rn:msg:QUESTION_SUBMITTED_HDG#</h1>
</section>
<section id="rn_PageContent" class="rn_AskConfirm">
    <div class="rn_Padding">
        <p>
            #rn:msg:SUBMITTING_QUEST_REFERENCE_FOLLOW_LBL# <em>#<rn:field name="incidents.ref_no" />#rn:url_param_value:refno#</em>
        </p>
        <p>
            #rn:msg:SUPPORT_TEAM_SOON_MSG#
        </p>
        <rn:condition logged_in="true">
        <p>
            #rn:msg:UPD_QUEST_VISIT_SUPPORT_HIST_UPD_MSG#
            <br/><br/>
            <a href="/app/account/questions/list#rn:session#">#rn:msg:YOUR_SUPPORT_HISTORY_LBL#</a>
        </p>
        <rn:condition_else/>
        <p>
            #rn:msg:UPD_QUEST_ACCT_LG_SEL_QUEST_UPD_MSG#
            <br/><br/>
            <a href="/app/utils/account_assistance#rn:session#">#rn:msg:ACCOUNT_ASSISTANCE_LBL#</a>
        </p>
        </rn:condition>
    </div>
</section>