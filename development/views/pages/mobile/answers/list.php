<rn:meta title="#rn:msg:FIND_ANS_HDG#" template="mobile.php" clickstream="answer_list"/>
<section id="rn_PageTitle" class="rn_AnswerList">
    <rn:condition is_spider="false">
        <div id="rn_SearchControls">
            <h1>#rn:msg:SEARCH_RESULTS_CMD#</h1>
            <form method="post" action="" onsubmit="return false;">
                <rn:widget path="search/KeywordText2" label_text="" report_id="176"/>
                <rn:widget path="search/SearchButton2" report_id="176"  icon_path="images/icons/search.png"/>
            </form>
            <rn:widget path="navigation/Accordion" toggle="rn_Advanced"/>
            <div class="rn_Padding">
                <a class="rn_AlignRight" href="javascript:void(0);" id="rn_Advanced">#rn:msg:PLUS_SEARCH_OPTIONS_LBL#</a>
                <div>
                    <rn:widget path="search/MobileProductCategorySearchFilter" filter_type="products" report_id="176"/>
                    <rn:widget path="search/MobileProductCategorySearchFilter" filter_type="categories" label_filter_type="#rn:msg:CATEGORIES_LBL#" 
                        label_prompt="#rn:msg:SELECT_A_CATEGORY_LBL#" label_input="#rn:msg:LIMIT_BY_CATEGORY_LBL# »" report_id="176"/>
                </div>
            </div>
        </div>
    </rn:condition>
</section>
<section id="rn_PageContent" class="rn_AnswerList">
    <div class="rn_Padding">
        <rn:widget path="reports/ResultInfo2" report_id="176" add_params_to_url="p,c"/>
        <rn:widget path="reports/MobileMultiline" report_id="176"/>
        <rn:widget path="reports/Paginator" report_id="176"/>
    </div>
</section>
