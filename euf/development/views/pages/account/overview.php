<? header('Location: /app/error404'); ?>

<rn:meta title="#rn:msg:ACCOUNT_OVERVIEW_LBL#" template="standard.php" login_required="true" />

<div id="rn_PageTitle" class="rn_Account">
    <h1>#rn:msg:ACCOUNT_OVERVIEW_LBL#</h1>
</div>
<div id="rn_PageContent">
    <div class="rn_Overview">
            <h2><a class="rn_Questions" href="/app/account/questions/list#rn:session#">#rn:msg:QUESTIONS_HDG#</a></h2>
            <div class="rn_Questions">
                <rn:widget path="reports/Grid2" report_id="196" per_page="4" label_caption="#rn:msg:YOUR_RECENTLY_SUBMITTED_QUESTIONS_LBL#" />
                <a href="/app/account/questions/list#rn:session#">#rn:msg:SEE_ALL_QUESTIONS_LBL#</a>
            </div>
            <h2><a class="rn_Profile" href="/app/account/profile#rn:session#">#rn:msg:SETTINGS_LBL#</a></h2>
            <div class="rn_Profile">
                <a href="/app/account/profile#rn:session#">#rn:msg:UPDATE_YOUR_ACCOUNT_SETTINGS_CMD#</a><br/>
                <rn:condition external_login_used="false">
                    <rn:condition config_check="RNW_UI:EU_CUST_PASSWD_ENABLED == true">
                        <a href="/app/account/change_password#rn:session#">#rn:msg:CHANGE_YOUR_PASSWORD_CMD#</a>
                    </rn:condition>
                </rn:condition>
            </div>
            <h2><a class="rn_Notifs" href="/app/account/notif/list#rn:session#">#rn:msg:NOTIFICATIONS_HDG#</a></h2>
            <div class="rn_Notifs">
                <rn:widget path="reports/Grid2" report_id="200" per_page="4" label_caption="#rn:msg:YOUR_RECENT_ANSWER_NOTIFICATIONS_LBL#"/>
                <a href="/app/account/notif/list#rn:session#">#rn:msg:PRODUCT_CATEGORY_ANS_NOTIFICATIONS_LBL#</a>
            </div>
            <h2><strong class="rn_Contracts">#rn:msg:SERVICE_CONTRACTS_LBL#</strong></h2>
            <div class="rn_Contracts">
                <rn:widget path="reports/Grid2" report_id="185" label_caption="#rn:msg:YOUR_SERVICE_CONTRACTS_LBL#"/>
            </div>
    </div>
</div>
