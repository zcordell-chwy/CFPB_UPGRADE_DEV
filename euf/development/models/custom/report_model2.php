<?php
use RightNow\Connect\v1 as RNCPHP;

/**
 * This class is copied from the standard report_model and provides functionality to allow
 * view incident complaints assigned to the same ComplaintAgainstOrg CBO using reports
 * This replaces the widgets/custom/instAgent/agentIncidents
 *
 * @author frank.tsai
 *
 */
class Report_model2 extends Model
{
    const htSearchOpenOK = 1;
    protected $isHTDigLoaded;
    protected $returnData;
    protected $reportID;
    protected $appliedFilters;
    protected $appliedFormats;
    protected $viewDefinition;
    protected $viewDataColumnDefinition;
    protected $reportIsTypeExternalDocument = false;
    protected $reportIsJoinedOnClusterTree = false;
    protected $answerTableAlias;
    protected $incidentTableAlias;
    protected $answerIDList = array();
    protected $CI;

    function __construct()
    {
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
	initConnectAPI();
        parent::__construct();
        $this->CI = get_instance();
        require_once(DOCROOT . '/views/view_utils.phph');
        $this->load->helper('config_helper');
    }

    /**************************************************************************************************
     * public functions for accessing information about report filters
     *************************************************************************************************/

    /**
     * setup function to recursively search for filter expression in multidimensional array
     * @param $filters array of runtime filters
     * @param #expression string- filter expression to lookup
     * @returns search filter array
     */
    function getSearchFilterInfo($filters, $expression)
    {
        foreach ($filters as $item)
        {
            if (($item === $expression) || (is_array($item) && $this->getSearchFilterInfo($item, $expression)))
            {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param $reportNumber int- the report to check
     * @returns array of all the runtime filter data
     */
    function getRuntimeFilters($reportNumber)
    {
        //echo"reportNum:$reportNumber";
        if ($this->isReportWidx($reportNumber))
            return array();
        $cacheKey = 'getRuntimeFilters' . $reportNumber;
        $runtimeFilters = checkCache($cacheKey);
        if ($runtimeFilters !== null)
            return $runtimeFilters;
        $filters = view_get_srch_filters($reportNumber);
        setCache($cacheKey, $filters);
        return $filters;
    }

     /**
    * Creates a search filter object.
    * This should be used if you are adding filters through hooks.  Use this to create the correctly formatted filter and add it to the filter array
    * @param $reportNumber int Report ID
    * @param $name String Filter name
    * @param $filterID Int Filter ID
    * @param $value Mixed Filter value
    * @param $rnSearchType (optional) rnSearchType; defaults to 'customName'
    * @param $operatorID Int Operator ID; defaults to 1
    * @return Object Filter object
    */
     function createSearchFilter($reportNumber, $name, $filterID, $value, $rnSearchType = 'customName', $operatorID = null)
     {
         $operatorID = ($operatorID !== null) ? $operatorID : OPER_EQ;
         $filter->filters->rnSearchType = $rnSearchType;
         $filter->filters->searchName = $name;
         $filter->filters->report_id = $reportNumber;
         $filter->filters->data->val = $value;
         $filter->filters->oper_id = $operatorID;
         $filter->filters->fltr_id = $filterID;
         return $filter;
     }

    /**
    * returns a default search filter.  Filters of type int or text are also included.
    * @param $reportNumber int - the report to check
    * @returns a default search type filter array if one exists
    */
    function getSearchFilterTypeDefault($reportNumber)
    {
        $default = array();
        if ($this->isReportWidx($reportNumber))
        {
            return array('fltr_id' => getConfig(EU_WIDX_SEARCH_BY_DEFAULT));
        }
        $rtFilters = $this->getRuntimeFilters($reportNumber);
        $first = true;
        if (is_array($rtFilters))
        {
            foreach($rtFilters as $key => $value)
            {
                if ($value['data_type'] === VDT_INT || $value['data_type'] === VDT_VARCHAR)
                {
                    if ($first)
                    {
                        $default = $rtFilters[$key];
                        $first = false;
                    }
                    if ($value['default_value'])
                    {
                        $default = $rtFilters[$key];
                    }
                }
            }
        }
        $default['default_value'] = ($default['default_value']) ? $default['default_value'] : "";
        return $default;
    }


    /**
     * Gets the runtime filter by fltr_id
     * @param $reportNumber int - the report to check
     * @param $filterID int - the filter you need
     * @return array data for a runtime filter
     */
    function getFilterById($reportNumber, $filterID)
    {
        foreach ($this->getRuntimeFilters($reportNumber) as $value)
        {
            if ($value['fltr_id'] == $filterID)
            {
                return $value;
            }
        }
        return null;
    }

    /**
    * @param $reportNumber int - the report
    * @param $filterName string - the name of the filter (i.e. prod_hier)
    * @return array of the data or null if the filter doesn't exist in the report or if the $filterName is empty.
    */
    function getFilterByName($reportNumber, $filterName)
    {
        if (strlen($filterName) > 0) {
            foreach($this->getRuntimeFilters($reportNumber) as $value) {
                if ($value['name'] === $filterName || stringContains($value['expression1'], $filterName)) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
    * converts the profile search type to the filter type
    * @param $reportNumber int - the report to check
    * @param $searchValue int - the profile value of the search type
    * @return a default search type filter if one exists
    */
    function getSearchTypeFromValue($reportNumber, $searchValue)
    {
        switch ($searchValue)
        {
        case SRCH_TYPE_NL:
            $searchName = PSEUDO_SEARCH_NL;
            break;
        case SRCH_TYPE_FNL:
            $searchName = PSEUDO_SEARCH_FNL;
            break;
        case SRCH_TYPE_EX:
            $searchName = PSEUDO_SEARCH_EX;
            break;
        case SRCH_TYPE_CPX:
            $searchName = PSEUDO_SEARCH_CPX;
            break;
        default:
            $searchName = "";
        }
        return ($this->getFilterByName($reportNumber, $searchName));
    }

    /**
     * gets the runtime filter data for a report id of type int or varchar
     * @param $reportNumber int - the report to check
     * @return all answer search filters
     */
    function getRuntimeIntTextData($reportNumber)
    {
        $runtimeFilters = $this->getRuntimeFilters($reportNumber);
        $filters = array();
        foreach($runtimeFilters as $key => $value)
        {
            if ($value['data_type'] === CDT_INT || $value['data_type'] === CDT_VARCHAR)
            {
                $filters[$key] = $value;
            }
        }
        return $filters;
    }

    /**
    * gets the search filter data for a report id
    * @param $reportNumber int - the report to check
    * @return all answer search filters
    */
    function getSearchFilterData($reportNumber)
    {
        $incidentAlias = $this->getIncidentAlias($reportNumber);
        $answerAlias = $this->getAnswerAlias($reportNumber);
        $filters = array();
        foreach($this->getRuntimeFilters($reportNumber) as $key => $value)
        {
            if ($answerAlias)
            {
                if ((stringContains($value['expression1'], PSEUDO_SEARCH_NL)) || stringContains($value['expression1'], PSEUDO_SEARCH_FNL) || stringContains($value['expression1'], PSEUDO_SEARCH_EX) || stringContains($value['expression1'], PSEUDO_SEARCH_CPX) )
                {
                    array_push($filters, $value);
                }
            }
            else if ($incidentAlias)
            {
                if (stringContains($value['expression1'], 'search_')
                    || stringContains($value['expression1'], 'ref_no'))
                {
                    array_push($filters, $value);
                }
            }
        }
        return $filters;
    }


    /**
    * returns the search type options list for external documents
    */
    function getExternalDocumentSearchOptions()
    {
        return array(WIDX_ANY_SEARCH     => getMessage(ANY_LBL),
                     WIDX_ALL_SEARCH     => getMessage(ALL_LBL),
                     WIDX_COMPLEX_SEARCH => getMessage(COMPLEX_LBL)
                     );
    }

    /**
    * returns the sort options list for external documents
    */
    function getExternalDocumentSortOptions()
    {
        return array(WIDX_SCORE_SORT     => getMessage(SCORE_LBL),
                     WIDX_TIME_SORT      => getMessage(TIME_LBL),
                     WIDX_TITLE_SORT     => getMessage(TITLE_LBL),
                     WIDX_REV_TIME_SORT  => getMessage(REVERSE_TIME_LBL),
                     WIDX_REV_TITLE_SORT => getMessage(REVERSE_TITLE_LBL)
                    );
    }

    /**
     * @param $bytes int
     * @return int
     */
    static function convertBytesToLargestUnit($bytes)
    {
        if ($bytes < 1024)
            return $bytes . 'b';
        if ($bytes < 1048576)
            return round($bytes / 1024, 0) . 'KB';
        if ($bytes < 1073741824)
            return round($bytes / 1048576, 0) . 'MB';
        // The numeric literal for TB must have ".0" on the end or PHP silently overflows and doesn't calculate correctly.
        if ($bytes < 1099511627776.0)
            return round($bytes / 1073741824, 0) . 'GB';
        return round($bytes / 1099511627776.0, 2) . 'TB';
    }

    /**
     * Increments the number of searches performed by the user
     */
    function updateSessionforSearch()
    {
        incrementNumberOfSearchesPerformed();
    }


    /**************************************************************************************************
     * public functions to get report data
     *************************************************************************************************/

    /**
    // filters[initial] - should the data be shown initially (use value of 1 to override report setting)
    // filters[page] - the page number to show
    // filters[per_page] - number to show
    // filters[level] - the drill down level requested
    // filters[no_truncate] - send 1 to prevent search limiting
    // filters[start_index] - index of first result (overrides filters[page])
    // filters[search] - send 1 to signify search, 0 for no search
    // $filters[recordKeywordSearch] - send 1 to signify that this search should
    //                     be recorded in the keyword_searches table
    //                     ($filters[search] must be set, as well)
    // filters[sitemap] - true to signify report is being run for sitemap output
    // format[highlight] - highlight text with <span></span> tags
    // format[emphasisHighlight] - highlight text with <em></em> tags
    // format[raw_date] - true to leave date fields alone
    // format[no_session] - do not append the session ID to URLs (applies to grid only)
    // format[urlParms] - string of key value pairs to add to any links
    // returnData[headers] = the visible headers formatted (ie date)
    // returnData[data] the visible data
    // returnData[data][i][text] - item to print - formated for date and wrapping text size and highlighted
    // returnData[data][i][a_id] - a_id of the item (if exists)
    // returnData[data][i][i_id] - i_id of the item (if exists)
    // returnData[data][i][link] - html link (if exists)
    // returnData[data][i][drilldown] - drilldown link to next level (if exists)
    // returnData[per_page] - number to show
    // returnData[page] - current page
    // returnData[total_num] - total number
    // returnData[total_pages] - number of total pages
    // returnData[row_num] - row numbers ahould be added to data
    // returnData[truncated] - search config truncated number to show
    // returnData[grouped] - id data on current level is grouped
    // returnData[initial] - if report should show on initial search
     */

    /**
     * get the data from the views engine and format it.
     *
     * @param $reportNumber int The analytics report number
     * @param $reportToken string The token matching the report number for security
     * @param $filters array A php array containing all the view data
     * @param $format array An array of ways to format the data
     *
     * @return array The report data in the correct format
     */
    function getDataHTML($reportNumber, $reportToken, $filters, $format)
    {
        if ($this->preProcesssData($reportNumber, $reportToken, $filters, $format))
        {
            $this->getData(false);
            $this->formatData();
            $this->getOtherKnowledgeBaseData();
        }
        return $this->returnData;
    }

    /**
     * Copy of getDataXML, but retrieves hidden field data, and does not add HTML
     *
     * @param $reportNumber int The analytics report number
     * @param $reportToken string The token matching the report number for security
     * @param $filters array A php array containing all the view data
     * @param $format array An array of ways to format the data
     *
     * @return array The report data in the correct format
     */
    function getDataXML($reportNumber, $reportToken, $filters, $format)
    {
        if ($this->preProcesssData($reportNumber, $reportToken, $filters, $format))
        {
            $this->getData(true);
            $this->formatData(false);
            $this->getOtherKnowledgeBaseData();
        }
        return $this->returnData;
    }

/**************************************************************************************************
 * common helper functions
 *************************************************************************************************/

    private function isAnswerListReportWithoutSpecialSettingsFilter($reportID)
    {
        $report = _report_get($reportID);
        return $this->doesReportIncludeAnswerTable($report) && !$this->doesReportIncludeAnswerSpecialSettingsFilter($report);
    }

    private function doesReportIncludeAnswerTable($report)
    {
        $tables = $report['tables'];
        if (!is_array($tables))
        {
            return false;
        }

        foreach ($tables as $table)
        {
            if (is_array($table) && $table['tbl'] === TBL_ANSWERS)
            {
                return true;
            }
        }
        return false;
    }

    private function doesReportIncludeAnswerSpecialSettingsFilter($report)
    {
        $filters = $report['filters'];
        if (!is_array($filters))
        {
            return false;
        }
        foreach ($filters as $filter)
        {
            if ($filter['val1'] === self::answersSpecialSettingsFilterName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @private
     */
    const answersSpecialSettingsFilterName = 'answers.special_settings';

    /**
     * runs the hooks witht the incoming data
     * sets up the default return array and class defaults
     * checks for token and proper widx set up
     * runs any preprocesses for the data
     * @param $reportNumber int
     * @param $reportToken string
     * @param $filters array of incoming filters
     * @param $format array of format options
     * @return bool false if error was encountered
     */
    protected function preProcesssData($reportNumber, $reportToken, $filters, $format)
    {
        // run hook
        $preHookData = array('data'=>array('reportId'=>$reportNumber, 'filters'=>$filters, 'format'=>$format));
        RightNowHooks::callHook('pre_report_get', $preHookData);
        $this->reportID = intval($preHookData['data']['reportId']);
        $this->appliedFilters = $preHookData['data']['filters'];
        $this->appliedFormats = $preHookData['data']['format'];

        // set up default return values
        $this->reportIsTypeExternalDocument = $this->isReportWidx($this->reportID);
        $this->setDefaultReportResult();
        $this->returnData['report_id'] = $this->reportID;
        $this->answerTableAlias = $this->getAnswerAlias($this->reportID);
        $this->incidentTableAlias = $this->getIncidentAlias($this->reportID);

        // check for valid report_id
        if ($this->checkTokenError($reportToken) || $this->checkInterfaceError())
        {
            return false;
        }

        // check for valid widx set up
        if ($this->isReportNumberWidx($this->reportID))
        {
            if(getConfig(WIDX_MODE) === 0)
            {
                $this->returnData['error'] = getMessage(ENABLE_WEB_INDEXING_ORDER_RPT_MSG);
                return false;
            }
        }

        // preprocess data
        if (is_array($this->appliedFilters['keyword']->filters->data))
            $this->appliedFilters['keyword']->filters->data = $this->appliedFilters['keyword']->filters->data[0];
        // don't use get_object_vars if the prod/cat filter data includes reconstructData because we have special (implicit)
        // handling of that in filtersToSearchArgs looking for node['0'] specifically
        if (is_object($this->appliedFilters['p']->filters->data) && !$this->appliedFilters['p']->filters->data->reconstructData)
            $this->appliedFilters['p']->filters->data = get_object_vars($this->appliedFilters['p']->filters->data);
        if (is_object($this->appliedFilters['c']->filters->data) && !$this->appliedFilters['c']->filters->data->reconstructData)
            $this->appliedFilters['c']->filters->data = get_object_vars($this->appliedFilters['c']->filters->data);
        $this->preProcessClusterTreeFilter();
        return true;
    }

    /**
     * @param $showHiddenColumns bool
     * Common data retrieval for getDataXML and getDataHTML.
     */
    protected function getData($showHiddenColumns)
    {

        if ($this->reportIsTypeExternalDocument)
        {
            $this->getExternalSearchData();
        }
        else
        {
            $this->getReportData($showHiddenColumns);
        }
        $this->viewDefinition['error'] = ($this->viewDefinition['error'] == '') ?  null : (getMessage(ERR_RPT_TABULATURE_FMT_RPT_ID_MSG) . $this->reportID);
        $this->returnData['error'] = $this->returnData['error'] ? $this->returnData['error'] : $this->viewDefinition['error'];
    }

    /**
     * Formats the views data
     * @param $isHtml bool {optional} whether to format links in html syntax; defaults to true
     */
    protected function formatData($isHtml = true)
    {
        if($this->reportIsTypeExternalDocument && $this->returnData['search_term'])
            $this->formatExternalSearchData();
        else
            $this->formatViewsData($isHtml);

        if($this->appliedFilters['search'] && $this->appliedFilters['recordKeywordSearch'])
            $this->keywordSearchUpdate();
    }

    /**
     * function to add the related prod/cats, spelling, topic words
     */
    protected function getOtherKnowledgeBaseData()
    {
        $this->getKBStrings();
        $searchSuggestions = getConfig(SEARCH_SUGGESTIONS_DISPLAY);

        $this->returnData['related_prods'] = $this->returnData['related_cats'] = array();
        if(($searchSuggestions & SRCH_SUGGESTIONS_DSPLY_PRODS) && $this->returnData['search_term'] && !$this->reportIsTypeExternalDocument)
            $this->returnData['related_prods'] = $this->getSuggestedSearch($this->answerIDList, HM_PRODUCTS, PH_TYPE_PROD);
        if(($searchSuggestions & SRCH_SUGGESTIONS_DSPLY_CATS) && $this->returnData['search_term'] && !$this->reportIsTypeExternalDocument)
            $this->returnData['related_cats'] = $this->getSuggestedSearch($this->answerIDList, HM_CATEGORIES, PH_TYPE_CAT);

        if($this->reportIsJoinedOnClusterTree)
        {
            $this->CI->load->model('standard/Topicbrowse_model');
            $this->returnData['topics'] = $this->CI->Topicbrowse_model->searchBrowseTree($this->returnData['search_term']);
        }

        if (count($this->returnData['data']) && is_numeric($this->returnData['not_dict']))
            $this->returnData['not_dict'] = '';
    }

      /**
     * checks security tokens
     * @param $token string - The token
     * @return bool false if there is no error
     */
    protected function checkTokenError($token)
    {
        return !(isValidSecurityToken($token, $this->reportID));
    }

    /**
     * checks interface for this report and sets error
     * @return bool false if there is no error
     *
     */
    protected function checkInterfaceError()
    {
        if (!$this->isReportNumberWidx($this->reportID) && !verify_interface($this->reportID))
        {
            $this->returnData['error'] = getMessage(REPORT_NOT_VISIBLE_INTERFACE_LBL);
            return true;
        }
        return false;
    }

    /**
     * Returns an array initialized to the defaults for a report result.
     * @returns An array initialized to the defaults for a report result.
     */
    protected function setDefaultReportResult()
    {
        $this->returnData = array(
            'data' => array(),
            'headers' => array(),
            'per_page' => 0,
            'total_pages' => 0,
            'total_num' => 0,
            'row_num' => 1,
            'truncated' => 0,
            'start_num' => 0,
            'end_num' => 0,
            'initial' => 0,
            'search_type' => 0,
            'search' => 0
        );
    }

    /**
     * gets kb data that is modified for html
     *
     * @return array
     */
    function getKBStrings()
    {
        // did you mean
        $this->returnData['spelling'] = "";
        $this->returnData['not_dict'] = "";

        if(strlen($this->returnData['search_term']) > 0)
        {
            $data = $this->didYouMean();
            if($data['dym'])
            {
                $this->returnData['spelling'] = str_replace("<i>", "", $data['dym']);
                $this->returnData['spelling'] = str_replace("</i>", "", $this->returnData['spelling']);
            }
            $this->returnData['not_dict'] = $data['nodict'];
            $this->returnData['stopword'] = $data['stopword'];
        }

        // suggested
        $this->returnData['ss_data'] = $this->getSimilarSearches();
        // topic words
        $this->returnData['topic_words'] = $this->getTopicWords();
    }

    /**
     * gets the similar search terms
     * @return array
     */
    protected function getSimilarSearches()
    {
        $searchTerm = $this->returnData['search_term'];
        if(getConfig(EU_SUGGESTED_SEARCHES_ENABLE) && $searchTerm)
        {
            $searchTextStem = strip_affixes($searchTerm);
            $sortedStemString = sort_word_string($searchTextStem);
            $sortedStemString = trim($sortedStemString);
            $escapedSortedStemString = strtr($sortedStemString, $this->CI->rnow->getSqlEscapeCharacters());

            $interfaceID = intf_id();
            $query = "SELECT s2.srch FROM similar_searches s1, similar_searches s2, similar_search_links sl
                      WHERE s1.id=sl.to_id AND sl.from_id=s2.id AND s1.stem='$escapedSortedStemString'
                      AND s2.interface_id = $interfaceID AND s1.interface_id = $interfaceID AND sl.interface_id = $interfaceID
                      ORDER BY sl.ml_weight DESC";

            $si = sql_prepare($query);
            sql_bind_col($si, 1, BIND_NTS, 255);

            $rowCount = 0;
            while(($row = sql_fetch($si)) && ($rowCount < 7))
            {
                $suggestedSearchData[$rowCount] = $row[0];
                $rowCount++;
            }
            sql_free($si);
        }
        return($suggestedSearchData);
    }

    /**
    * puts keyword search term into clickstreams
    */
    protected function keywordSearchUpdate()
    {
        $term = $this->returnData['search_term'];
        $total = $this->returnData['total_num'];
        if ($this->reportIsTypeExternalDocument)
            $source = SRCH_EXT_DOC;
        else if($this->reportIsJoinedOnClusterTree)
            $source = SRCH_BROWSE;
        else if($this->answerTableAlias)
            $source = SRCH_END_USER;
        else
            return;
        static $alreadyUpdated = false;
        if($term && $term != '' && !$alreadyUpdated && !$this->CI->rnow->isSpider())
        {
            $list = implode(',', array_unique($this->answerIDList));
            if(count($this->answerIDList)){
                $list .= ','; //API require that the last char is ,. This needs to be changed
            }

            $list = trim($list);
            $list = $list ? $list : NULL;
            if (IS_PRODUCTION)
            {
                $listSize  = strlen($list);
                if($listSize > 255){ //Check DB limit:if more than 255, removed from the end of the list.
                    $lastPosition =  strrpos($list, ',', 254 - $listSize);
                    if ($lastPosition === false) { // Maybe list is misconstructed
                        $list = substr($list, 0, 255);
                    } else {
                        $list = substr($list, 0, $lastPosition + 1);
                    }
                }
                $this->CI->Clickstream_model->insertKeywordSearch($term, $total, $list, $source);
            }
            $alreadyUpdated = true;
        }
    }

    /**
    * check if the page requested is outside of the proper bounds for the request
    */
    protected function checkValidPageNumberRequest()
    {
        if (isset($this->appliedFilters['page']) && $this->returnData['total_pages'] != 0 && ($this->appliedFilters['page'] > $this->returnData['total_pages'] || $this->appliedFilters['page'] <= 0))
        {
            $this->returnData['data'] = array();
            $this->returnData['total_num'] = 0;
            $this->returnData['start_num'] = 0;
            $this->returnData['total_pages'] = 0;
            $this->returnData['end_num'] = 0;
            $this->returnData['page'] = 1;
        }
    }

    /**
     * Knowledgebase function to get topic words based on the
     * specified query text.
     *
     * @return array The topic words associated with that keyword
     */
    function getTopicWords()
    {
        $searchWord = $this->returnData['search_term'];
        $cacheKey = 'topicWords-'.$searchWord;
        $topicWordCache = checkCache($cacheKey);
        if($topicWordCache !== null)
            return $topicWordCache;

        if(icache_int_get(ICACHE_TOPIC_WORDS_EXIST))
        {
            if(strlen($searchWord) > 0)
            {
                $searchTextStem = strip_affixes($searchWord);
                $keywords = preg_split("/[\s,]+/", $searchTextStem);
                foreach ($keywords as $element)
                {
                    $escapedElement = strtr($element, $this->CI->rnow->getSqlEscapeCharacters());
                    $wordList .= "'$escapedElement',";
                }
                $keywordCount = count($keywords);
                if($keywordCount > 1)
                {
                    for($i = 0; $i < $keywordCount; $i++)
                    {
                        $mwEscapedElement = strtr($keywords[$i], $this->CI->rnow->getSqlEscapeCharacters());
                        //5-words is our limit
                        for($j = $i+1; ($j<$keywordCount) && ($j< $i+5); $j++)
                        {
                            $mwEscapedElement .= " ".strtr($keywords[$j], $this->CI->rnow->getSqlEscapeCharacters());
                            $wordList .= "'$mwEscapedElement',";
                        }
                    }
                }

                $wordListSize = strlen($wordList);
                $wordList[$wordListSize-1] = " ";

                $sql = sprintf("SELECT DISTINCT t.tw_type, t.a_id,
                    a.summary, t.title, t.text, t.url, a.type, a.url
                    FROM topic_words_phrases ti, topic_words t
                    LEFT OUTER JOIN answers a ON a.a_id=t.a_id
                    WHERE interface_id=%d
                    AND (a.status_type=%d OR a.status_type IS NULL)
                    AND (ti.stem IN (%s) AND t.state = 1
                    AND ti.tw_id=t.tw_id)
                    UNION SELECT DISTINCT t.tw_type, t.a_id, a.summary,
                    t.title, t.text, t.url, a.type, a.url
                    FROM topic_words_phrases ti, topic_words t
                    LEFT OUTER JOIN answers a ON a.a_id=t.a_id
                    WHERE interface_id=%d AND (a.status_type=%d
                    OR a.status_type IS NULL)
                    AND (t.state = 2)",
                    intf_id(), STATUS_TYPE_PUBLIC, $wordList, intf_id(), STATUS_TYPE_PUBLIC);
            }
            else
            {
                $sql = sprintf("SELECT t.tw_type, t.a_id, a.summary, t.title,
                    t.text, t.url, a.type, a.url
                    FROM topic_words t
                    LEFT OUTER JOIN answers a ON a.a_id=t.a_id
                    WHERE interface_id=%d
                    AND (a.status_type=%d OR a.status_type IS NULL)
                    AND t.state = 2", intf_id(), STATUS_TYPE_PUBLIC);
            }
            $si = sql_prepare($sql);
            $i = 1;
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_NTS, 255);
            sql_bind_col($si, $i++, BIND_NTS, 255);
            sql_bind_col($si, $i++, BIND_NTS, 255);
            sql_bind_col($si, $i++, BIND_NTS, 255);
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_NTS, 255);

            $topicWordData = array();
            while(list($topicType, $answerID, $summary, $title, $text, $topicUrl, $answerType, $answerUrl) =  sql_fetch($si))
            {
                $icon = '';
                if($answerType === ANSWER_TYPE_HTML)
                    $icon = getIcon('RNKLANS');
                else if ($answerType === ANSWER_TYPE_ATTACHMENT || $answerType === ANSWER_TYPE_URL)
                    $icon = getIcon($answerUrl);
                else if($topicUrl != "")
                    $icon = getIcon($topicUrl);
                $topicWordTitle = $title != '' ? $title : $summary;

                $topicWordItem = array('url' => $topicUrl != "" ? $topicUrl : getShortEufAppUrl('sameAsCurrentPage', getConfig(CP_ANSWERS_DETAIL_URL) . '/a_id/' . $answerID . sessionParm()),
                                    'title' => print_text2str($topicWordTitle, OPT_VAR_EXPAND|OPT_ESCAPE_SCRIPT|OPT_ESCAPE_HTML),
                                    'text' => print_text2str($text, OPT_VAR_EXPAND|OPT_ESCAPE_SCRIPT|OPT_ESCAPE_HTML),
                                    'icon' => $icon
                                    );
                array_push($topicWordData, $topicWordItem);
            }
            sql_free($si);
        }
        setCache($cacheKey, $topicWordData);
        return $topicWordData;
    }

    /**
     * Gets suggested searches from the keyword temp table when a search is performed
     *
     * @return Suggested searches for the query that was done
     * @param $list Array Array of answer IDs that are present in the report
     * @param $type int Define for hm_type
     * @param $tempTableType int Number defining if we're getting products or categories
     */
    function getSuggestedSearch($list, $type, $tempTableType)
    {
        $searchTerm = $this->returnData['search_term'];
        $displayMax = getConfig(MAX_SEARCH_SUGGESTIONS);

        $keywordTempTable = build_temp_index_hier($searchTerm);
        if(($keywordTempTable !== "") && (count($list)))
        {
            $sql = sprintf("SELECT l.label, m.lvl1_id, m.lvl2_id, m.lvl3_id, m.lvl4_id, m.lvl5_id, m.lvl6_id
                FROM labels l, hier_menus m, $keywordTempTable t
                WHERE l.label_id = t.id AND l.fld = 1 AND l.tbl = %s AND
                m.hm_type = $type AND m.id = t.id AND t.type = $tempTableType AND l.lang_id=%d ORDER BY t.weight DESC",
                TBL_HIER_MENUS, lang_id(LANG_DIR));
            if($displayMax)
                $sql .= " LIMIT $displayMax";

            $si = sql_prepare($sql);
            $i = 1;
            sql_bind_col($si, $i++, BIND_NTS, 41);
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_INT, 0);
            sql_bind_col($si, $i++, BIND_INT, 0);

            $results = array();
            while($row = sql_fetch($si))
            {
                $result = array();
                $result['label'] = $row[0];
                $idArray = array();
                for($j = 1; $j <= 6; $j++)
                {
                    if($row[$j] != "")
                        array_push($idArray, $row[$j]);
                    else
                        break;
                }
                $result['id'] = implode(",", $idArray);
                array_push($results, $result);
            }
            sql_free($si);
            return $results;
        }
    }

    /**
     * takes the link in the report and replaces any column names with values
     * @param $url string is the original link
     * @param $values array is the row values
     * @param $urlParms existing parameters in the url
     * @returns string a converted link
     */
    protected function replaceColumnLinks($url, $values, $urlParms = '')
    {
        if ($this->reportIsTypeExternalDocument)
        {
            $url = str_replace(getConfig(CP_ANSWERS_DETAIL_URL), getConfig(CP_WEBSEARCH_DETAIL_URL), $url);
        }
        $str = $url;
        $start = strpos($str, "&lt;");
        if ($start === false)
        {
            return $url .= $urlParms;
        }
        while($start !== false)
        {
            $end = strpos($str, "&gt;");
            $name = substr($str, $start + 4, ($end - $start - 4));
            $rep = "&lt;$name&gt;";
            $value = $values[$name - 1];
            $url = substr_replace($url, $value, $start, strlen($rep));
            $start = strpos($str, "&lt;", $end);
        }
        return $url .= $urlParms;
    }

    /**
     * gets the data for the result info for spelling, stopwords, etc.
     * @return array
     */
    protected function didYouMean()
    {
        $searchTerm = $this->returnData['search_term'];
        $searchType = $this->returnData['search_type'];
        $data = array();
        if(getConfig(EU_SEARCH_TERM_FEEDBACK_ENABLE) && $searchTerm && $searchType)
        {
            $isComplex = $this->getComplex($searchType);
            $incident = ($this->incidentTableAlias) ? 1 : 0;
            if ($isComplex)
            {
                $queryResults = rnkl_query_parse(0x0040, $searchTerm, $incident);
            }
            else
            {
                $queryResults = rnkl_query_parse(0x0004, $searchTerm, $incident);
            }

            if (($queryResults['nspelled'] > 0) || ($queryResults['ntrimmed'] > 0) || ($queryResults['nid'] > 0))
            {
                // did you mean (roan)
                if ($queryResults['nspelled'])
                {
                    $data['dym'] = trim($queryResults['dym']);
                }

                // stopwords (and)
                if ( $queryResults['ntrimmed'] > 0)
                {
                    $data['stopword'] = trim($queryResults['trimmed']);
                }

                // not in dict (dfa)
                if ( $queryResults['nid'] > 0)
                {
                    $data['nodict'] = trim($queryResults['nodict']);
                }
                $data['aliases'] = trim($queryResults['aliases']);
            }

            if($rowCount == 0)
            {
                if ($queryResults['nomatch'])
                {
                    $data['nores'] = getMessage(NO_RES_FND_PLEASE_EXP_YOUR_QUERY_MSG);
                }
                else if ($queryResults['nodict'])
                {
                    $data['nodict'] = trim($queryResults['nodict']);
                }
            }
        }
        return $data;
    }

/*****************************************************************************************
 * external document functions
 *****************************************************************************************/
    /**
     * Function to get external document results
     */
    protected function getExternalSearchData()
    {
        $searchTerm = (isset($this->appliedFilters['keyword']->filters->data->val)) ? $this->appliedFilters['keyword']->filters->data->val : $this->appliedFilters['keyword']->filters->data;
        if (!$searchTerm)
        {
            $this->getStandardAnswerReportInsteadOfWidx();
            return;
        }
        $this->loadHTDigLibrary();
        $htSearchQueryArguments = $this->getHTSearchQueryArguments($searchTerm);
        $cacheKey = "getExternalSearchData$this->reportID" . serialize($htSearchQueryArguments);
        if (null !== ($cachedResult = checkCache($cacheKey)))
        {
            $this->returnData = $cachedResult;
        }

        $errorMessage = false;
        $webindexPath = webindex_path();
        $webindexConfigFile = "$webindexPath/webindex.conf";

        if (isReadableFile($webindexConfigFile))
        {
            $htSearchOpenResult = htsearch_open($this->getHTSearchOpenArguments($webindexConfigFile, $webindexPath));
            if ($htSearchOpenResult != self::htSearchOpenOK)
            {
                $errorMessage = $this->getOpenHTDigErrorMessage($htSearchOpenResult);
            }
            else
            {
                $rowCount = htsearch_query($htSearchQueryArguments);
                if ($rowCount < 0)
                {
                    if($rowCount == HTSEARCH_ERROR_QUERYPARSER_ERROR)
                        $errorMessage = getMessage(ERR_BAD_SRCH_QUERY_SYNTAX_MSG);
                    else if(($rowCount <= -209 ) && ($rowCount >= -212 ))
                        $errorMessage = getMessage(ERR_OPEN_IDX_EMPTY_CORRUPT_MSG);
                    $rowCount = 0; // Set the number of rows found to zero so that don't say something silly like "viewing 0-0 of -213 answers."
                }
            }
        }

        if ($errorMessage)
            $this->returnData['error'] = $errorMessage;
        $this->returnData['search_term'] = $searchTerm;
        $this->returnData['grouped'] = 0;
        $pageNumber = $this->appliedFilters['page'] ? intval($this->appliedFilters['page']) : 1 ;
        $numberPerPage = $this->getNumberPerPage($this->appliedFilters['per_page'], 0);
        $this->returnData['data'] = $this->getExternalSearchResults($numberPerPage, $rowCount, $pageNumber);
        $numberThisPage = count($this->returnData['data']);
        $this->returnData['headers'] = $this->getExternalDocumentHeaders();
        $this->returnData['page'] = $pageNumber;
        $this->returnData['total_num'] = $rowCount;
        $this->returnData['start_num'] = ($this->returnData['total_num'] > 0) ? ($numberPerPage * ($pageNumber - 1) + 1) : 0;
        $this->returnData['per_page'] = $numberThisPage;
        $this->returnData['total_pages'] = ($numberPerPage > 0) ? ceil($rowCount/$numberPerPage) : 0;
        $this->returnData['end_num'] = ($this->returnData['total_num'] > 0) ? ($this->returnData['start_num'] + $numberThisPage - 1) : 0;
        $this->returnData['row_num'] = 1;

        if ($htSearchOpenResult == self::htSearchOpenOK)
        {
            htsearch_close();
        }
        $this->checkValidPageNumberRequest();
        $data = array($this->returnData, array(), array());
        setCache($cacheKey, $data);
    }

    /**
     * gets the external results from the api
     * @param $numberPerPage
     * @param $resultCount
     * @param $pageNumber
     * @return unknown_type
     */
    protected function getExternalSearchResults($numberPerPage, $resultCount, $pageNumber)
    {
        $results = array();
        $firstResultIndex = ($pageNumber - 1) * $numberPerPage;
        $maxResultIndex = min($resultCount, $firstResultIndex + $numberPerPage);
        for ($n = $firstResultIndex; $n < $maxResultIndex; ++$n)
        {
            $row = htsearch_get_nth_match($n);
            $answerID = $row['id'];

            if ($answerID == 0)
            {
                if ((strlen($row['title']) == 0) || ($row['title'] == '[No title]'))
                    $title = $url;
                else
                    $title=$row['title'];

                $link = $row['URL'];
                $icon = getIcon($link, true);
                $url = '<a href="'.$link.'">'.$title.'</a>';
            }
            else
            {
                $link = getShortEufAppUrl('sameAsCurrentPage', getConfig(CP_WEBSEARCH_DETAIL_URL));
                $link .= '/a_id/' . $answerID . sessionParm();
                $url = '<a href="'.$link.'">'.$row['title'].'</a>';
                if(strncmp($row['URL'], 'RNKLURL', 7) == 0)
                {
                    $row['URL'] = $row['name'];
                    $link = $row['name'];
                    $url = '<a href="'.$link.'">'.$row['title'].'</a>';
                }
                else if (strncmp($row['URL'], 'RNKLATTACH', 10) == 0)
                {
                    if(preg_match('/filename=(.*):p_created=(.*)/', $row['name'], $matches) != 0)
                        $row['URL'] = $matches[1];
                }
                $icon = getIcon($row['URL']);

                if (in_array(LANG_DIR, array('ja_JP', 'ko_KR', 'zh_CN', 'zh_TW', 'zh_HK')))
                    $row['excerpt'] = $this->getHighlightingFromAnswerID($answerID);
            }

            $score = max(1, (int)($row['score'] * 100));
            $result = array($icon, $url, $row['excerpt'], $row['size'], $row['time'], $score);
            if (getConfig(EU_WIDX_SHOW_URL))
                $result[] = $link;
            $results[] = $result;
        }
        return $results;
    }

    /**
     * converts filters to external document format
     * @param $searchTerm string
     * @return array
     */
    protected function getHTSearchQueryArguments($searchTerm)
    {
        $oldSortArgs = $this->appliedFilters['sort_args'];
        $webSortArgs = $this->appliedFilters['webSearchSort'];
        $webSearchArgs = $this->appliedFilters['webSearchType'];
        if ($webSortArgs)
        {
            $sortArgs = $webSortArgs->filters->data->col_id;
        }
        if ($webSearchArgs)
        {
            $searchArgs = $webSearchArgs->filters->data;
        }
        if ($oldSortArg && !isset($sortArgs) && !isset($searchArgs))
        {
            if (is_array($oldSortArg))
            {
                $sortArgs = $oldSortArgs['filters']['col_id'];
                $searchArgs = $oldSortArgs['sort_field0']['search_type'];
            }
            else
            {
                $sortArgs = $oldSortArgs->filters->col_id;
                $searchArgs = $oldSortArgs->sort_field0->search_type;
            }
        }

        $internalSort = ($sortArgs) ? $this->getExternalDocumentSortByType($sortArgs) : $this->getExternalDocumentSortByType(getConfig(EU_WIDX_SORT_BY_DEFAULT));
        $searchType = ($searchArgs) ? $this->getExternalDocumentSearchByType($searchArgs) : $this->getExternalDocumentSearchByType(getConfig(EU_WIDX_SEARCH_BY_DEFAULT));
        // parse the text into seperate fields
        $searchFields = rnkl_ext_doc_query_parse($searchType, 1, str_replace('%', ' ', $searchTerm));

        return array(
            'optional_query' => $searchFields['optional_query'],
            'required_query' => $searchFields['required_query'],
            'forbidden_query' => $searchFields['forbidden_query'],
            'prefix_query' => $searchFields['prefix_query'],
            'synonym_query' => $searchFields['synonym_query'],
            'algorithm' => HTSEARCH_ALG_BOOLEAN_STR,
            'sortby' => $internalSort,
            'format' => HTSEARCH_FORMAT_LONG_STR
        );
    }

    /**
     * gets the error string based on error type
     * @param $htSearchOpenResult bool
     * @return string of error
     */
    protected function getOpenHTDigErrorMessage($htSearchOpenResult)
    {
        switch ($htSearchOpenResult)
        {
        case HTSEARCH_ERROR_INDEX_NOT_FOUND:
            return getMessage(ERROR_EXTERNAL_DOC_INDEX_EXIST_MSG);
        case HTSEARCH_ERROR_CONFIG_READ:
            return getMessage(ERROR_READ_CONFIGURATION_FILE_MSG);
        case HTSEARCH_ERROR_LOGFILE_OPEN:
            return getMessage(ERROR_UNABLE_TO_OPEN_LOGFILE_MSG);
        default:
            return getMessage(ERR_OPEN_IDX_FAILED_MSG);
        }
    }

    /**
     * gets the headers array for external documents
     * @return array
     */
    protected function getExternalDocumentHeaders()
    {
        if($this->reportID === CP_NOV09_WIDX_DEFAULT)
        {
            $headers = array(
                array('heading' => ''),
                array('heading' => getMessage(SUMMARY_LBL)),
                array('heading' => getMessage(DESCRIPTION_LBL))
            );
            if(getConfig(EU_WIDX_SHOW_URL))
                array_push($headers, array('heading' => getMessage(URL_LBL)));
            array_push($headers, array('heading' => getMessage(LAST_UPDATED_LBL)));
        }
        else
        {
            $headers = array(
                array('heading' => ''),
                array('heading' => getMessage(SUMMARY_LBL)),
                array('heading' => getMessage(DESCRIPTION_LBL)),
                array('heading' => getMessage(SIZE_LBL)),
                array('heading' => getMessage(LAST_UPDATED_LBL)),
                array('heading' => getMessage(SCORE_LBL))
            );
            if(getConfig(EU_WIDX_SHOW_URL))
                array_push($headers, array('heading' => getMessage(URL_LBL)));
        }

        return $headers;
    }

    /**
     * opens the libraries for external documents
     */
    protected function loadHTDigLibrary()
    {
        if ($this->isHTDigLoaded)
            return;

        if(!extension_loaded('htdig'))
            dl('libhtdigphp' . sprintf(DLLVERSFX, MOD_HTDIG_BUILD_VER));

        if (IS_HOSTED && !IS_OPTIMIZED)
            require_once(DOCROOT . '/include/src/htdig.phph');
        else if (!IS_HOSTED)
            require_once(DOCROOT . '/include/htdig.phph');

        $this->isHTDigLoaded = true;
    }

    /**
     * if the report is external document but there is no search term then we retrieve the standard report instead
     */
    protected function getStandardAnswerReportInsteadOfWidx()
    {
        //if using websearch mode but mode is set to display only answers then we want to set search filters back to default
        //rather than report id 10016
        $this->reportID = ($this->reportID == CP_WIDX_REPORT_DEFAULT) ? CP_REPORT_DEFAULT : CP_NOV09_ANSWERS_DEFAULT;
        $this->answerTableAlias = $this->getAnswerAlias($this->reportID);
        $this->incidentTableAlias = $this->getIncidentAlias($this->reportID);
        $this->reportIsTypeExternalDocument = false;
        unset($this->appliedFilters['sort_args']);

        // get a search type for the views engine
        $st = $this->getSearchFilterTypeDefault($this->reportID);
        if ($st)
        {
            $this->appliedFilters['searchType']->filters->fltr_id = $st['fltr_id'];
            $this->appliedFilters['searchType']->filters->data = $st['fltr_id'];
            $this->appliedFilters['searchType']->filters->oper_id = $st['oper_id'];
        }

        $this->getReportData(false);
    }

    /**
     * gets data for the external documents engine
     * @param $webIndexConfigFile string
     * @param $webIndexPath string
     * @return array
     */
    protected function getHTSearchOpenArguments($webIndexConfigFile, $webIndexPath)
    {
        $restrict = getConfig(WIDX_SEARCH_RESTRICT);
        $exclude  = getConfig(WIDX_SEARCH_EXCLUDE);
        $alwaysreturn = ' RNKLANS RNKLATTACH RNKLURL ';

        //
        // EU_WIDX MODE can affect the search filters if it's zero or two
        //
        $euWidxMode = getConfig(EU_WIDX_MODE);
        if ($euWidxMode === 0)
        {
            $restrict .= " RNKLANS RNKLATTACH RNKLURL ";
        }
        else if ($euWidxMode === 2)
        {
            $exclude .= ' RNKLANS RNKLATTACH RNKLURL ';
            $alwaysreturn = '';
        }

        $webIndexLogFile = "$webIndexPath/logs/htsearch.log";
        $webIndexBedebugFile = "$webIndexPath/logs/htsearch.debug.log";

        return array(
            'configFile' => $webIndexConfigFile,
            'logFile' => $webIndexLogFile,
            'debugFile' => $webIndexBedebugFile,
            'DBpath' => $webIndexPath,
            'search_restrict' => $restrict,
            'search_exclude' => $exclude,
            'search_alwaysreturn' => $alwaysreturn,
            'keyword_factor' => getConfig(SRCH_KEY_WEIGHT, 'COMMON'),
            'text_factor' => getConfig(SRCH_BODY_WEIGHT, 'COMMON'),
            'title_factor' => getConfig(SRCH_SUBJ_WEIGHT, 'COMMON'),
            'meta_description_factor' => getConfig(SRCH_DESC_WEIGHT, 'COMMON'),
            'debug' => 0
        );
    }

    /**
     * Formats external searching report data
     */
    protected function formatExternalSearchData()
    {
        foreach ($this->returnData['data'] as &$row)
        {
            if($this->appliedFormats['truncate_size'] > 0)
                $row[2] = truncateText($row[2], $this->appliedFormats['truncate_size'], true, $this->appliedFormats['max_wordbreak_trunc']);

            //Highlight title and summary
            if ($this->appliedFormats['highlight'])
            {
                $row[1] = highlightTextHelper($row[1], $this->returnData['search_term'], $this->appliedFormats['highlightLength']);
                $row[2] = highlightTextHelper($row[2], $this->returnData['search_term'], $this->appliedFormats['highlightLength']);
            }
            else if ($this->appliedFormats['emphasisHighlight'])
            {
                $row[1] = emphasizeText($row[1], array('query'=>$this->returnData['search_term']));
                $row[2] = emphasizeText($row[2], array('query'=>$this->returnData['search_term']));
            }
            if($this->reportID === CP_WIDX_REPORT_DEFAULT)
            {
                $row[3] = $this->convertBytesToLargestUnit($row[3]);
                $row[4] = date_str(DATEFMT_SHORT, $row[4]);

                $score = $row[5];
                $scoreHtml = "$score&nbsp;&nbsp;";
                $altText = getMessage(SCORE_LBL).": $score";
                $tempScore = $score / 2;
                $scoreHtml .= "<img src='images/icons/widxdark.gif' style='vertical-align:middle;' height=8 width=$tempScore alt='$altText'>";
                $tempScore = (HTSEARCH_MAX_SCORE - (int)($score / 2));
                $scoreHtml .= "<img src='images/icons/widxlight.gif' style='vertical-align:middle;' height=8 width=$tempScore alt='$altText'>";
                $row[5] = $scoreHtml;
            }
            else if($this->reportID === CP_NOV09_WIDX_DEFAULT)
            {
                //doesn't have size or score columns
                //1. summary 2. desc 3. url (optional) 4. updated
                $row[3] = date_str(DATEFMT_SHORT, $row[4]); //overwrite size
                unset($row[5]); //get rid of score
                if($row[6])
                {
                    //if there's a url, swap it with updated
                    $temp = $row[3];
                    $row[3] = $row[6];
                    $row[4] = $temp;
                    unset($row[6]);
                }
            }
        }
    }

    /**
     * Gets the sort by type for external document searching
     * @return string
     * @param $sortBy int The current sort by value
     */
    protected function getExternalDocumentSortByType($sortBy)
    {
        switch ($sortBy)
        {
            case WIDX_SCORE_SORT:
                return HTSEARCH_SORT_SCORE_STR;
            case WIDX_TIME_SORT:
                return HTSEARCH_SORT_REV_TIME_STR;
            case WIDX_TITLE_SORT:
                return HTSEARCH_SORT_TITLE_STR;
            case WIDX_REV_TIME_SORT:
                return HTSEARCH_SORT_TIME_STR;
            case WIDX_REV_TITLE_SORT:
                return HTSEARCH_SORT_REV_TITLE_STR;
            default:
                return HTSEARCH_SORT_SCORE_STR;
        }
    }

    /**
     * Gets the search by type for external document searching
     * @param $searchBy int The current search by type ID
     * @return int
     */
    protected function getExternalDocumentSearchByType($searchBy)
    {
        switch ($searchBy)
        {
            case WIDX_ANY_SEARCH:
                return 0x0004;
            case WIDX_ALL_SEARCH:
                return 0x0001;
            case WIDX_COMPLEX_SEARCH:
                return 0x0004|0x0040|0x0008;
            default:
                return 0x0004|0x0040|0x0008;
        }
    }

    /**
    * returns correctly highlighted and truncated string
    * @param $answerID int
    * @return string
    */
    function getHighlightingFromAnswerID($answerID)
    {
        $text = '';
        $maximumTextLength = 1024; // this is the same as the webindexer
        $options = OPT_VAR_EXPAND | OPT_STRIP_HTML_TAGS | OPT_REF_TO_URL_PREVIEW | OPT_HIGHLIGHT_KEYWORDS | OPT_COND_SECT_FILTER;

        $si = sql_prepare(sprintf('SELECT a.solution FROM answers a WHERE a.a_id=%d', $answerID));

        sql_bind_col($si, 1, BIND_NTS, 4000); define(ans_soln, 0);

        if ($answerRow = sql_fetch($si))
        {
            $text = print_text2str($answerRow[ans_soln], $options);
            $text = utf8_trunc_nchars($text, $maximumTextLength);
        }
        sql_free($si);

        return $text;
    }


/*****************************************************************************************
 * standard report functions
 *****************************************************************************************/

      /*
     * Gets the report headers and view definition.
     * @param $showHiddenColumns bool
     * @return headers array
     **/
    protected function getHeaders($showHiddenColumns)
    {   $columns = $this->viewDefinition['all_cols'];
        $cacheKey = "reportVisHeaders$this->reportID";
        $headers = checkCache($cacheKey);
        if ($headers !== null)
        {
            return $headers;
        }

        $columnID = 0;
        $headers = array();
        foreach ($columns as $column)
        {
            if ($column['visible'] || $showHiddenColumns)
            {
                $header = array(
                    'heading' => $column['heading'],
                    'width' => $column['width'],
                    'data_type' => $column['data_type'],
                    'col_id' => $columnID + 1,  //used for sorting
                    'order' => $column['order'],
                );
                if ($column['url_info'] && $column['url_info']['url'] )
                {
                    $header['url_info'] = $column['url_info']['url'];
                }

                if ($showHiddenColumns)
                {
                    $answerAlias = $this->answerTableAlias;
                    if ($column['col_definition'] === "$answerAlias.summary") {
                        $header['col_alias'] = 'summary';
                    }
                    else if ($column['col_definition'] === "$answerAlias.updated") {
                        $header['col_alias'] = 'updated';
                    }
                    else if ($column['col_definition'] === "$answerAlias.solved") {
                        $header['col_alias'] = 'score';
                    }
                }

                $headers[] = $header;
            }
            $columnID++;
        }
        setCache($cacheKey, $headers);
        return $headers;
    }

    /**
     * converts the filters array into a format used by the views engine
     * @return array
     */
    protected function convertCPFiltersToQueryArguments()
    {
        $numberPerPage = $this->getNumberPerPage(intval($this->viewDefinition['num_per_page']));
        $pageNumber = (isset($this->appliedFilters['page'])) ? intval($this->appliedFilters['page']) : 1;
        $rowStart = (isset($this->appliedFilters['start_index'])) ? intval($this->appliedFilters['start_index']) : intval(($pageNumber - 1) * $numberPerPage);
        $rowStart = (intval($rowStart) >= 0) ? intval($rowStart) : INT_NULL;
        return array(
            'search_args' => $this->filtersToSearchArgs(),
            'sort_args' => $this->filtersToSortArgs(),
            'limit_args' => array(
                'row_limit' => $numberPerPage,
                'row_start' => $rowStart,
            ),
            'count_args' => array(
                'get_row_count' => 1,
                'get_node_leaf_count' => 1,
            ),
        );
    }

    /**
     * Gets report data and view definition information from views engine and builds it
     * up into the correct structure
     * @param $showHiddenColumns bool
     * @return array The report data in the correct format
     */
    protected function getReportData($showHiddenColumns)
    {
        $this->viewDefinition = view_get_grid_info($this->reportID, null);
        $queryArguments = $this->convertCPFiltersToQueryArguments();
        $cacheKey = "getReportData$this->reportID" . serialize($queryArguments);
        if (null !== ($cachedResult = checkCache($cacheKey)))
        {
            $this->returnData = $cachedResult;
            return;
        }
        if (IS_DEVELOPMENT && $this->isAnswerListReportWithoutSpecialSettingsFilter($this->reportID))
        {
            addDevelopmentHeaderWarning(sprintf(getMessage(RPT_ID_PCT_D_INCLUDES_ANS_TB_PCT_S_MSG), $this->reportID, self::answersSpecialSettingsFilterName, self::answersSpecialSettingsFilterName));
        }
        $viewData = view_get_query_cp($this->reportID, $queryArguments);
        $this->viewDataColumnDefinition = $viewData['columns'];
        $exceptions = $this->getViewExceptions($viewData['view_handle']);
        $numberPerPage = $this->getNumberPerPage(intval($this->viewDefinition['num_per_page']));
        $this->setMaxResultsBasedOnSearchLimiting($this->viewDefinition['num_per_page']);
        $dataArray = $this->getViewResults($viewData['view_handle'], min($numberPerPage, ($this->returnData['max_results']) ? $this->returnData['max_results'] : 0x7fffffff));
        $numberThisPage = count($dataArray);
        $pageNumber = (isset($this->appliedFilters['page'])) ? intval($this->appliedFilters['page']) : 1;
        $pageNumber = (intval($pageNumber) <= 0) ? 1 : $pageNumber;
        $rowCount = ($this->returnData['max_results'] > 0) ? $this->returnData['max_results'] : $viewData['row_count'];
        $this->returnData['headers'] = $this->getHeaders($showHiddenColumns);
        $this->returnData['total_num'] = ($numberPerPage > 0) ? $viewData['row_count'] : 0;
        $this->returnData['start_num'] = ($this->returnData['total_num'] > 0) ? ($numberPerPage * ($pageNumber - 1) + 1) : 0;
        $this->returnData['per_page'] = ($viewData['row_count'] < $numberThisPage) ? $rowCount : $numberThisPage;
        $this->returnData['total_pages'] = ($numberPerPage > 0) ? ceil($rowCount / $numberPerPage) : 0;
        $this->returnData['end_num'] = ($this->returnData['total_num'] > 0) ? ($this->returnData['start_num'] + $numberThisPage - 1) : 0;
        $this->returnData['search_term'] = $this->appliedFilters['keyword']->filters->data;
        if ($this->returnData['total_num'] <= $numberThisPage)
        {
            $this->returnData['truncated'] = 0;
        }
        $this->returnData['row_num'] = $this->viewDefinition['row_num'];
        $this->returnData['grouped'] = $this->viewDefinition['grouped'];
        $this->returnData['data'] = $dataArray;
        $this->returnData['exceptions'] = $exceptions;
        $this->returnData['page'] = intval($pageNumber);
        $this->checkValidPageNumberRequest();
        setCache($cacheKey, $this->returnData);
        $this->viewCleanup($viewData['view_handle']);
    }

    /**
     * @private
     * Call view_cleanup to free view resources including memory and SQL handles.
     * This function should be called after retrieving all the view data.
     * @param $vhandle int - handle of view to clean up
     * @return none (since php_view_cleanup returns nothing)
     */
    protected function viewCleanup($vhandle)
    {
        view_cleanup($vhandle);
    }

    /**
     * fetches the results
     * @param $vhandle int
     * @param $maxResults int - the max number to get
     * @return array
     */
    protected function getViewResults($vhandle, $maxResults)
    {
        $dataArray = array();
        while ((count($dataArray) < $maxResults) && ($row = view_fetch($vhandle)))
        {
            array_push($dataArray, $row);
        }
        return $dataArray;
    }

    /**
     * gets the exceptions out of the data column definition
     * these are used to add color to 'new' and 'updated'
     * @param $vhandle
     * @return array
     */
    protected function getViewExceptions($vhandle)
    {
        $exceptions = array();
        if ($this->viewDataColumnDefinition)
        {
            foreach ($this->viewDataColumnDefinition as $column)
            {
                view_bind_col($vhandle, $column['bind_pos'], $column['bind_type'], $column['bind_size']+1);
                if ($column['type'] & VIEW_CTYPE_EXCEPTION)
                {
                    array_push($exceptions, $column['bind_pos'] - 1);
                }
            }
        }
        return $exceptions;
    }

    /**
    * get the number_per_page
    * order is
    * 1 - per_page attribute in widget
    * 2 - user profile set from the setFilters function
    * 3 - report
    * 4 - default of 15
    * $this->filters['per_page'] and $this->filters['sitemap'] should be set prior to this
    */
    protected function getNumberPerPage($reportPerPageSetting)
    {
        if (isset($this->appliedFilters['per_page']) && $this->appliedFilters['per_page'])
        {
            return ($this->appliedFilters['per_page'] < 0) ? 0 : intval($this->appliedFilters['per_page']);
        }
        return $reportPerPageSetting ? $reportPerPageSetting : 15;
    }

    /**
     * sets the max results based on the search limiting config
     * @param $reportPerPageSetting
     * @return unknown_type
     */
    protected function setMaxResultsBasedOnSearchLimiting($reportPerPageSetting)
    {
        $searchResultLimitingStyle = getConfig(SEARCH_RESULT_LIMITING);
        if (!$this->appliedFilters['no_truncate'] && !($this->appliedFilters['page'] > 1) && $searchResultLimitingStyle &&
            $this->appliedFilters['keyword']->filters->data && !$this->CI->session->getProfileData('lines_per_page'))
        {
            $this->returnData['truncated'] = 1;
            $reportPerPageSetting = ($reportPerPageSetting) ? $reportPerPageSetting : 15;
            if ($searchResultLimitingStyle === 1)
            {
                $this->returnData['max_results'] = (9 * $reportPerPageSetting) / 10;
            }
            elseif ($searchResultLimitingStyle === 2)
            {
                $this->returnData['max_results'] = (5 * $reportPerPageSetting) / 10;
            }
            elseif ($searchResultLimitingStyle >= 3)
            {
                $this->returnData['max_results'] = (2 * $reportPerPageSetting) / 10;
            }
            else
            {
                $this->returnData['max_results'] = 0;
            }
        }
    }

    /**
     * formats all the data with html links and appropriate date, currency formatting
     *
     * @param $formatAsHtml bool if true html links are added for column links and exceptions tags are added
     *
     **/
    protected function formatViewsData($formatAsHtml = true)
    {
        $formattedDataCacheKey = "getFormattedData$this->reportID" . crc32(serialize($this->appliedFormats) . serialize($this->returnData));
        $formattedAidCacheKey = "getFormattedAid$this->reportID" . crc32(serialize($this->appliedFormats) . serialize($this->returnData));

        if (null !== ($cachedResult = checkCache($formattedDataCacheKey)))
        {
            if (null !== ($aidResult = checkCache($formattedAidCacheKey)))
            {
                $this->answerIDList = $aidResult;
            }
            $this->returnData['data'] = $cachedResult;
            return;
        }
        $columnExceptionList = $this->setExceptionTags($this->viewDefinition['exceptions'], $this->returnData['exceptions']);
        $dataSize = count($this->returnData['data']);
        for ($i = 0; $i < $dataSize; $i++)
        {
            $icon = '';
            $row = $this->returnData['data'][$i];
            $columnCount = count($this->viewDataColumnDefinition);
            $count = 0;
            $answersIDListIsUpdated = false;
            for ($j = 0; $j < $columnCount; $j++)
            {
                $columnDefinition = $this->viewDefinition['all_cols']['field'.$j]['col_definition'];
                if($columnDefinition === ($this->answerTableAlias.'.a_id') && !$answersIDListIsUpdated){
                    $this->answerIDList[] = $row[$j];
                    $answersIDListIsUpdated = true;
                }
                if($this->viewDataColumnDefinition['col_item'.$j]['val'] === "answers.url" && $row[$j] !== "" && $formatAsHtml)
                    $icon = getIcon($row[$j]);

                if ( (isset($this->viewDataColumnDefinition['col_item'.$j]['hidden']) &&  ($this->viewDataColumnDefinition['col_item'.$j]['hidden'] === 0)) || !$formatAsHtml)
                {
                    $bindType = $this->viewDataColumnDefinition['col_item'.$j]['bind_type'];

                    if ($bindType == BIND_MEMO)
                    {
                        //expand answer tags if the column definition is either answer.solution or answer.description
                        if ($columnDefinition === "{$this->answerTableAlias}.solution" || $columnDefinition === "{$this->answerTableAlias}.description" || $this->reportIsTypeExternalDocument)
                        {
                            $temp[$count] = expandAnswerTags($row[$j]);
                            //The truncate_size attribute only applies to answer.solution and answer.description
                            if($this->appliedFormats['truncate_size'] > 0)
                                $temp[$count] = truncateText($temp[$count], $this->appliedFormats['truncate_size'], true, $this->appliedFormats['max_wordbreak_trunc']);
                        }
                        else
                        {
                            $temp[$count] = $row[$j];
                        }
                    }
                    else if ($bindType == BIND_NTS)
                    {
                        $temp[$count] = print_text2str($row[$j], OPT_VAR_EXPAND|OPT_ESCAPE_SCRIPT|OPT_ESCAPE_HTML);
                    }
                    //else if ($bindType == BIND_DATE || $bindType == BIND_DTTM) // <-- original code doesn't break-out DTTM independently
                    else if ($bindType == BIND_DATE)
                    {
                        if ($this->appliedFormats['raw_date'])
                            $temp[$count] = $row[$j];
                        else
                        {
                            if(!is_null($row[$j]))
                                $temp[$count] = date_str(DATEFMT_SHORT, $row[$j]);
                            else
                                $temp[$count] = "";
                        }
                    }
                    // start NEW SECTION - added to target DTTM fields so we can restore sorting (broken when using custom formatting)
                    else if ($bindType == BIND_DTTM)
                    {
                        if ($this->appliedFormats['raw_date'])
                            $temp[$count] = $row[$j];
                        else
                        {
                            if(!is_null($row[$j]))
                                $temp[$count] = str_replace(array(' /  ', ' / '), '/', date_str(DATEFMT_DTTM_FLD, $row[$j]));
                            else
                                $temp[$count] = "";
                        }
                    }
                    // end NEW SECTION - added to target DTTM fields so we can restore sorting (broken when using custom formatting)
                    else if ($bindType == BIND_CURRENCY )
                    {
                        $temp[$count] = currency_str($row[$j]->currency_id, $row[$j]->value);
                    }
                    else
                    {
                        $temp[$count] = $row[$j];
                    }

                    if($formatAsHtml)
                    {
                        // add highlighting to non-numeric columns
                        if(($this->appliedFormats['highlight'] || $this->appliedFormats['emphasisHighlight']) && ($bindType !== BIND_INT))
                        {
                            $searchTermArray = explode(' ', $this->returnData['search_term']);
                            if(count($searchTermArray))
                            {
                                $text = $temp[$count];
                                if ($this->appliedFormats['emphasisHighlight'])
                                    $text = emphasizeText($text, array('query'=>$this->returnData['search_term']));
                                else
                                    $text = highlightTextHelper($text, $this->returnData['search_term'], $this->appliedFormats['highlightLength']);
                                $temp[$count] = $text;
                            }
                        }

                        // add exceptions
                        if (in_array($j + 1, $columnExceptionList))
                        {
                            foreach ($this->viewDefinition['exceptions'] as $k => $v)
                            {
                                if ($row[$this->viewDefinition['exceptions'][$k]['data_col']] > 0 && $this->viewDefinition['exceptions'][$k]['col_id'] - 1 == $j)
                                {
                                    $temp[$count] = $this->viewDefinition['exceptions'][$k]['start_tag'].$temp[$count].$this->viewDefinition['exceptions'][$k]['end_tag'];
                                    break;
                                }
                            }
                        }
                    }

                    //add links
                    $url = $this->viewDefinition['all_cols']['field'.$j]['url_info']['url'];
                    $target = $this->viewDefinition['all_cols']['field'.$j]['url_info']['target'];
                    if ($url != "")
                    {
                        $url = $this->replaceColumnLinks($url, $row, $this->appliedFormats['urlParms']);

                        if ($this->appliedFormats['no_session'])
                            $str = '<a href="'.$url.'" ';
                        else
                            $str = '<a href="'.$url.sessionParm() .'"';

                        if ($target != "")
                        {
                            $target = $this->replaceColumnLinks($target, $row);
                            $str .= ' target="'.$target.'" ';
                        }
                        if ($this->appliedFormats['tabindex'])
                        {
                            $str .= ' tabindex="'.$this->appliedFormats['tabindex'].$i.'" ';
                        }

                        $str .= '>';
                        $str .= $temp[$count].'</a>';
                        $temp[$count] = $str;
                    }
                    $count++;
                }
            }
            if($icon && $this->viewDataColumnDefinition['col_item0']['bind_type'] !== BIND_INT)
                $temp[0] = "{$icon} {$temp[0]}";

            $dataArray[$i] = $temp;
        }
        if (count($dataArray))
        {
            $this->returnData['data'] = $dataArray;
        }
        setCache($formattedDataCacheKey, $this->returnData['data']);
        setCache($formattedAidCacheKey, $this->answerIDList);
        return;
    }

    /**
     *
     * @param $exceptions - array of exceptions
     * @param $dataFields array
     * adds array elements of start tag and end tag which can be used as inline styles
     *
     */
    protected function setExceptionTags(&$exceptions, $dataFields)
    {
        $exCount = 0;
        $colList = array();
        $exceptions = (is_array($exceptions)) ? $exceptions : array();
        foreach($exceptions as $key => $value)
        {
            $colList[$exCount] = $value['col_id'];
            $exceptions[$key]['data_col'] = $dataFields[$exCount++];
            $arr = $this->xmlToArray($value['xml_data']);
            foreach($arr as $k => $v)
            {
                if ($v['name'] == "Style")
                {
                    $elems = $v['elements'];
                    $start = "";
                    $end = "";
                    foreach ($elems as $type => $style)
                    {
                        if ($style['name'] == "ForeColorString")
                        {
                            $color = $style['text'];
                            if (strlen($color) == 8)
                            {
                                $color = substr($color, 2);
                            }
                            $start.= "color:#".$color;
                        }
                    }
                    if ($start != "")
                    {
                        $start = '<span style="'.$start.'">';
                        $end = "</span>";
                    }
                    $exceptions[$key]['start_tag'] = $start;
                    $exceptions[$key]['end_tag'] = $end;
                }
            }
        }
        return $colList;
    }

    /**
     * changes an xml structure into an array
     * @param $xml String
     * @return Array
     */
    protected function xmlToArray($xml)
    {
        $xmlArray = array();
        $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
        $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';
        preg_match_all($reels, $xml, $elements);
        foreach ($elements[1] as $key => $value) {
            $xmlArray[$key]['name'] = $value;
            if ($attributes = trim($elements[2][$key])) {
                preg_match_all($reattrs, $attributes, $attributeArray);
                foreach ($attributeArray[1] as $nestedKey => $nestedValue)
                    $xmlArray[$key]['attributes'][$nestedValue] = $attributeArray[2][$nestedKey];
            }

            $endPosition = strpos($elements[3][$key], '<');
            if ($endPosition > 0)
                $xmlArray[$key]['text'] = substr($elements[3][$key], 0, $endPosition - 1);

            if (preg_match($reels, $elements[3][$key]))
                $xmlArray[$key]['elements'] = $this->xmlToArray($elements[3][$key]);
            else if ($elements[3][$key])
                $xmlArray[$key]['text'] = $elements[3][$key];
        }
        return $xmlArray;
    }

    /**
     * if filter is of type VDT_INT, non-integer parts of the value will be removed and an error message added
     * @param $searchTypeName
     * @param $keywordValue
     * @return string the possibly modified $keywordValue
     */
    protected function cleanKeywordValue($searchTypeName, $keywordValue)
    {
        if ($keywordValue === "")
            return $keywordValue;

        $runtimeFilters = $this->getRuntimeFilters($this->reportID);
        foreach($runtimeFilters as $runtimeFilter)
        {
            if ($searchTypeName === $runtimeFilter['fltr_id'])
                $dataType = $runtimeFilter['data_type'];
        }

        if ($dataType === VDT_INT)
        {
            if (stringContains($keywordValue, ";"))
            {
                $valuesToTest = explode(";", $keywordValue);
                $valuesToUse = array();
                foreach($valuesToTest as $valueToTest)
                {
                    if ($valueToTest === "")
                        continue;
                    $valueToTestInt = intval($valueToTest, 10);
                    // only accept a value that parses exactly
                    if (strval($valueToTestInt) === $valueToTest)
                        array_push($valuesToUse, $valueToTest);
                    else
                        $this->returnData['error'] = sprintf(getMessage(VAL_PCT_S_PCT_S_INT_KEYWORD_MSG), $valueToTest, $keywordValue);
                }
                $keywordValue = implode(";", $valuesToUse);
            }
            else
            {
                $keywordValueInt = intval($keywordValue, 10);
                // only accept a value that parses exactly
                if (strval($keywordValueInt) !== $keywordValue)
                {
                    $this->returnData['error'] = sprintf(getMessage(VAL_PCT_S_INT_KEYWORD_SEARCHING_CHG_MSG), $keywordValue);
                    $keywordValue = "";
                }
            }
        }

        return $keywordValue;
    }

    /**
     * Takes an array of named search elements
     * and converts it into the appropriate search arg array
     *
     * returns an array of search args in an array for views engine
     */
    protected function filtersToSearchArgs()
    {
        $searchArgs = array();
        if (isset($this->appliedFilters['search']) && ($this->appliedFilters['search'] === '0'  || $this->appliedFilters['search'] === 0) && !$this->appliedFilters['no_truncate'])
        {
            return $searchArgs;
        }

        $keywordValue = "";
        $seenKeyword = false;
        $seenSearchType = false;
        $searchTypeName = "";
        $searchTypeOperator = "";
        $contactData = null;
        $count = 0;
        foreach($this->appliedFilters as $key => $value)
        {
            if (isset($value->filters->rnSearchType)) // these are search filters
            {
                // map to new events
                if (isset($value->filters->data->fltr_id))
                    $value->filters->fltr_id = $value->filters->data->fltr_id;
                if (isset($value->filters->data->oper_id))
                    $value->filters->oper_id = $value->filters->data->oper_id;
                if (isset($value->filters->data->val))
                    $value->filters->data = $value->filters->data->val;

                // handle keyword term
                if ($key === 'keyword')
                {
                    $seenKeyword = true;
                    $keywordValue = $value->filters->data;
                    $this->returnData['search_term'] = $keywordValue;
                    if($searchTypeName && $keywordValue)
                    {
                        $this->returnData['search'] = true;
                    }
                    if ($seenKeyword && $seenSearchType)
                    {
                        $keywordValue = $this->cleanKeywordValue($searchTypeName, $keywordValue);
                        $searchArgs['search_field' . $count++] = $this->toFilterArray($searchTypeName, intval($searchTypeOperator), $keywordValue);
                    }
                }
                // handle search types
                else if ($key === 'searchType')
                {
                    $seenSearchType = true;
                    $searchTypeName = $value->filters->fltr_id;
                    $searchTypeOperator = $value->filters->oper_id;
                    $this->returnData['search_type'] = $value->filters->fltr_id;
                    if($searchTypeName && $keywordValue)
                    {
                        $this->returnData['search'] = true;
                    }
                    if ($seenKeyword && $seenSearchType)
                    {
                        $keywordValue = $this->cleanKeywordValue($searchTypeName, $keywordValue);
                        $searchArgs['search_field' . $count++] = $this->toFilterArray($searchTypeName, intval($searchTypeOperator), $keywordValue);
                    }
                }
                else if ($key === 'org')
                {
                    if($value->filters->fltr_id)
                    {
                        $contactData = true;
                        $searchArgs['search_field' . $count++] = $this->toFilterArray(strval($value->filters->fltr_id),
                            intval($value->filters->oper_id),
                            strval($value->filters->val) ? $value->filters->val : $value->filters->data);
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    $vals = "";
                    $values = $value->filters->data;
                    if (count($values))
                    {
                        if (!is_array($values))
                        {
                            $values = array($values);
                        }

                        foreach($values as $k => $v)
                        {
                            if ($value->filters->rnSearchType === 'menufilter')
                            {
                                $size = count($v);
                                if (is_array($v) && $v[$size-1] && $v[$size-1] > 0)
                                {
                                    $vals .= $size.".".$v[$size-1].";";
                                }
                                else if (is_array($v))
                                {
                                    for ($i = $size-1; $i >=0; $i--)
                                    {
                                        if ($v[$i] != null && $v[$i] != "")
                                        {
                                            $vals .= ($i + 1).".".$v[$i].";";
                                            break;
                                        }
                                    }
                                }
                                else if (is_string($v))
                                {
                                    $temp = explode(',', $v);
                                    $s = count($temp);
                                    $last = 0;
                                    $num = 0;
                                    for ($i = 0; $i < $s; $i++)
                                    {
                                        if ($temp[$i])
                                        {
                                            $last = $temp[$i];
                                            $num = $i + 1;
                                        }
                                    }
                                    if ($last > 0)
                                    {
                                        $vals .= $num.".".$last.";";
                                    }
                                }
                                else if ($v)
                                {
                                    foreach ($v as $node => $data)
                                    {
                                        if ($node == '0')
                                        {
                                            if(is_string($data))
                                                $data = explode(',', $data);
                                            $s = count($data);
                                            $last = $num = 0;
                                            for ($i = 0; $i < $s; $i++)
                                            {
                                                if ($data[$i])
                                                {
                                                    $last = $data[$i];
                                                    $num = $i + 1;
                                                }
                                            }
                                            if ($last > 0)
                                            {
                                                $vals .= $num.".".$last.";";
                                            }
                                        }
                                    }
                                }
                                // error check for bad data
                                $temp = explode('.', $vals);
                                if (!intval($temp[0])  || !intval($temp[1]))
                                    $vals = NULL;
                            }
                            else
                            {
                                $vals = ($v->fltr_id || $v->oper_id || $v->val) ? $v->val : $v;
                            }
                        }
                        if ($vals)
                        {
                            $searchArgs['search_field' . $count++] = $this->toFilterArray($value->filters->fltr_id, $value->filters->oper_id, $vals);
                        }
                        else
                        {
                            $searchArgs['search_field' . $count++] = $this->toFilterArray($value->filters->fltr_id, $value->filters->oper_id, ANY_FILTER_VALUE);
                        }
                    }
                    else
                    {
                        $searchArgs['search_field' . $count++] = $this->toFilterArray($value->filters->fltr_id, $value->filters->oper_id, ANY_FILTER_VALUE);
                    }
                }
            }
        }
        //remove contact search filter
        //$searchArgs = $this->addContactInformation($contactData, $searchArgs);
        //echo"<pre>";print_r($searchArgs);echo"</pre>";exit;
        return $searchArgs;
    }

    /**
     * adds contact information to the search args
     * @param $contactDataSet
     * @param $searchArgs
     * @return array the searchArgs modified array
     */
    protected function addContactInformation($contactDataSet, $searchArgs)
    {
        $sessionData = $this->CI->session->getProfile();
        $contactID = ($sessionData->c_id->value) ? $sessionData->c_id->value :  0;
        if ($this->incidentTableAlias && !$contactDataSet)
        {
            // set contact
            $searchArgs['search_field' . $count++] = $this->toFilterArray($this->incidentTableAlias.'.c_id', OPER_EQ, ''.$contactID);
        }

        $answerNotificationTableAlias = view_tbl2alias($this->reportID, TBL_ANS_NOTIF);
        if ($answerNotificationTableAlias)
        {
            $searchArgs['search_field' . $count++] = $this->toFilterArray('ans_notif.interface_id', OPER_EQ, intf_id().'');
            $searchArgs['search_field' . $count++] = $this->toFilterArray($answerNotificationTableAlias.'.c_id', OPER_EQ, ''.$contactID);
        }

        $slaTableAlias = view_tbl2alias($this->reportID, TBL_SLA_INSTANCES);
        if ($slaTableAlias)
        {
            // set contact
            if($sessionData->org_id->value != INT_NULL)
            {
                $searchArgs['search_field' . $count++] = $this->toFilterArray($slaTableAlias.'.owner_id', OPER_EQ, ''.$sessionData->org_id->value);
                $searchArgs['search_field' . $count++] = $this->toFilterArray($slaTableAlias.'.owner_tbl', OPER_EQ, ''.TBL_ORGS);
            }
            else
            {
                $searchArgs['search_field' . $count++] = $this->toFilterArray($slaTableAlias.'.owner_id', OPER_EQ, ''.$contactID);
                $searchArgs['search_field' . $count++] = $this->toFilterArray($slaTableAlias.'.owner_tbl', OPER_EQ, ''.TBL_CONTACTS);
            }
        }
        return $searchArgs;
    }

    /**
     *
     * @return array
     * @param $name Object
     * @param $oper Object
     * @param $val Object
     */
    protected function toFilterArray($name, $oper, $val)
    {
        if(strlen(trim($val)) === 0)
            $val = null;
        return array(
            'name' => $name,
            'oper' => $oper,
            'val' => $val,
        );
    }

    /**
     * @return array
     */
    protected function filtersToSortArgs()
    {
        if (!isset($this->appliedFilters['sort_args']) || !$this->appliedFilters['sort_args'])
            return null;
        if(is_array($this->appliedFilters['sort_args'])) // from php controller
            if (isset($this->appliedFilters['sort_args']['filters']['sort_field0']))
                return $this->appliedFilters['sort_args']['filters'];
            else
                return array('sort_field0' => $this->appliedFilters['sort_args']['filters']);
        $sortArgs = $this->appliedFilters['sort_args']->filters;  // from javascript
        if ($sortArgs->data)
            $sortFilters = $sortArgs->data;
        else
            $sortFilters = $sortArgs;
        return array(
            'sort_field0' => array(
                'col_id' => intval($sortFilters->col_id),
                'sort_direction' => intval($sortFilters->sort_direction),
                'sort_order' => 1,
            )
        );
    }

    /**
    * returns true if the search filter is type complex
    * @param $searchType int
    */
    protected function getComplex($searchType)
    {
        if (!$searchType)
        {
            return false;
        }
        $filter = $this->getFilterById($this->reportID, $searchType);
        return (stringContains($filter['expression1'], 'search_cpx'));
    }

    /**
    * Sets the cluster tree filter object in the $this->appliedFilters array to the cluster ID of the best match found by the topic browse model.
    * @return Boolean T if the report is joined on cluster_tree2answers, F if the report is not joined.
    */
    protected function preProcessClusterTreeFilter()
    {
        if(view_tbl2alias($this->reportID, TBL_CLUSTER2ANSWERS))
        {
            if($this->appliedFilters['keyword']->filters->data && !$this->appliedFilters['parent']->filters->data->val)
            {
                //check for a best match cluster ID only if there's search terms and there's no cluster tree parent ID already being passed in
                $this->CI->load->model('standard/Topicbrowse_model');
                $bestClusterID = $this->CI->Topicbrowse_model->getBestMatchClusterID($this->appliedFilters['keyword']->filters->data);
                if($bestClusterID)
                {
                    $filter = $this->getFilterByName($this->reportID, 'cluster_tree2answers.parent_id');
                    $this->appliedFilters['parent'] = $this->createSearchFilter($this->reportID, 'parent', $filter['fltr_id'], $bestClusterID, 'topicBrowse', $filter['oper_id']);
                }
            }
            $this->reportIsJoinedOnClusterTree = true;
        }
    }

    /**
     * gets the table name aliased for answers table
     * @param $reportNumber int
     * @return string or null
     */
    public function getAnswerAlias($reportNumber)
    {
        if ($this->isReportWidx($reportNumber))
            return null;
        $cacheKey = 'getAnswerAlias' . $reportNumber;
        $alias = checkCache($cacheKey);
        if ($alias !== null)
            return $alias;

        $alias = view_tbl2alias($reportNumber, TBL_ANSWERS);
        setCache($cacheKey, $alias);
        return $alias;
    }

    /**
     * gets the table name aliased for incidents table
     * @param $reportNumber
     * @return string or null
     */
    public function getIncidentAlias($reportNumber)
    {
        if ($this->isReportWidx($reportNumber))
            return null;
        $cacheKey = 'getIncidentAlias' . $reportNumber;
        $alias = checkCache($cacheKey);
        if ($alias !== null)
            return $alias;
        $alias = view_tbl2alias($reportNumber, TBL_INCIDENTS);
        setCache($cacheKey, $alias);
        return $alias;
    }

    /**
     * returns if the report number is widx and the configs are set correctly
     * @param $reportNumber int
     * @return bool
     */
    public function isReportWidx($reportNumber)
    {
        return ($this->isReportNumberWidx($reportNumber) && (getConfig(WIDX_MODE) !== 0));
    }

     /**
     * returns if the report number is widx
     * @param $reportNumber int
     * @return bool
     */
    protected function isReportNumberWidx($reportNumber)
    {
        return ((intval($reportNumber) === CP_WIDX_REPORT_DEFAULT || intval($reportNumber) === CP_NOV09_WIDX_DEFAULT) );
    }

/*****************************************************************************************
 * hook report functions
 *****************************************************************************************/

    /*
     * pre_report_get hook function to apply filter for org and custom_menu incident.c$bank_statuses
     */
    public function applyReportFilter($args)
    {
        // compAgainstOrg.Organization filter id: 9
        $sessionData = $this->CI->session->getProfile();
        $c_id = $sessionData->c_id->value;
        $org_id = $sessionData->org_id->value;

        /* 2013.03.18 (T. Woodham): Splitting this up into two switch statements. This one will enforce the contacts assigned organization for Company Portal.
         * 2013.10.29 (T. Woodham): Adding Congressional Portal assigned orgranization filter.
         */
        switch($args['data']['reportId'])
        {
          case getSetting('EXPORT_JOBS_REPORT_ID'):
            $args['data']['filters']['Contact']->filters->fltr_id = 1;
            $args['data']['filters']['Contact']->filters->oper_id = OPER_EQ;
            $args['data']['filters']['Contact']->filters->rnSearchType = 'custom';
            $args['data']['filters']['Contact']->filters->data = $c_id;
           break;
          case getSetting('CASE_ACTIVE_REPORT_ID'):
          case getSetting('CASE_REVIEW_REPORT_ID'):
          case getSetting('CASE_ARCHIVE_REPORT_ID'):

            $args['data']['filters']['custom_int1']->filters->fltr_id = 'CO$ComplaintAgainstOrg.Organization';
            $args['data']['filters']['custom_int1']->filters->oper_id = OPER_EQ;
            $args['data']['filters']['custom_int1']->filters->rnSearchType = 'custom';
            $args['data']['filters']['custom_int1']->filters->data = $org_id;

            break;
          case getSetting('CONGRESSIONAL_PORTAL_CASE_ACTIVE_REPORT_ID'):
          case getSetting('CONGRESSIONAL_PORTAL_CASE_CLOSED_REPORT_ID'):

            $args['data']['filters']['custom_int1']->filters->fltr_id = 'Portal$InboundReferral.Organization';
            $args['data']['filters']['custom_int1']->filters->oper_id = OPER_EQ;
            $args['data']['filters']['custom_int1']->filters->rnSearchType = 'custom';
            $args['data']['filters']['custom_int1']->filters->data = $org_id;

          break;

          default: break;
        }

        /* 2013.03.18 (T. Woodham)
         *
         * Second switch statement to enforce product filtering in both Company and Government Portal.
         */
        switch( $args['data']['reportId'] )
        {
            // Company Portal views.
            case getSetting('CASE_ACTIVE_REPORT_ID'):
            case getSetting('CASE_REVIEW_REPORT_ID'):
            case getSetting('CASE_ARCHIVE_REPORT_ID'):
            // Government Portal views.
            case getSetting('GOVERNMENT_PORTAL_CASE_ACTIVE_REPORT_ID'):
            case getSetting('GOVERNMENT_PORTAL_CASE_CLOSED_REPORT_ID'):
            // Data exports.
            case getSetting('CASE_EXPORT_REPORT_ID'):
            case getSetting('GOVERNMENT_PORTAL_ACTIVE_CASE_EXPORT_REPORT_ID'):
            case getSetting('GOVERNMENT_PORTAL_CLOSED_CASE_EXPORT_REPORT_ID'):
			// Congressional Portal views
			case getSetting('CONGRESSIONAL_PORTAL_CASE_ACTIVE_REPORT_ID'):
			case getSetting('CONGRESSIONAL_PORTAL_CASE_CLOSED_REPORT_ID'):

                // filter on product based on contact custom field mapping (c$product_role) to the product they have permission on
                $prodArray = $this->_getProductRole($c_id);

                // What's the filter name? The data export report uses a filter named 'prod', whereas CP search reports use a filter named 'p'.
                $searchFilterName = isset( $args['data']['filters']['p'] ) ? 'p' : 'prod';

                $searchFilters = isset( $args['data']['filters'][$searchFilterName]->filters->data->{0} ) ? $args['data']['filters'][$searchFilterName]->filters->data->{0} : $args['data']['filters'][$searchFilterName]->filters->data->val;
                // capture product in url parameter if any
                $prodUrl = getUrlParm('p');
                $searchArray = ($prodUrl) ? explode(",", $prodUrl) : $searchFilters;
                $searchArrayCount = count($searchArray);

                // if a product search is applied, then we need to filter which assigned products to show
                // note: search can only filter on 1 product hierachy i.e. {4,177}
                if (!empty($prodArray[0]) && !empty($searchArray[0]))
                {

                    // loop through each assigned product hierarchy (could be multiple levels of product hierarchies)
                    // i.e. {4,177}, {1221,1222}
                    $prodArrayCount = count($prodArray);
                    for ($o=0; $o < $prodArrayCount; $o++)
                    {
                        unset($dispProd);
                        $currentProd = explode(",", $prodArray[$o]);
                        $currentProdCount = count($currentProd);
                        //echo"currentProd<pre>";print_r($currentProd);echo"</pre>";

                        // if assigned and search prod level count matches,
                        // then all level much match otherwise display no results
                        if ($currentProdCount == $searchArrayCount)
                        {
                            for ($i=0; $i < $currentProdCount; $i++)
                            {
                                //echo"$currentProd[$i] == $searchArray[$i]\n";
                                if ($currentProd[$i] == $searchArray[$i])
                                {
                                    $dispProd[$i] = $currentProd[$i];
                                    $reconstructData->{$i} = array('level' => $i + 1,
                                                             'label' => '',
                                                             'hierList' => implode(",", $dispProd));
                                    if ($i == $currentProdCount - 1)
                                    {
                                        $args['data']['filters'][$searchFilterName]->filters->data->{0} = $dispProd;
                                        $args['data']['filters'][$searchFilterName]->filters->data->reconstructData = $reconstructData;
                                        return; // we matched to the end so we done
                                    }
                                }
                                else
                                    break;
                            }
                        }
                        else if ($currentProdCount > $searchArrayCount)
                        {
                            for ($i=0; $i < $currentProdCount; $i++)
                            {
                                if ($currentProd[$i] == $searchArray[$i] || $searchArray[$i] == null)
                                {
                                    $dispProd[$i] = $currentProd[$i];
                                    $reconstructData->{$i} = array('level' => $i + 1,
                                                             'label' => '',
                                                             'hierList' => implode(",", $dispProd));
                                    if ($i == $currentProdCount - 1)
                                    {
                                        $args['data']['filters'][$searchFilterName]->filters->data->{0} = $dispProd;
                                        $args['data']['filters'][$searchFilterName]->filters->data->reconstructData = $reconstructData;
                                        return;
                                    }
                                }
                                else
                                    break;
                            }
                        }
                        else if ($currentProdCount < $searchArrayCount)
                        {
                            for ($i=0; $i < $searchArrayCount; $i++)
                            {
                                if ($currentProd[$i] == $searchArray[$i] || $currentProd[$i] == null)
                                {
                                    $dispProd[$i] = $searchArray[$i];
                                    $reconstructData->{$i} = array('level' => $i + 1,
                                                             'label' => '',
                                                             'hierList' => implode(",", $dispProd));
                                    if ($i == $searchArrayCount - 1)
                                    {
                                        $args['data']['filters'][$searchFilterName]->filters->data->{0} = $dispProd;
                                        $args['data']['filters'][$searchFilterName]->filters->data->reconstructData = $reconstructData;
                                        return;
                                    }
                                }
                                else
                                    break;
                            }
                        }
                    }
                    // if we got here, then nothing was matched so display nothing by assigning to org id of 0 (org does not exist)
                    $args['data']['filters']['custom_int1']->filters->data = '00';
                    // For government portal exports, also set the consumer state filter to a bogus value to make sure nothing is exported.
                    if( isset( $args['data']['filters']['consumer_state'] ) )
                    {
                        $args['data']['filters']['consumer_state']->filters->data->val = 'ZZZ';
                    }
                    return;
                }
                else if (!empty($prodArray[0]) && empty($currentFilters[0]))
                {
                    if( isset( $args['data']['filters'][$searchFilterName] ) )
                    {
                        $args['data']['filters'][$searchFilterName]->filters->data = $prodArray;
                    }
                    else
                    {
                        $args['data']['filters']['custom_prod1']->filters->fltr_id = 'incidents.prod_hierarchy';
                        $args['data']['filters']['custom_prod1']->filters->oper_id = OPER_LIST;
                        $args['data']['filters']['custom_prod1']->filters->rnSearchType = 'menufilter';
                        $args['data']['filters']['custom_prod1']->filters->data = $prodArray;
                    }
                    return ;
                }

            break;
            default: break;
        }
    }

    /*
     * private function to get the contact product role that filters only the products assigned
     */
    private function _getProductRole($c_id)
    {
        try {
            $contact = RNCPHP\Contact::fetch($c_id);
        } catch (RNCPHP\ConnectAPIError $err) {
            $msg = "Error Generated ::" . $err -> getCode() . "::" . $err -> getMessage();
            die($msg);
        }

        return explode(';', $contact->CustomFields->product_role);
    }
}
