<? header('Location: /app/error404'); ?>

<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="cfpb.php" clickstream="home"/>
<div id="rn_PageTitle" class="rn_Home">
    <rn:condition is_spider="false">
        <div id="rn_SearchControls">
            <h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>
            <form method="post" action="" onsubmit="return false" >
                <div class="rn_SearchInput">
                    <rn:widget path="search/AdvancedSearchDialog" report_page_url="/app/answers/list" report_id="176" show_confirm_button_in_dialog="true"/>
                    <rn:widget path="search/KeywordText2" label_text="#rn:msg:FIND_THE_ANSWER_TO_YOUR_QUESTION_CMD#" initial_focus="true" report_id="176"/>
                </div>
                <rn:widget path="search/SearchButton2" report_page_url="/app/answers/list" report_id="176"/>
            </form>
        </div>
    </rn:condition>
</div>
<div id="rn_PageContent" class="rn_Home">
    <?/*<rn_:widget path="search/ProductCategoryList" data_type="categories" label_title="#rn:msg:FEATURED_SUPPORT_CATEGORIES_LBL#"/>
    */?>
    <br/><br/>
    <div class="rn_Module">
        <h2>#rn:msg:MOST_POPULAR_ANSWERS_LBL#</h2>
        <rn:widget path="reports/Multiline2" report_id="194" per_page="10"/>
        <a class="rn_AnswersLink" href="/app/answers/list#rn:session#">#rn:msg:SEE_ALL_POPULAR_ANSWERS_UC_LBL#</a>
    </div>
</div>
test
