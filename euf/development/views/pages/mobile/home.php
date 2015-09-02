<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="mobile.php" clickstream="home"/>

<section id="rn_PageContent" class="rn_Home">
    <div class="rn_Module">
        <rn:widget path="navigation/Accordion" toggle="rn_AccordTrigger"/>
        <h2 id="rn_AccordTrigger" class="rn_Expanded">#rn:msg:MOST_POPULAR_ANSWERS_LBL#<span class="rn_Expand"></span></h2>
        <div class="rn_Report">
            <rn:widget path="reports/MobileMultiline" report_id="194" per_page="6"/>
            <a class="rn_AnswersLink" href="/app/answers/list#rn:session#">#rn:msg:SEE_ALL_POPULAR_ANSWERS_UC_LBL#</a>
        </div>
    </div>
    <div class="rn_Module">
        <rn:widget path="navigation/Accordion" toggle="rn_ListTrigger"/>
        <h2 id="rn_ListTrigger" class="rn_Collapsed">#rn:msg:FEATURED_SUPPORT_CATEGORIES_LBL#<span class="rn_Expand"></span></h2>
        <div class="rn_Hidden">
            <rn:widget path="search/MobileProductCategoryList" data_type="categories" levels="1" label_title=""/>
        </div>
    </div>
    <div class="rn_Module">
        <rn:widget path="navigation/Accordion" toggle="rn_ContactTrigger"/>
        <h2 id="rn_ContactTrigger" class="rn_Expanded">#rn:msg:CONTACT_US_LBL#<span class="rn_Expand"></span></h2>
        <ul class="rn_ContactChannels">
            <rn:condition config_check="COMMON:MOD_CHAT_ENABLED == true">
                <li>
                    <a class="rn_ChatChannel" href="/app/chat/chat_launch">#rn:msg:CHAT_LBL#</a>
                </li>
            </rn:condition>
            <li>
                <a class="rn_AskChannel" href="/app/ask">#rn:msg:EMAIL_US_LBL#</a>
            </li>
            <li>
                <a class="rn_VoiceChannel" href="javascript:void(0);">#rn:msg:CALL_US_DIRECTLY_LBL#</a>
            </li>
            <rn:condition config_check="RNW:COMMUNITY_ENABLED == true">
                <li>
                    <a class="rn_CommunityChannel" href="javascript:void(0);">#rn:msg:ASK_THE_COMMUNITY_LBL#</a>
                </li>
            </rn:condition>
        </ul>
    </div>
</section>
