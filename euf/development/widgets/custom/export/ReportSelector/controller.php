<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class ReportSelector extends Widget
{
    function __construct()
    {
        parent::__construct();

        $this->attrs['search_report_id'] = new Attribute( 'Search Report ID', 'INT', "The ID of the search report used on this CP page.", CP_NOV09_ANSWERS_DEFAULT );
        $this->attrs['search_report_id']->min = 1;
        $this->attrs['search_report_id']->optlistId = OPTL_CURR_INTF_PUBLIC_REPORTS;
        $this->attrs['export_type'] = new Attribute( 'Export Type', 'STRING', "The type of exports that should be loaded. Must be a valid LookupName for DataExport\ExportType menu objects. Defaults to an empty string.", '' );

        $this->CI->load->helper( 'config_helper' );
        $this->CI->load->helper( 'label_helper' );
        $this->CI->load->model( 'custom/ContactPermissions_model' );
        $this->CI->load->model( 'custom/DataExportJobManager_model' );
    }

    function generateWidgetInformation()
    {
        //Create information to display in the tag gallery here
        $this->info['notes'] =  "This widget generates a list of links allowing the user to download incident details in CSV format. The user must be authorized to download files for links to show up.";
    }

    function getData()
    {

        $organizationID = $this->CI->DataExportJobManager_model->getOrganizationIdForUser();
        $this->data['js']['links'] = array();

        if( !$organizationID )
        {
            $this->data['js']['authorized'] = false;
        }
        else
        {
            // Logic for Govt Portal Only
            if(!strstr($this->data['attrs']['export_type'], 'Company Portal'))
            {
                // set the export type based on the contact permissions
                $this->CI->load->model('custom/contactpermissions_model');
                $contactID = $this->CI->contactpermissions_model->getProfileContactID();
                $userType = $this->CI->contactpermissions_model->userTypeByContactIDandOrganizationID($contactID, $organizationID);
                $exportType = ucfirst($userType) . ' ' . $this->data['attrs']['export_type'];
            }
            else
            {
                $exportType = $this->data['attrs']['export_type'];
            }

            $this->data['js']['authorized'] = true;
            $this->data['js']['organization'] = $organizationID;
            $this->data['js']['products'] = $this->CI->ContactPermissions_model->userProductAccess();
            $this->data['js']['links'] = $this->CI->DataExportJobManager_model->getAvailableDataExportArray( $exportType );
            $this->data['js']['job_status_url'] = getSetting( 'CASE_DATA_EXPORT_JOB_STATUS_URL' );
            $this->data['js']['export_kickoff_url'] = getSetting( 'CASE_DATA_EXPORT_JOB_KICKOFF_URL' );

            /*
             * 6/17/2013 (Thomas Woodham)
             *
             * If the export is not "featured", then it is aimed at product-specific data sets. However, there are a series of
             * service products available on company/government portal for which there is no webform. We need to catch them so that they
             * are not displayed to the user if they're product-limited. Also, we want to toggle them off if the user selects a product for
             * which the export isn't relevant. This setting captures those products which fit in this group.
             *
             * NOTE: This may need to be updated as new intake forms come online.
             *
             * Current catch-all service products (all in tier 1):
             *      Cash advance
             *      Check services
             *      Credit counseling / Debt management
             *      General
             *      Gift card
             *      Money Orders / Traveler's checks
             *      Other financial products
             *      Other Loan
             *      Prepaid/Stored value card
             *      Tax refund anticipation loan
             */
            $this->data['js']['catchall_product_ids'] = getSetting( 'CASE_DATA_EXPORT_CATCH_ALL_PRODUCT_IDS' );

            // Is the contact product-limited? If so, no sense in showing them something they can't access.
            if( isset( $this->data['js']['products'] ) && count( $this->data['js']['products'] ) > 0 )
            {
                $this->cullExportList();
            }

            // List of valid search parameters that could be captured..
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

    /**
     * Helper function to remove exports from the list if the user doesn't have access to them.
     */
    private function cullExportList()
    {
        // Get the product restrictions into a format we can more easily use.
        $userProductList = array();
        $culledExportList = array();

        foreach( $this->data['js']['products'] as $product )
        {
            $userProductList[] = $product[0];
        }

        foreach( $this->data['js']['links'] as $export )
        {
            if( $export['Featured'] )
            {
                // Featured exports, by definition, are not product specific...yet.
                $culledExportList[] = $export;
            }
            else if( isset( $export['Product'] ) && in_array( $export['Product'], $userProductList ) )
            {
                $culledExportList[] = $export;
            }
            else if( !isset( $export['Product'] ) )
            {
                $productDiff = array_diff( $this->data['js']['catchall_product_ids'], $userProductList );
                if( count( $productDiff ) < count( $this->data['js']['catchall_product_ids'] ) )
                {
                    $culledExportList[] = $export;
                }
            }
        }

        $this->data['js']['links'] = $culledExportList;
    }
}
