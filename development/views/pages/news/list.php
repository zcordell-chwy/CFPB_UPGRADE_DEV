<rn:meta title="News" template="cfpb.php" clickstream="answer_list" use_profile_defaults="true" login_required="true" />

<rn:widget path="knowledgebase/RssIcon2" icon_path="" />
<div id="rn_PageTitle" class="rn_AnswerList">
    <h6><?=getLabel('LATEST_NEWS_HDG');?></h6>
</div>
<div id="rn_PageContent" class="rn_AnswerList">
    <div class="rn_Padding">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="reports/ResultInfo2" report_id="#rn:php:getSetting('LATEST_NEWS_REPORT_ID')#" add_params_to_url="p,c"/>
        <rn:widget path="knowledgebase/TopicWords2"/>
        <rn:widget path="reports/Multiline2" report_id="#rn:php:getSetting('LATEST_NEWS_REPORT_ID')#" truncate_size="300"/>
        <rn:widget path="reports/Paginator" report_id="#rn:php:getSetting('LATEST_NEWS_REPORT_ID')#"/>
    </div>
    <rn:widget path="custom/notifications/SubscribeLink" hier_map="#rn:php:getSetting('NEWS_CAT_ID')#">    
</div>
