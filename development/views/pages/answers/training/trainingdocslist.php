<rn:meta title="Frequently Asked Questions" template="cfpb.php" clickstream="answer_list" use_profile_defaults="true" login_required="true"/>

<style>
    .answers_subtab{
        margin-bottom: 15px;
        height: 42px;
        border-bottom: 1px solid #BBB;

    }
    
    .answers_subtab div {
        float: left;
        display: block;
        
        margin-right: 12px;
        padding: 12px;
        border-radius: 7px 7px 0px 0px;
        
        -webkit-appearance: none;
        -webkit-box-shadow: #6d9347 0px 0px 0px 1px inset,
                            rgba(167,255,143,0.597656) 0px 1px 0px 1px inset,
                            #fff 0px 28px 20px -18px inset,
                            #6ae23c 0px 0px 0px 2px inset,rgba(255,255,255,0.597656) 0px 1px 0px 0px;
        -webkit-box-sizing: border-box;
        -webkit-font-smoothing: antialiased;
        background-attachment: scroll;
        background-clip: border-box;
        background-color: #6ac344;
        background-image: none;
        background-origin: padding-box;
     }
     
     .answers_subtab div.selectedtab {
        height: 43px;
        background: #fff;
        -webkit-box-shadow:none;
        box-shadow: none;
        border-top: 1px solid #bbb;
        border-right: 1px solid #bbb;
        border-left: 1px solid #bbb;
     }
     
     .answers_subtab  a{
         text-decoration:none !important;
         color:black !important;
     }
</style>

<rn:widget path="knowledgebase/RssIcon2" icon_path="" />
<div id="rn_PageTitle" class="rn_AnswerList">
    <h6><?=getLabel('GET_ANSWERS_LBL');?></h6>
</div>

    <rn:condition is_spider="false">
        <div id="rn_SearchControls">
            <h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>
            <form method="post" action="" onsubmit="return false" >
                <div class="rn_SearchInput">
                    <?/*<rn_:widget path="search/AdvancedSearchDialog" report_id="#rn:php:getSetting('TRAINING_DOCS_REPORT_ID')#" show_confirm_button_in_dialog="true"/>*/?>
                    <rn:widget path="search/KeywordText2" label_text="#rn:msg:FIND_THE_ANSWER_TO_YOUR_QUESTION_CMD#" 
                      initial_focus="true" report_id="#rn:php:getSetting('TRAINING_DOCS_REPORT_ID')#"/>
                </div>
                <rn:widget path="search/SearchButton2" report_id="#rn:php:getSetting('TRAINING_DOCS_REPORT_ID')#"/>
            </form>
            <rn:widget path="search/DisplaySearchFilters" report_id="#rn:php:getSetting('TRAINING_DOCS_REPORT_ID')#"/>
        </div>
    </rn:condition>
 
<div id="rn_PageContent" class="rn_AnswerList">
    <div class="answers_subtab">
        <div class ="tab">
            <a href = "/app/answers/training/traininglist">Videos</a>
        </div>
        <div class ="selectedtab">
            <a href = "javascript:void(0);">Documents</a>
        </div>
    </div>
    <div class="rn_Padding">
        <h2 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_RESULTS_CMD#</h2>
        <rn:widget path="reports/ResultInfo2" report_id="#rn:php:getSetting('TRAINING_DOCS_REPORT_ID')#" add_params_to_url="p,c"/>
        <rn:widget path="knowledgebase/TopicWords2"/>
        <rn:widget path="reports/Multiline2" report_id="#rn:php:getSetting('TRAINING_DOCS_REPORT_ID')#" per_page="10" truncate_size="300"/>
        <rn:widget path="reports/Paginator" report_id="#rn:php:getSetting('TRAINING_DOCS_REPORT_ID')#" maximum_page_links="27"/>
    </div>
</div>
