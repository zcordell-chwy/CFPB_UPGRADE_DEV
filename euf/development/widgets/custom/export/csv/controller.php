<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class csv extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['report_id'] = new Attribute( getMessage( REPORT_ID_LBL ), 'INT', getMessage( ID_RPT_DISP_DATA_SEARCH_RESULTS_MSG ), CP_NOV09_ANSWERS_DEFAULT );
        $this->attrs['report_id']->min = 1;
        $this->attrs['report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['per_page'] = new Attribute( getMessage( ITEMS_PER_PAGE_LBL ), 'INT', getMessage( CONTROLS_RES_DISP_PG_OVERRIDDEN_MSG ), 0 );
        $this->attrs['controller_location'] = new Attribute( 'Controller Location', 'STRING', "Location of the controller used to download files. Defaults to /cc/csvDataExport.", '/cc/csvDataExport' );
        $this->attrs['search_report_id'] = new Attribute( 'Search Report ID', 'INT', "The ID of the search report used on this CP page.", CP_NOV09_ANSWERS_DEFAULT );
        $this->attrs['search_report_id']->min = 1;
        $this->attrs['search_report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['update_export_pages'] = new Attribute( 'Update Export Pages', 'BOOL', "Boolean indicating the number of pages available in the user's data export should be dynamically updated along with their search activities. Defaults to false.", false );

        $this->CI->load->helper( 'config_helper' );
        $this->CI->load->helper( 'label_helper' );
        $this->CI->load->model( 'custom/ContactPermissions_model' );
    }

    function generateWidgetInformation()
    {
        //Create information to display in the tag gallery here
        $this->info['notes'] =  "This widget generates a list of links allowing the user to download incident details in CSV format. The user must be authorized to download files for links to show up.";
    }

    function getData()
    {
        $this->CI->load->model( 'custom/csvDataExport_model' );
        $organizationID = $this->CI->csvDataExport_model->getOrganizationIdForUser();
        $this->data['links'] = array();

        if( !$organizationID )
        {
            $this->data['js']['authorized'] = false;
        }
        else
        {
            $this->data['js']['authorized'] = true;
            $this->data['js']['links'][] = array( 'label' => 'Cases', 'link' => sprintf( '%s/incident', $this->data['attrs']['controller_location'] ), 'form_label' => 'SelectCases' );
            // $this->data['links'][] = array( 'label' => 'Communication History', 'link' => sprintf( '%s/incidentThreads', $this->data['attrs']['controller_location'] ) );
            $this->data['js']['links'][] = array( 'label' => 'File Attachments', 'link' => sprintf( '%s/incidentFileAttachments', $this->data['attrs']['controller_location'] ), 'form_label' => 'SelectAttachments' );

            // Adapted from the custom instAgent/reports/Paginator widget.
            $this->CI->load->model('custom/Report_model2');

            list( $initialFilters, $allFilters ) = $this->setSearchFilter( $organizationID );
            if ($this->data['attrs']['per_page'] > 0)
            {
                $initialFilters['per_page'] = $this->data['attrs']['per_page'];
                $allFilters['per_page'] = $this->data['attrs']['per_page'];
            }
            $reportToken = createToken($this->data['attrs']['report_id']);
            // logmessage( $allFilters );
            $results = $this->CI->Report_model2->getDataHTML($this->data['attrs']['report_id'], $reportToken, $initialFilters, null);
            $this->data['js']['defaultFilters'] = $allFilters;

            // logmessage( $results );

            $this->data['js']['startPage'] = 1;
            $this->data['js']['endPage'] = $results['total_pages'];

            $this->data['totalPages'] = $results['total_pages'];

            $this->data['hideWidgetClass'] = ($results['truncated'] || ($results['total_pages'] < 2)) ? 'rn_Hidden' : '';

            // List of valid search parameters.
            $this->data['js']['validSearchFilters'] = array(
                'c',
                'p',
                'consumer_state',
                'keyword',
                'searchType',
                'month_of_cases'
            );
        }
    }

    function setSearchFilter( $organizationID )
    {
        $filters = array();
        $initialFilters = array();
        // Do we have initial search filters to load?
        setFiltersFromUrl( $this->data['attrs']['search_report_id'], $filters );

        // Update the report ID so it's against the export report and not the search report.
        foreach( $filters as $key => $filter )
        {
            $filters[$key]->filters->report_id = $this->data['attrs']['report_id'];
        }

        $searchFilters = $this->CI->Report_model2->getRuntimeFilters($this->data['attrs']['report_id']);

        // createSearchFilter($reportNumber, $name, $filterID, $value, $rnSearchType = 'customName', $operatorID = null)
        switch( $this->CI->ContactPermissions_model->userType() )
        {
            case 'company':
                $filterInfo = $this->CI->Report_model2->getSearchFilterInfo($searchFilters, 'CO$ComplaintAgainstOrg.Organization');
                $filters[$filterInfo['name']] = $this->CI->Report_model2->createSearchFilter(
                    $this->data['attrs']['report_id'],
                    $filterInfo['name'],
                    $filterInfo['fltr_id'],
                    $organizationID
                );
                $initialFilters[$filterInfo['name']] = $filters[$filterInfo['name']];
                $this->data['js']['organizationID'] = $organizationID;
                break;
            case 'state':
                $filterInfo = $this->CI->Report_model2->getSearchFilterInfo($searchFilters, 'incidents.c$consumer_state');
                $filters[$filterInfo['name']] = $this->CI->Report_model2->createSearchFilter(
                    $this->data['attrs']['report_id'],
                    $filterInfo['name'],
                    $filterInfo['fltr_id'],
                    $this->CI->ContactPermissions_model->stateJurisdiction(),
                    'filterDropdown'
                );
                $initialFilters[$filterInfo['name']] = $filters[$filterInfo['name']];
                // $initialFilters['consumer_state']->filters->report_id = $this->data['attrs']['search_report_id'];
                $this->data['js']['consumer_state'] = $this->CI->ContactPermissions_model->stateJurisdiction();
                break;
            case 'federal':
                $filterInfo = $this->CI->Report_model2->getSearchFilterInfo($searchFilters, 'incidents.created');
                $filters[$filterInfo['name']] = $this->CI->Report_model2->createSearchFilter(
                    $this->data['attrs']['report_id'],
                    $filterInfo['name'],
                    $filterInfo['fltr_id'],
                    $filterInfo['default_value'],
                    'filterDropdown',
                    $filterInfo['oper_id']
                );
                break;
            default:
                break;
        }

        //echo"initialFilters<pre>";print_r($initialFilters);echo"</pre>";
        return array( $initialFilters, $filters );
    }
}
