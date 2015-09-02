<?php
/*
* Fattach_model->get function modified on 2013-11-08 to allow for
* incident permissions bypass when retrieving a file.  This is 
* required to allow congressional users access to files which
* there user does not have appropriate permissions for the incident
* james.a.greene@oracle.com
*
*/


class Fattach_model extends Model
{
    function __construct()
    {
        parent::__construct();
    }

     /**
     * Redefine functions from controller to whitelist in model
     */
    function ps_fas_enabled()
    {
        return fas_enabled();
    }
    function ps_fas_has_file($str)
    {
        return fas_has_file($str);
    }
    function ps_fas_get_filesize($str)
    {
        return fas_get_filesize($str);
    }
    function ps_fas_has_temp_file($str)
    {
        return fas_get_tmp_filesize($str);
    }
    function ps_fas_get($str)
    {
        return fas_get($str);
    }
    /*
    function ps_fas_has_file($str)
    {
        return fas_has_file($str);
    }
    */

     /**
     * Function to retrieve fattach information from database.
     *
     * @param $fileID int The id of the fattach to retrieve
     * @param $created int date created
     *
     * @return array Details about the file attachment
     */
    function get($fileID, $created, $bypassIncidentPermissionsCheck=FALSE)
    {
        if(!is_numeric($fileID))
            return false;
        $sql = "select localfname, userfname, content_type, tbl, sz, id, type, private from fattach where file_id = $fileID";
        if($created && (is_int($created) || ctype_digit($created)))
        {
            $validatingCreation = true;
            $sql .= sprintf(' and created = %s', time2db($created));
        }
        else
        {
            $validatingCreation = false;
            $sql .= sprintf(' and (private = 0 or private is null)');
        }

        $si = sql_prepare($sql);

        $i = 1;
        sql_bind_col($si, $i++, BIND_NTS, 197);
        sql_bind_col($si, $i++, BIND_NTS, 41);
        sql_bind_col($si, $i++, BIND_NTS, 61);
        sql_bind_col($si, $i++, BIND_INT, 0);
        sql_bind_col($si, $i++, BIND_INT, 0);
        sql_bind_col($si, $i++, BIND_INT, 0);
        sql_bind_col($si, $i++, BIND_INT, 0);
        sql_bind_col($si, $i++, BIND_INT, 0);
        $row = sql_fetch($si);
        sql_free($si);

        if(!$row)
            return false;

        list($localFileName, $userFileName, $contentType, $table, $fileSize, $id, $type, $private) = $row;

        //Minimal check to see if user is logged in before viewing non-answer file attachments.
        //Created time must also have been passed as well
        if(($table) && ($table !== TBL_ANSWERS) && ($table !== TBL_META_ANSWERS) && (!isLoggedIn() || !$validatingCreation))
        {
            if( $table == TBL_INCIDENTS && isLoggedIn() )
            {
                // 2014.05.19 (T. Woodham): There's an error somewhere in file submission. There are instances where the API thinks a file was created sometime 
                //                          in the past 1.5 months, whereas the DB thinks a file was created in 2012. We need to bypass this check if it's incidents and 
                //                          the user is logged in.
                ;
            }
            else
            {
                $this->throwError();
            }
        }
        else if ($table === TBL_INCIDENTS) 
        {
          if(TRUE !== $bypassIncidentPermissionsCheck){
            $CI = get_instance();
            $CI->load->model('custom/Incident_model');

            // Incident_model::get will usually redirect us off to the "access denied" page if 
            // the current user isn't allowed to access the requested incident
            $incident = $CI->Incident_model->get($id);

            if (!$incident) {
                $this->throwError();
            }
          }
        }
        else if($table === TBL_ANSWERS && $id)
        {
            $CI = get_instance();
            $CI->load->model('standard/Answer_model');
            $answerDetails = $CI->Answer_model->getAnsDetails($id);
            if($answerDetails === null || $answerDetails[2] != STATUS_TYPE_PUBLIC)
            {
                $this->throwError();
            }
        }
        else if(!$userFileName && !$table && stringContains($contentType, 'image'))
        {
            //only allow workflow/guided assistance image requests
            if(!$validatingCreation || $type !== FA_TYPE_WF_SCRIPT_IMAGE)
            {
                $this->throwError();
            }
            //client workflow images don't populate fattach tbl with userfname and tbl
            $userFileName = sql_get_str(sprintf("SELECT label FROM labels WHERE tbl=%d AND label_id=$fileID AND lang_id=%d", TBL_FATTACH, lang_id(LANG_DIR)), 241);
            //assert content_type is constructed like: image/png
            $userFileName .= '.' . substr($contentType, strrpos($contentType, '/') + 1);
        }

        $localFileName = fattach_full_path($localFileName);

        //IE needs a url-encoded filename to properly display unicode characters; all other browsers do not.
        //Raw encoded so that any whitespace chars in the filename aren't turned into plus chars.
        //Replace any encoded plus chars (they're the only encoded special chars that browsers display as still encoded) back to unencoded value.
        $userFileName = (stringContains($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? str_replace('%2B', '+', rawurlencode($userFileName)) : $userFileName;

        return array($localFileName, $userFileName, $contentType, $fileSize, $private);
    }

    /**
    * Sends a header pointing to the error page with an error id.
    * @param $errorID int (optional) The error id to pass to the error page;
    *       defaults to 4.
    */
    private function throwError($errorID = 4)
    {
        redirectToErrorPage($errorID, stringContainsCaseInsensitive($_SERVER['HTTP_REFERER'], '/cx/facebook'));
    }
}
