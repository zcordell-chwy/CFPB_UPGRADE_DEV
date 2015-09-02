<?

class csvDataExport extends ControllerBase {
    //This is the constructor for the custom controller. Do not modify anything within
    //this function.
    function __construct()
    {
        parent::__construct();
        $this->load->model( 'custom/csvDataExport_model' );
        $this->load->model( 'custom/ContactPermissions_model' );
    }

    private function _getFileSuffix( $fileName )
    {
        // ExportIncidentIncident-340-----1-1510-09172012.csv
        // There can be a variable number of filters, but page, time, and date will always be the last three filters in the local file name.
        $token = explode( '-', $fileName );
        $numTokens = count( $token );
        $pageToken = $numTokens - 3;
        $timeToken = $numTokens - 2;
        $dateToken = $numTokens - 1;
        $page = $token[$pageToken];
        $dateTime = sprintf( '%s-%s', $token[$timeToken], $token[$dateToken] );
        $downloadFileSuffix = sprintf( "%s-%s", $page, $dateTime );

        // echo sprintf( "<pre>Num tokens: '%s', File name: '%s', Download file suffix: '%s'</pre>", $numTokens, $fileName, $downloadFileSuffix );
        return $downloadFileSuffix;
    }

    private function _initializeFilters()
    {
        foreach( array_keys( $_POST ) as $input )
        {
            // echo sprintf( "<pre>%s: %s</pre>", $input, $this->input->post( $input ) );
            $this->csvDataExport_model->addFilter( $input, $this->input->post( $input ) );
        }
    }

    function incident( $page, $number )
    {
        $organizationID = $this->getOrganizationID();
        $this->csvDataExport_model->initialize( $organizationID, 'Incident' );
        $this->_initializeFilters();
        $fileName = $this->csvDataExport_model->activeIncidents();

        //set headers to NOT cache a page
        // header( "Cache-Control: no-cache" ); //HTTP 1.1
        // header( "Pragma: no-cache" ); //HTTP 1.0
        header( sprintf( 'Content-disposition: attachment; filename=ExportAllCases-%s', $this->_getFileSuffix( $fileName ) ) );
        header( 'Content-type: text/csv' );
        readfile( $fileName );
    }

    function incidentThreads()
    {
        $pageNumber = json_decode( $this->input->post( 'page' ) );

        $organizationID = $this->getOrganizationID();
        $this->csvDataExport_model->initialize( $organizationID, 'CommunicationHistory', $pageNumber );
        $fileName = $this->csvDataExport_model->incidentThreads();

        //set headers to NOT cache a page
        header( "Cache-Control: no-store, no-cache" ); //HTTP 1.1
        header( "Pragma: no-cache" ); //HTTP 1.0
        header( sprintf( 'Content-disposition: attachment; filename=ExportCommunicationHistory-%s', $this->_getFileSuffix( $fileName ) ) );
        header( 'Content-type: text/csv' );
        readfile( $fileName );
    }

    function incidentFileAttachments()
    {
        $pageNumber = json_decode( $this->input->post( 'page' ) );

        $organizationID = $this->getOrganizationID();
        $this->csvDataExport_model->initialize( $organizationID, 'FileAttachments', $pageNumber );
        $this->_initializeFilters();
        $fileName = $this->csvDataExport_model->incidentFileAttachments();

        //set headers to NOT cache a page
        // header( "Cache-Control: no-store, no-cache" ); //HTTP 1.1
        // header( "Pragma: no-cache" ); //HTTP 1.0
        header( sprintf( 'Content-disposition: attachment; filename=ExportAllAttachments-%s', $this->_getFileSuffix( $fileName ) ) );
        header( 'Content-type: text/csv' );
        readfile( $fileName );
    }

    private function getOrganizationID()
    {
        $organizationID = $this->csvDataExport_model->getOrganizationIdForUser();
        if( $organizationID )
        {
            return $organizationID;
        }
        else
        {
            header("HTTP/1.1 403 Forbidden");
            exit(getMessage(ACCESS_DENIED_HDG));
        }
    }
}
