<rn:meta controller_path="custom/instAgent/search/SearchCFPB"
         js_path="custom/instAgent/search/SearchCFPB"
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
                <div class="rn_AdvancedFilter rn_AdvancedSubWidget">
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
            <? if( $this->data['userType'] != 'company' && $this->data['attrs']['search_state'] === true ): ?>
                <div class="rn_AdvancedFilter rn_AdvancedSubWidget">
                    <rn:widget path="custom/instAgent/search/StateDropdown" report_id="#rn:php:$this->data['attrs']['report_id']#"
                        filter_name="#rn:php:getSetting( 'GOVERNMENT_PORTAL_STATE_FILTER_NAME' )#"
                        alt_menu_filter_name="#rn:php:getSetting( 'GOVERNMENT_PORTAL_MENU_FILTER_NAME' )#" />
                </div>
            <? endif; ?>
            <? if( $this->data['userType'] != 'company' && $this->data['attrs']['search_date_range'] === true ): ?>
                <? if( $this->data['userType'] == 'state' ): ?><div class="rn_Hidden"><? endif; ?>
                <div class="rn_AdvancedFilter rn_AdvancedSubWidget">
                    <rn:widget path="custom/instAgent/search/DateRangeMenu" report_id="#rn:php:$this->data['attrs']['report_id']#"
                        filter_name="#rn:php:getSetting( 'GOVERNMENT_PORTAL_DATE_RANGE_FILTER' )#" />
                </div>
                <? if( $this->data['userType'] == 'state' ): ?></div><? endif; ?>
            <? endif; ?>
                <div style="position:relative; left:20px;">
                  <rn:widget path="custom/instAgent/search/SearchTypeList2" label_text="Search by"
                    hide_options="#rn:php:getSetting('CASE_DATA_EXPORT_STATE_FILTER_NAME')#" />
                </div>
                <div style="position:relative; left:30px; top:-6px;">
                  <rn:widget path="custom/instAgent/search/KeywordText2" label_text="" initial_focus="true"/>
                </div>
                <div style="position:relative; left:30px; bottom:5px">
                  <rn:widget path="custom/instAgent/search/SearchButton2"/>
                </div>
            </div>
        </form>
        <rn:widget path="custom/instAgent/search/DisplaySearchFilters"/>
    </div>

