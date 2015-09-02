<rn:meta title="#rn:msg:SUPPORT_HISTORY_LBL#" template="mobile.php" clickstream="incident_list" login_required="true" />

<section id="rn_PageTitle" class="rn_QuestionList">
    <div id="rn_SearchControls">
        <h1>#rn:msg:SEARCH_YOUR_SUPPORT_HISTORY_CMD#</h1>
        <form method="post" action="" onsubmit="return false">
            <rn:widget path="search/KeywordText2" label_text="" report_id="196" initial_focus="true"/>
            <rn:widget path="search/SearchButton2" report_id="196" icon_path="images/icons/search.png"/>
        </form>
    </div>
</section>
<section id="rn_PageContent" class="rn_QuestionList">
    <div class="rn_Padding">
        <rn:widget path="reports/ResultInfo2" report_id="196" add_params_to_url="p,c"/>
        <rn:widget path="reports/MobileMultiline" report_id="196"/>
        <rn:widget path="reports/Paginator" report_id="196"/>
    </div>
</section>
