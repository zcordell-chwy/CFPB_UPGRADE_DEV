<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class ResultInfo2 extends Widget
{
    function __construct()
    {
        parent::__construct();

        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_ID_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['per_page'] = new Attribute(getMessage(ITEMS_PER_PAGE_LBL), 'INT', getMessage(CONTROLS_RES_DISP_PG_OVERRIDDEN_MSG), 0);
        $this->attrs['label_suggestion'] = new Attribute(getMessage(LABEL_SUGGESTED_SEARCHES_LBL), 'STRING', getMessage(DISP_MSG_SUGG_SEARCHES_FND_EU_SUGG_MSG), getMessage(OTHER_SUGGESTED_SEARCHES_LBL));
        $this->attrs['label_dictionary'] = new Attribute(getMessage(LABEL_NOT_FOUND_IN_DICTIONARY_LBL), 'STRING', getMessage(DISP_MESSAGE_KEYWORDS_FOUND_DICT_MSG), getMessage(THIS_WORD_WAS_NOT_FOUND_MSG));
        $this->attrs['label_spell'] = new Attribute(getMessage(LABEL_SPELLING_LBL), 'STRING', getMessage(DISP_SPELLING_SUGG_EU_SRCH_TERM_MSG), getMessage(DID_YOU_MEAN_LBL));
        $this->attrs['label_no_results'] = new Attribute(getMessage(LABEL_NO_RESULTS_LBL), 'STRING', getMessage(DISPLAYS_WHEN_NO_RESULTS_ARE_FOUND_MSG), getMessage(NO_RESULTS_FOUND_MSG));
        $this->attrs['label_no_results_suggestions'] = new Attribute(getMessage(NO_RESULTS_SUGGESTIONS_LABEL_LBL), 'STRING', getMessage(DISPLAYS_WHEN_NO_RESULTS_ARE_FOUND_MSG), getMessage(SUGG_UL_THAN_LI_WORDS_SPELLED_MSG));
        $this->attrs['label_common'] = new Attribute(getMessage(LABEL_COMMON_LBL), 'STRING', getMessage(DISP_COMMON_STOPWORDS_EU_SRCH_TERM_MSG), getMessage(WORD_COMMON_EXCLUDED_SEARCH_MSG));
        $this->attrs['label_results'] = new Attribute(getMessage(SEARCH_RESULTS_LABEL_CMD), 'STRING', getMessage(DSP_RES_MSG_SRCH_PERFORMED_SRCH_LBL), getMessage(RES_B_PCT_D_S_B_THAN_B_PCT_D_S_B_LBL));
        $this->attrs['label_results_search_query'] = new Attribute(getMessage(SEARCH_RESULTS_WITH_QUERY_LABEL_CMD), 'STRING', getMessage(DISP_RES_MSG_SRCH_PERFORMED_SRCH_LBL), getMessage(RS_B_PCT_D_S_B_THAN_B_PCT_D_S_B_LBL));
        $this->attrs['add_params_to_url'] = new Attribute(getMessage(ADD_PRMS_TO_URL_CMD), 'STRING', getMessage(CMMA_SPRATED_L_URL_PARMS_LINKS_MSG), '');
        $this->attrs['display_results'] = new Attribute(getMessage(DISPLAY_RESULTS_CMD), 'BOOL', getMessage(DETERMINES_DISP_TOT_RES_LABEL_RES_LBL), true);
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] = getMessage(WIDGET_DISP_INFO_RES_RPT_RET_INFO_MSG);
    }

    function getData()
    {
        if($this->data['attrs']['add_params_to_url'])
        {
            $appendedParameters = explode(',', trim($this->data['attrs']['add_params_to_url']));
            foreach($appendedParameters as $key => $parameter)
            {
                if(trim($parameter) === 'kw')
                {
                    unset($appendedParameters[$key]);
                    break;
                }
            }
            $this->data['attrs']['add_params_to_url'] = (count($appendedParameters)) ? implode(',', $appendedParameters) : '';
            $this->data['appendedParameters'] = getUrlParametersFromList($this->data['attrs']['add_params_to_url']);
        }

        $this->CI->load->model('custom/Report_model2');
        if($this->data['attrs']['per_page'] > 0)
            $filters['per_page'] = $this->data['attrs']['per_page'];
        setFiltersFromUrl($this->data['attrs']['report_id'], $filters);
        $reportToken = createToken($this->data['attrs']['report_id']);
        $results = $this->CI->Report_model2->getDataHTML($this->data['attrs']['report_id'], $reportToken, $filters, $format);
        //hide elements with no info
        $this->data['suggestionClass'] = $this->data['spellClass'] = $this->data['noResultsClass'] = $this->data['resultClass'] = 'rn_Hidden';
        if(count($results['ss_data']))
        {
            $this->data['suggestionClass'] = '';
            $this->data['suggestionData'] = $results['ss_data'];
        }
        if($results['spelling'])
        {
            $this->data['spellClass'] = '';
            $this->data['spellData'] = $results['spelling'];
        }
        if($results['total_num'] === 0 && $results['search_term'] !== '' && !$results['topics'])
        {
            //display 'no results' message only if there was a search query and no results were found; don't display if there's topic tree results
            $this->data['noResultsClass'] = '';
        }
        elseif(!$results['truncated'])
        {
            $this->data['resultClass'] = '';
            $this->data['firstResult'] = $results['start_num'];
            $this->data['lastResult'] = $results['end_num'];
            $this->data['totalResults'] = $results['total_num'];
        }
        
        $this->data['js']['linkUrl'] = ((FACEBOOK_REQUEST) ? "/cx/facebook/" : "/app/") . "{$this->CI->page}/search/1/kw/";

        if($results['search_term'] !== null && $results['search_term'] !== '' && $results['search_term'] !== false)
        {
            $stopWords = $results['stopword'];
            $noDictWords = $results['not_dict'];
            $searchTerms = explode(' ', $results['search_term']);
            $this->data['searchQuery'] = array();

            //construct search results message for the searched-on terms
            foreach($searchTerms as $word)
            {
                //get rid of punctuation, whitespace
                $strippedWord = preg_replace('/\W/', '', $word);
                //a word in the search query was a stopword
                if($stopWords && $strippedWord && strstr($stopWords, $strippedWord) !== false)
                    $type = 'stop';
                //a word in the search query was a no_dict word
                elseif($noDictWords && $strippedWord && strstr($noDictWords, $strippedWord) !== false)
                    $type = 'notFound';
                //probably a valid search term
                else
                    $type = 'normal';
                $word = htmlspecialchars($word, ENT_QUOTES, 'UTF-8', false);
                array_push($this->data['searchQuery'], array('word' => $word, 'url' => urlencode(str_replace('&amp;', '&', $word)) . '/search/1', $type => true));
            }
        }
        //validate sprintf strings so that a horrid php error isn't output
        if(substr_count($this->data['attrs']['label_results'], '%d') > 3)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_ATTRIBUTE_3_PCT_D_ARGUMENTS_MSG), 'label_results'));
            return false;
        }
        elseif(substr_count($this->data['attrs']['label_results_search_query'], '%d') > 3)
        {
            echo $this->reportError(sprintf(getMessage(PCT_S_ATTRIBUTE_3_PCT_D_ARGUMENTS_MSG), 'label_results_search_query'));
            return false;
        }
    }
}
