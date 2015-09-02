<rn:meta controller_path="custom/congressional/search/SearchCFPB"
         js_path="custom/congressional/search/SearchCFPB"
         compatibility_set="November '09+"/>

    <div id="rn_SearchControls" style="width:1146px;">
        <h1 class="rn_ScreenReaderOnly">#rn:msg:SEARCH_CMD#</h1>
        <form method="post" action="" onsubmit="return false" >
            <div class="ps_SearchInputLine">
            <? if($this->data['attrs']['search_prod']):?>
                <div class="rn_AdvancedFilter rn_AdvancedSubWidget">
                  <rn:widget path="custom/instAgent/search/ProductCategorySearchRoleFilter" filter_type="products"
                    label_input="" label_nothing_selected="#rn:php:getLabel('FILTER_BY_PRODUCT_LBL')#"
                    remove_menu_items="#rn:php:$this->data['attrs']['hide_prod']#
                        #rn:php:getSetting('COMPANY_PORTAL_PROD_ID')#" />
                </div>
            <? endif;?>
            <? if($this->data['attrs']['search_cat']):?>
                <div class="rn_AdvancedFilter rn_AdvancedSubWidget" style="margin-left:20px">
                  <rn:widget path="custom/instAgent/search/ProductCategorySearchRoleFilter" filter_type="categories"
                    label_input="" label_nothing_selected="#rn:php:getLabel('FILTER_BY_ISSUE_LBL')#"
                    remove_menu_items="#rn:php:$this->data['attrs']['hide_cat']#
                        #rn:php:getSetting('TECH_ISSUE_CAT_ID')#,
                        #rn:php:getSetting('ASK_CAT_ID')#,
                        #rn:php:getSetting('NEWS_CAT_ID')#,
                        #rn:php:getSetting('FAQ_CAT_ID')#,
                        #rn:php:getSetting('QUICK_LINKS_CAT_ID')#" />
                </div>
            <? endif;?>
                <div class="rn_AdvancedFilter rn_AdvancedSubWidget" style="margin-left:20px">
                    <rn:widget path="custom/congressional/search/FilterDropdown2" report_id="#rn:php:$this->data['attrs']['report_id']#"
                        filter_name="#rn:php:getSetting( 'CONGRESSIONAL_PORTAL_CONSUMER_STATUS_FILTER_NAME' )#" />
                </div>

                <div style="position:relative; margin-left:10px; bottom:5px">
                  <rn:widget path="custom/instAgent/search/SearchButton2"/>
                </div>
            </div>
        </form>
        <rn:widget path="custom/instAgent/search/DisplaySearchFilters"/>
    </div>
