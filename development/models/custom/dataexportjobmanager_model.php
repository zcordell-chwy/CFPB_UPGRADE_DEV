<?

use RightNow\Connect\v1_2 as RNCPHP;
use PS\Log\v1_1 as PSLog;

if( !defined( 'CUSTOM_SCRIPT' ) )
    define( "CUSTOM_SCRIPT", true );

class DataExportJobManager_model extends Model
{
    protected $contact;
    protected $organizationID;
    protected $job;

    function __construct()
    {
        parent::__construct();

        // CPHP initialization.
	require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI( 'priv_msg_addin', 'v7Sv1T0hj@cf' );

        // PSLog reusable tool initialization.
        require_once( "custom/src/oracle/libraries/PSLog-1.1.phph" );
        PSLog\Log::init( PSLog\Type::CP, "DataExportJobManagerModel" );

        // Initialize data members.
        $this->contact = null;
        $this->organizationID = null;
        $this->job = null;
    }

    /**
     * Function returning a 2D array of available data exports for the requested page.
     *
     * @param   $type   STRING  The NamedID LookupName for export types.
     *
     * @return  ARRAY   Ordered array of associative arrays. Associative array keys:
     *                   'LabelText' - the name of the export to be shown to users.
     *                   'Featured' - Boolean indicating whether or not the export is 'featured' and should be set apart from non-featured exports.
     *                   'ReportID' - The ID of the report responsible for generating downloads.
     *                   'Product' - The ID of the service product to which the export is associated.
     */
    public function getAvailableDataExportArray( $type )
    {
        $exports = array();

        if( strlen( $type ) > 0 )
        {
            $roql = sprintf( "SELECT Export.Labels.LabelText, Export.Featured, Export.ReportID, Export.Product
                              FROM DataExport.Export Export
                              WHERE Export.Type.LookupName = '%s' and Export.Labels.Language.ID = 1
                              ORDER BY Export.Featured DESC, Export.Labels.LabelText", strval( $type ) );
            try
            {
                $resultSet = RNCPHP\ROQL::query( $roql );
                while( $result = $resultSet->next() )
                {
                    while( $row = $result->next() )
                    {
                        $exports[] = $row;
                    }
                }
            }
            catch( RNCPHP\ConnectAPIErrorBase $err )
            {
                PSLog\Log::error( sprintf( "Query to generate array of available exports failed for '%s' type: %s", $type, $err->getMessage() ) );
            }
        }

        return $exports;
    }

    /**
     * Function that creates DataExport\Job and DataExport\JobComponent records requested by the user.
     *
     * @param   $reports    ARRAY   Array or reports requested by the logged-in contact.
     * @param   $filters    STRING  JSON-encoded string of report filters to be applied to JobComponents as they are run.
     *
     * @return              BOOL    Boolean true/false indicating success or failure of create operation.
     */
    public function createJob( $reports, $filters )
    {
        $success = true;
        if( isset( $this->contact ) && count( $reports ) > 0 )
        {
            try
            {
                $status = RNCPHP\DataExport\JobStatus::fetch( 'In Queue' );
                $this->job = new RNCPHP\DataExport\Job();
                $this->job->Contact = $this->contact;
                $this->job->Parameters = $filters;
                $this->job->Status = $status;
                $this->job->save();

                foreach( $reports as $report )
                {
                    $component = new RNCPHP\DataExport\JobComponent();
                    $component->Job = $this->job;
                    $component->Status = $status;
                    $component->Parameters = $filters;
                    $component->Export = RNCPHP\DataExport\Export::first( sprintf( "ReportID = %s", intval( $report ) ) );
                    $component->save();
                }
            }
            catch( RNCPHP\ConnectAPIErrorBase $err )
            {
                PSLog\Log::error( sprintf( "Failure creating new DataExport Job and its component records: %s", $err->getMessage() ) );

                $success = false;
                break;
            }

            if( $success === true )
            {
                try
                {
                    RNCPHP\ConnectAPI::commit();
                }
                catch( RNCPHP\ConnectAPIErrorBase $err )
                {
                    $success = false;
                    RNCPHP\ConnectAPI::rollback();
                    PSLog\Log::error( sprintf( "Failure committing new DataExport Job and its components: %s", $err->getMessage() ) );
                }
            }
        }

        return $success;
    }

    /**
     * Function to retrieve a Job instance by ID.
     *
     * @param   INT The ID of a Job instance.
     *
     * @return  OBJ The Job object identified by the ID. Will be null if no object found.
     */
    public function getJob( $jobId )
    {
        $job = null;

        try
        {
            $job = RNCPHP\DataExport\Job::fetch( intval( $jobId ) );
        }
        catch( RNCPHP\ConnectAPIErrorBase $err )
        {
            PSLog\Log::error( sprintf( "Unable to fetch Job by given ID (%s): %s", $jobId, $err->getMessage() ) );
        }

        return $job;
    }

    /**
     * Function to retrieve the ID of the current Job.
     *
     * @return  INT The Job ID. Will be null if no job has been run.
     */
    public function getJobId()
    {
        if( isset( $this->job ) )
        {
            return $this->job->ID;
        }
        else
        {
            return null;
        }
    }

    /**
     * Function to archive a Job instance if it is in a Finished status.
     *
     * @param   INT The ID of a Job instance.
     *
     * @return  OBJ The Job object identified by the ID. Will be null if no object found.
     */
    public function archiveJob( $jobId )
    {
        try
        {
            $job = RNCPHP\DataExport\Job::fetch( intval( $jobId ) );
            if( $job->Status->LookupName == 'Finished' )
            {
                $status = RNCPHP\DataExport\JobStatus::fetch( 'Archived' );
                $job->Status = $status;
                $job->save();
                RNCPHP\ConnectAPI::commit();
            }
        }
        catch( RNCPHP\ConnectAPIErrorBase $err )
        {
            RNCPHP\ConnectAPI::rollback();
            PSLog\Log::error( sprintf( "Unable to fetch and archive Job by given ID (%s): %s", $jobId, $err->getMessage() ) );
        }
    }

	/**
	 * Retrieves all attachments from Job objects. Borrowed from dr_model.
	 */
	public function getAttachments( $fattachList )
    {
        $resultObjs = array();

        try
        {
            foreach( $fattachList as $i => $fattach )
            {
                $url = $fattach->getAdminURL();
                $resultObjs[$i]['url'] = $url;
                $resultObjs[$i]['filename'] = $fattach->FileName;
                $resultObjs[$i]['type'] = $fattach->ContentType;
                $resultObjs[$i]['icon'] = getIcon($fattach->FileName);
                $resultObjs[$i]['size'] = getReadableFileSize($fattach->Size);
            }
        }
        catch (Exception $e)
        {
            // Silently continue. We'll return an empty array.
            ;
        }

        return $resultObjs;
	}

    /**
     * Function returning the ID of the currently logged-in Contact's affiliated Organization.
     *
     * @return  INT The ID of the affiliated Organization object. Will be false if there is no logged-in Contact, the Contact is
     *              not affiliated with any Organization, or if the Contact is not provisioned as a financial institution agent
     *              and cannot download an export.
     */
    public function getOrganizationIdForUser()
    {
        if( !isset( $this->contact ) || !isset( $this->organizationID ) )
        {
            $CI = get_instance();
            $this->organizationID = $CI->session->getProfileData( 'org_id' );
            if( is_null( $this->organizationID ) || !is_numeric( $this->organizationID ) )
            {
                return false;
            }

            $contactID = $CI->session->getProfileData( 'c_id' );
            $this->contact = RNCPHP\Contact::fetch( $contactID );
        }

        // Now that we know they're logged in and have an organization associated with them, are they authorized?
        try
        {
            if( $this->contact->CustomFields->c->download_export && $this->contact->CustomFields->c->is_inst_agent )
                return $this->organizationID;
        }
        catch( RNCPHP\ConnectApiErrorBase $err )
        {
            // Just silently continue. If we're not definitively sure they're authorized, we'll return false and not let them through.
            ;
        }

        return false;
    }

    /**
     * Function returing the number of finished jobs for the currently logged-in contact.
     *
     * @return  INT The number of finished jobs for the logged-in contact.
     */
    public function numberFinishedJobsForContact()
    {
        $numFinishedJobs = 0;

        // If we don't know the contact already, try to find it.
        if( !isset( $this->contact ) )
            $this->getOrganizationIdForUser();

        if( isset( $this->contact ) )
        {
            try
            {
                // Get a list of all jobs for this user that are finished and the user has not been notified.
                $jobList = RNCPHP\DataExport\Job::find( sprintf( "Contact = %d AND Status.LookupName = 'Finished'", $this->contact->ID ) );
                foreach( $jobList as $job )
                {
                    if( $job->UserNotified !== true )
                    {
                        $numFinishedJobs++;
                        $job->UserNotified = true;
                        $job->save();
                    }
                }

                RNCPHP\ConnectAPI::commit();
            }
            catch( RNCPHP\ConnectApiErrorBase $err )
            {
                // Errors in this case are not fatal, but we should log them for future reference.
                RNCPHP\ConnectAPI::rollback();
                PSLog\Log::warning( sprintf( "Unable to query finished jobs for a contact (%s): %s", $this->contact->ID, $err->getMessage() ) );
            }
        }

        return $numFinishedJobs;
    }
}

