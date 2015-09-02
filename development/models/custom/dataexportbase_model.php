<?

use RightNow\Connect\v1 as RNCPHP;

class DataExportBase_model extends Model
{
    protected $fileType;
    protected $organizationID;
    protected $fileName;
    protected $timestamp;
    protected $dataType;
    protected $worksheetName;
    protected $fileLocation;
    protected $cacheExpiryTime;
    protected $contactID;

    // Report filters.
    protected $reportID;
    protected $pageNumber;
    protected $product;
    protected $category;
    protected $consumerState;
    protected $searchType;
    protected $keyword;
    protected $month_of_cases;
    protected $filterList;

    function __construct()
    {
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
	initConnectAPI();
        parent::__construct();

        $this->fileType = null;
        $this->organizationID = null;
        $this->fileName = null;
        $this->timestamp = time();
        $this->dataType = null;
        $this->worksheetName = null;
        $this->fileLocation = '/tmp/';
        $this->cacheExpiryTime = 3600; // 1 hour.
        $this->contactID = null;

        // Initialize report filter values.
        $this->reportID = null;
        $this->pageNumber = 1;
        $this->product = null;
        $this->category = null;
        $this->consumer_state = null;
        $this->searchType = null;
        $this->keyword = null;
        $this->month_of_cases = null;
        $this->filterList = array(
            'reportID',
            'product',
            'category',
            'consumer_state',
            'month_of_cases',
            'searchType',
            'keyword',
            'pageNumber'
        );
    }

    function initialize( $orgID, $worksheet, $pageNumber = 1 )
    {
        $this->organizationID = intval( $orgID );
        $this->worksheetName = $worksheet;
        $this->pageNumber = $pageNumber;
    }

    function addFilter( $filter, $value )
    {
        if( $filter === 'p' )
            $filter = 'product';
        if( $filter === 'c' )
            $filter = 'category';
        if( $filter === 'page' )
            $filter = 'pageNumber';

        if( in_array( $filter, $this->filterList ) )
        {
            $this->$filter = $value;
            // echo sprintf( "Adding %s filter.<br />", $filter );
        }
    }

    function cacheFileAvailable()
    {
        $fileDirectory = '/tmp';
        $fileNameSubstring = $this->_generateFileNamePrefix();
        $cacheFile = false;
        $filesExamined = 0;

        // Temporarily forcing this to always return a file.
        // return $cacheFile;

        if( $handle = opendir( $fileDirectory ) )
        {
            while( false !== ( $entry = readdir( $handle ) ) )
            {
                $filesExamined++;

                if( strpos( $entry, $fileNameSubstring ) !== false )
                {
                    // File was found. Is it new enough?
                    $age = filemtime( sprintf( '%s/%s', $fileDirectory, $entry ) ) + $this->cacheExpiryTime - $this->timestamp;
                    if( $age > 0 )
                    {
                        $cacheFile = sprintf( '%s/%s', $fileDirectory, $entry );
                        break;
                    }
                }
            }

            closedir( $handle );
        }
        else
        {
            throw new Exception( "Unable to open tmp directory." );
        }

        return $cacheFile;
    }

    private function _generateFileNamePrefix()
    {
        // Deprecated filename prefix 12/12/2012.
        // $filePrefix = sprintf( 'Export%s%s-%s-', $this->dataType, $this->worksheetName, $this->organizationID );
        $filePrefix = sprintf( 'Export%s-%s-%s-', $this->dataType, $this->organizationID, $this->contactID );
        foreach( $this->filterList as $filter )
        {
            if( isset( $this->$filter ) )
                $filePrefix = sprintf( '%s%s-', $filePrefix, $this->$filter );
        }

        return $filePrefix;
    }

    /**
     * Generates the file name for this export. Valid format is "Export<DataType><WorksheetName>-HHHH-MMDDYYYY".
     *
     * @return  String  The name of the file generated in this export.
     */
    function getFileName()
    {
        $fileTime = strftime( '%H%M-%m%d%Y', $this->timestamp );

        $fileName = sprintf(
            '%s%s%s.%s',
            $this->fileLocation,
            $this->_generateFileNamePrefix(),
            $fileTime,
            strtolower( $this->fileType )
        );

        return $fileName;
    }

    function writeDataRow( $fileHandle )
    {
        throw new Exception( "Not implemented." );
    }

    function getOrganizationIdForUser()
    {
        $CI = get_instance();
        $organizationID = $CI->session->getProfileData( 'org_id' );
        if( is_null( $organizationID ) || !is_numeric( $organizationID ) )
        {
            return false;
        }

        // Now that we know they're logged in and have an organization associated with them, are they authorized?
        try
        {
            $this->contactID = $CI->session->getProfileData( 'c_id' );
            $contact = RNCPHP\Contact::fetch( $this->contactID );
            if( $contact->CustomFields->download_export && $contact->CustomFields->is_inst_agent )
                return $organizationID;
        }
        catch( RNCPHP\ConnectApiErrorBase $err )
        {
            // Just silently continue. If we're not definitively sure they're authorized, we'll return false and not let them through.
            ;
        }

        return false;
    }
}

