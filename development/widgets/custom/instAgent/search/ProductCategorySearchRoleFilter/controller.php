<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class ProductCategorySearchRoleFilter extends Widget
{
    function __construct()
    {
        parent::__construct();

        $this->attrs['label_input'] = new Attribute(getMessage(INPUT_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_INPUT_CONTROL_LBL), getMessage(LIMIT_BY_PRODUCT_LBL));
        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['filter_type'] = new Attribute(getMessage(FILTER_TYPE_LBL), 'OPTION', getMessage(FILTER_DISP_DROPDOWN_INFORMATION_LBL), 'products');
        $this->attrs['filter_type']->options = array('products', 'categories');
        $this->attrs['linking_off'] = new Attribute(getMessage(PROD_SLASH_CAT_LINKING_OFF_LBL), 'BOOL', getMessage(SET_TRUE_PROD_CAT_LINKING_DISABLED_MSG), false);
        $this->attrs['label_nothing_selected'] = new Attribute(getMessage(NOTHING_SELECTED_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_VALUE_SELECTED_LBL), getMessage(SELECT_A_PRODUCT_LBL));
        $this->attrs['label_confirm_button'] = new Attribute(getMessage(CONFIRM_BUTTON_LABEL_MSG), 'STRING', sprintf(getMessage(LABEL_CONFIRMATION_BTN_PCT_S_ATTRIB_MSG), 'show_confirm_button_in_dialog'), getMessage(OK_LBL));
        $this->attrs['label_cancel_button'] = new Attribute(getMessage(CANCEL_BUTTON_LABEL_CMD), 'STRING', sprintf(getMessage(LABEL_CANCEL_BTN_PCT_S_ATTRIB_MSG), 'show_confirm_button_in_dialog'), getMessage(CANCEL_CMD));
        $this->attrs['label_nothing_selected'] = new Attribute(getMessage(NOTHING_SELECTED_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_VALUE_SELECTED_LBL), getMessage(SELECT_A_PRODUCT_LBL));
        $this->attrs['label_accessible_interface'] = new Attribute(getMessage(ACCESSIBLE_INTERFACE_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAYED_SCREEN_EFFECTIVELY_MSG), getMessage(BTN_SCREEN_READERS_PLS_PREV_LINK_MSG));
        $this->attrs['label_screen_reader_selected'] = new Attribute(getMessage(VALUES_SELECTED_LABEL_LBL), 'STRING', getMessage(LABEL_DISP_SCREEN_READERS_LBL), getMessage(VALUES_SELECTED_LBL));
        $this->attrs['label_screen_reader_accessible_option'] = new Attribute(getMessage(ACCESSIBLE_OPTION_LABEL_LBL), 'STRING', getMessage(TXT_LINK_DISPLAYED_SCREEN_READERS_MSG), getMessage(SCREEN_READER_USERS_PRESS_ENTER_PCT_MSG));
        $this->attrs['report_page_url'] = new Attribute(getMessage(REPORT_PAGE_LBL), 'STRING', getMessage(PG_DISP_ITEM_SEL_SRCH_SEL_SET_TRUE_MSG), '');
        $this->attrs['search_on_select'] = new Attribute(getMessage(SEARCH_ON_SELECTED_CMD), 'BOOL', getMessage(START_SEARCH_SOON_ITEM_IS_SELECTED_MSG), false);
        $this->attrs['treeview_css'] = new Attribute(getMessage(TREEVIEW_CSS_LBL), 'STRING', sprintf(getMessage(FILE_CONT_TREEVIEW_CSS_DISP_EXP_MSG), "/rnt/rnw/yui_2.7/treeview/assets/treeview-skin.css"), getYUICodePath('treeview/assets/treeview-menu.css'));
        $this->attrs['show_confirm_button_in_dialog'] = new Attribute(getMessage(SHOW_CONFIRM_BUTTONS_IN_DIALOG_MSG), 'BOOL', getMessage(ENABLED_TREE_POPUP_CONT_CANCEL_MSG), false);
        $this->attrs['remove_menu_items'] = new Attribute("Remove specific menu items", 'STRING', "Comma seperated list of menu labels to remove for menu custom fields", null);
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  getMessage(WIDGET_DISP_DROPDOWN_MENU_MSG);
        $this->parms['p'] = new UrlParam(getMessage(PRODUCT_LBL), 'p', false, getMessage(CMMA_SPARATED_IDS_COMMAS_DENOTING_MSG), 'p/1,2,3');
        $this->parms['c'] = new UrlParam(getMessage(CATEGORY_LBL), 'c', false, getMessage(COMMA_SEPARATED_IDS_COMMAS_DENOTING_MSG), 'c/1');
    }

    function getData()
    {
        $this->CI->load->model('custom/Report_model2');
        $this->CI->load->model('standard/Prodcat_model');
        setFiltersFromUrl($this->data['attrs']['report_id'], $filters);

        $filterType = ($this->data['attrs']['filter_type'] === 'products') ? 'p' : 'c';

        $defaultValue = $filters[$filterType]->filters->data[0];
        if($defaultValue)
            $defaultValue = explode(',', $defaultValue);
        else
            $defaultValue = array();

        $optlistID = $filters[$filterType]->filters->optlist_id;
        if(!$optlistID)
        {
            echo $this->reportError(sprintf(getMessage(FILTER_PCT_S_EXIST_REPORT_PCT_D_LBL), $this->data['attrs']['filter_type'], $this->data['attrs']['report_id']));
            return false;
        }

        $trimmedTreeViewCss = trim($this->data['attrs']['treeview_css']);
        if ($trimmedTreeViewCss !== '')
            $this->addStylesheet($trimmedTreeViewCss);

        $this->data['js'] = array('oper_id' => $filters[$filterType]->filters->oper_id,
                                            'fltr_id' => $filters[$filterType]->filters->fltr_id,
                                            'linkingOn' => $this->data['attrs']['linking_off'] ? 0 : $this->CI->Prodcat_model->getLinkingMode(),
                                            'report_def' => $filters[$filterType]->report_default,
                                            'searchName' => $filterType,
                                            'defaultData' => (count($defaultValue)) ? true : false,
                                            'hierData' => array());

        //if linking is on we need to get all values for prods as well as cats
        if($filterType === 'c' && $this->data['js']['linkingOn'])
            $selectedProds = ($filters['p']) ? explode(',', $filters['p']->filters->data[0]) : null;

        if($selectedProds[0]){
            $returnValue = $this->_setProdLinkingDefaults($selectedProds, $defaultValue);
            $this->data['js']['defaultData'] = true;
        }
        else{
            $returnValue = $this->_setDefaults($defaultValue);
        }
        if(!$returnValue)
            return false;
        
        $this->data['js']['initial'] = $defaultValue;
        $this->data['js']['hm_type'] = $this->data['attrs']['filter_type'] === 'products' ? HM_PRODUCTS : HM_CATEGORIES;

        // even if no default product is selected, get a fresh set of categories
        // so that an initial category selection doesn't result in weird behavior
        // when the tree is reset
        if($this->data['js']['linkingOn'] && $this->data['attrs']['filter_type'] === 'categories')
            $this->_setNoneDefaults();
        
        //echo"<pre>";print_r($this->data['attrs']['remove_menu_items']);echo"</pre>";
        // add functionality to remove specific menu items (hier data) from prod/cat list
        if ($this->data['js']['hierData'][0][1]['value'] && $this->data['attrs']['remove_menu_items'])
        {
            //echo"<pre>";print_r($this->data['js']['hierData'][0]);echo"</pre>";
            $this->data['js']['hierData'][0] = $this->_removeMenuItems(explode(",", $this->data['attrs']['remove_menu_items']));
        }
                
    }

     /**
     * Private function to remove menu items from a menu custom field
     * @param $removeArray array of menu items to remove
     * @return new array of menu items
     */
    private function _removeMenuItems($removeArray)
    {
        $newArray = array();
        foreach ($this->data['js']['hierData'][0] as $index => $obj)
        {
            if (!in_array($obj['value'], $removeArray))
                $newArray[] = $obj;
        }
        return $newArray;
    }

    /**
     * Utility function to retrieve category hier menu and massage
     * the data for our usage when "No Value" is selected for the product.
     */
    private function _setNoneDefaults()
    {
        $filterName = $this->data['attrs']['filter_type'];
        $hierData = $this->CI->Prodcat_model->hierMenuGet($filterName, 1, 0, $this->data['js']['linkingOn']);
        $this->data['js']['hierDataNone'] = array(array());
        foreach($hierData[0] as $value)
        {
            //parent is the node's parent id; hasChildren is a flag denoting whether a node has children
            array_push($this->data['js']['hierDataNone'][0], array('value' => $value[0], 'label' => $value[1], 'parentID' => 0, 'selected' => false, 'hasChildren' => $value[3]));
        }
        //add an additional 'no value' node to the front
        array_unshift($this->data['js']['hierDataNone'][0], array('value' => 0, 'label' => getMessage(NO_VAL_LBL)));
    }

    /**
     * Utility function to retrieve hier menus and massage
     * the data for our usage.
     * @param $hierItems Array List of hier menu IDs
     * @return Boolean T if the hierarchy data was successfully populated
     *                          or F if no hierarchy data was found
     */
    private function _setDefaults($hierItems)
    {
        $emptyReturns = 0;
        for($i = 0; $i < count($hierItems) + 1; $i++)
        {
            if($i <= 5)
            {
                $arrayIndex = ($i === 0) ? 0 : $hierItems[$i - 1];
                $hierData = $this->CI->Prodcat_model->hierMenuGet($this->data['attrs']['filter_type'], $i+1, $arrayIndex, $this->data['js']['linking_on']);
                if(!count($hierData[0]))
                    $emptyReturns++;
                $this->data['js']['hierData'][$i] = array();
                foreach($hierData[0] as $value)
                {
                    $selected = ($value[0] == $hierItems[$i]) ? true : false;
                    //parent is the node's parent id; hasChildren is a flag denoting whether a node has children
                    array_push($this->data['js']['hierData'][$i], array('value' => $value[0], 'label' => $value[1], 'parentID' => $arrayIndex, 'selected' => $selected, 'hasChildren' => $value[3]));
                }
            }
        }
        if($emptyReturns > count($hierItems))
            return false;
        //add an additional 'no value' node to the front
        array_unshift($this->data['js']['hierData'][0], array('value' => 0, 'label' => getMessage(NO_VAL_LBL)));
        return true;
    }
    /**
     * Utility function to retrieve hier menus for prod linking
     * and massage the data for our usage.
     * @param $hierItems Array List of hier menu IDs
     * @param $categoryArray Array List of hier menu IDs
     * @return Boolean T if the hierarchy data was successfully populated
     *                          or F if no hierarchy data was found
     */
    private function _setProdLinkingDefaults($productArray, $categoryArray)
    {
        $lastProdId = $productArray[count($productArray) - 1];
        if($lastProdId)
        {
            $hierArray = $this->CI->Prodcat_model->hierMenuGetLinkingWithLevel($lastProdId);
            if(count($hierArray) === 0)
                return false;
            uasort($hierArray, function($a, $b) {
                if(!count($a))
                    return -1;
                if(!count($b))
                    return 1;
                // for each parent, compare the first child's (index 0) level value (index 3) 
                if($a[0][3] === $b[0][3])
                    return 0;
                return $a[0][3] < $b[0][3] ? -1 : 1;
            });
            $this->data['js']['hierData'][0] = array();
            $count = 0;
            $matchIndex = 0;
            foreach($hierArray as $parentId => $children)
            {
                $validChild = false;
                $this->data['js']['hierData'][$count] = array();
                foreach($children as $child)
                {
                    $validChild = true;
                    $value = $child[0];
                    $label = $child[1];
                    $hasChildren = $child[2];
                    $selected = ($categoryArray[$matchIndex] == $value);
                    if($selected)
                        $matchIndex++;
                    //parent is the node's parent id; hasChildren is a flag denoting whether a node has children
                    array_push($this->data['js']['hierData'][$count], array('value' => $value, 'label' => $label, 'parentID' => $parentId, 'selected' => $selected, 'hasChildren' => $hasChildren));
                }
                if($validChild)
                    $count++;
            }
            $this->data['js']['link_map'] = $hierArray;
            //add an additional 'no value' node to the front
            array_unshift($this->data['js']['hierData'][0], array('value' => 0, 'label' => getMessage(NO_VAL_LBL)));
            return true;
        }
    }
}
