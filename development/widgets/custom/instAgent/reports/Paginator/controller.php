<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Paginator extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['report_id'] = new Attribute(getMessage(REPORT_ID_LBL), 'INT', getMessage(ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG), CP_NOV09_ANSWERS_DEFAULT);
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['per_page'] = new Attribute(getMessage(ITEMS_PER_PAGE_LBL), 'INT', getMessage(CONTROLS_RES_DISP_PG_OVERRIDDEN_MSG), 0);
        $this->attrs['maximum_page_links'] = new Attribute(getMessage(MAXIMUM_PAGE_LINKS_LBL), 'INT', getMessage(MAX_INDIVIDUAL_PG_LINKS_DISP_LBL), 6);
        $this->attrs['maximum_page_links']->min = 0;
        $this->attrs['forward_icon_path'] = new Attribute(getMessage(IMAGE_FOR_FORWARD_ARROW_LBL), 'STRING', getMessage(FILE_PATH_IMAGE_DISP_FORWARD_BTN_LBL), '');
        $this->attrs['back_icon_path'] = new Attribute(getMessage(IMAGE_FOR_BACK_ARROW_LBL), 'STRING', getMessage(FILE_PATH_IMAGE_DISPLAY_BUTTON_LBL), '');
        $this->attrs['label_page'] = new Attribute(getMessage(LABEL_PAGE_LBL), 'STRING', getMessage(DISP_PAGE_INDICATOR_HOVERS_PAGE_LBL), getMessage(PAGE_PCT_S_OF_PCT_S_LBL));
        $this->attrs['label_forward'] = new Attribute(getMessage(FORWARD_LABEL_CMD), 'STRING', getMessage(LABEL_FOR_THE_FORWARD_LINK_LBL), getMessage(NEXT_GT_WIN_G_HK));
        $this->attrs['label_back'] = new Attribute(getMessage(BACK_LABEL_CMD), 'STRING', getMessage(LABEL_FOR_THE_BACK_LINK_LBL), getMessage(LT_PREVIOUS_WIN_L_HK));
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  getMessage(WIDGET_DISP_PG_LINKS_NAVIGATE_PAGES_MSG);
        $this->parms['page'] = new UrlParam(getMessage(PAGE_LBL), 'page', false, getMessage(SETS_PAGE_PAGE_URL_PARAMETER_LBL), 'page/2');
    }

    function getData()
    {
        $this->CI->load->model('custom/Report_model2');

        if ($this->data['attrs']['per_page'] > 0)
            $filters['per_page'] = $this->data['attrs']['per_page'];
        setFiltersFromUrl($this->data['attrs']['report_id'], $filters);
        $reportToken = createToken($this->data['attrs']['report_id']);
        $results = $this->CI->Report_model2->getDataHTML($this->data['attrs']['report_id'], $reportToken, $filters, null);

        if(!$this->data['attrs']['maximum_page_links'])
        {
            $this->data['js']['startPage'] = $this->data['js']['endPage'] = $results['page'];
        }
        elseif($results['total_pages'] > $this->data['attrs']['maximum_page_links'])
        {
            //calculate how far the page links should be shifted based on the specified cutoff
            $split = round($this->data['attrs']['maximum_page_links'] / 2);
            if($results['page'] <= $split)
            {
                //selected page is halfway (or less) to max_pages, so just stop displaying
                //links at the specified cutoff
                $this->data['js']['startPage'] = 1;
                $this->data['js']['endPage'] = $this->data['attrs']['maximum_page_links'];
            }
            else
            {
                //selected page is is more than half of max_pages, so shift the window of page links
                //by difference between the current page and halfway point
                $offsetFromMiddle = $results['page'] - $split;
                $maxOffset = $offsetFromMiddle + $this->data['attrs']['maximum_page_links'];
                if($maxOffset <= $results['total_pages'])
                {
                    //the shifted window hasn't hit up against the maximum number of pages of the data set
                    $this->data['js']['startPage'] = 1 + $offsetFromMiddle;
                    $this->data['js']['endPage'] = $maxOffset;
                }
                else
                {
                    //the shifted window hit up against the maximum number of pages of the data set,
                    //so stop at the maximum number of pages
                    $this->data['js']['startPage'] = $results['total_pages'] - ($this->data['attrs']['maximum_page_links'] - 1);
                    $this->data['js']['endPage'] = $results['total_pages'];
                }
            }
        }
        else
        {
            $this->data['js']['startPage'] = 1;
            $this->data['js']['endPage'] = $results['total_pages'];
        }

        $this->data['totalPages'] = $results['total_pages'];

        $url = $this->CI->page;
        // we need to add incstatus to paginate on correct tab
        $incstatus = (getUrlParm('incstatus')) ? 'incstatus/' . getUrlParm('incstatus') . '/' : '';
        $this->data['js']['pageUrl'] = "/app/$url/" . $incstatus . "page/";
        $this->data['js']['currentPage'] = $results['page'];
        $this->data['js']['backPageUrl'] = $this->data['js']['pageUrl'] . (intval($this->data['js']['currentPage']) - 1);
        $this->data['js']['forwardPageUrl'] = $this->data['js']['pageUrl'] . (intval($this->data['js']['currentPage']) + 1);

        $this->data['hideWidgetClass'] = ($results['truncated'] || ($results['total_pages'] < 2)) ? 'rn_Hidden' : '';
        $forwardClass = ($this->data['attrs']['forward_img_path']) ? 'rn_ForwardImageLink' : '';
        $this->data['forwardClass'] = ($results['total_pages'] <= $this->data['js']['currentPage']) ? 'rn_Hidden' : $forwardClass;
        $backClass = ($this->data['attrs']['back_img_path']) ? 'rn_BackImageLink' : '';
        $this->data['backClass'] = ($this->data['js']['currentPage'] <= 1) ? 'rn_Hidden' : $backClass;
    }
}
