<?php

class Fattach extends ControllerBase
{
    const EMPTY_FILE_ERROR = 10;
    const GENERIC_ERROR = 2;

    function __construct()
    {
        parent::__construct();
        parent::_setClickstreamMapping(array(
            "get" => "attachment_view",
            "upload" => "attachement_upload"
        ));
    }

    /**
     * Redirects the user to the admin url for a file attached to an investigation
     *
     * @param $investigationID int ID of an investigation
     * @param $fileID int ID of a file attachment
     */
    function investigation( $investigationID, $fileID )
    {
        $this->load->model( 'custom/dr_model' );
        $fattachURL = $this->dr_model->getInvAttach( $fileID, $investigationID );
        if( $fattachURL !== false )
        {
            logmessage( $fattachURL );
            header( sprintf( "Location: %s", $fattachURL ) );
            exit();
        }
        else
        {
            // If no admin url returned, kick the usr to permission denied. 
            header( 'Location: /app/error/error_id/4' );
            exit();
        }
    }

    /**
     * Retrieves file attachment given an ID
     * @param $id int ID of the file attachment
     * @param $created String created timestamp (optional)
     */
    function get($id, $created = null)
    {
        logmessage( sprintf( "ID: %s, Created: %s", $id, $created ) );
        $fileExists = 0; // 1 - on fas, 2 - on disk, 3 - on fas as tmp file
        $this->load->model('custom/Fattach_model');
        $this->load->model('custom/Contactpermissions_model');
        $bypassPermissionsCheck = ('congressional'==$this->Contactpermissions_model->userType())?TRUE:FALSE;

        $fattach = $this->Fattach_model->get($id, $created, $bypassPermissionsCheck);

        if($fattach)
        {
            //logMessage($fattach);
            if(!is_readable($fattach[0]))
            {
                if (get_cfg_var('rnt.hosted'))
                {
                    if ($this->Fattach_model->ps_fas_enabled())
                    {
                        if ($this->Fattach_model->ps_fas_has_file($fattach[0]))
                        {
                            $fileExists = 1; // on fas
                            // Need to get the filesize from the fas since it doesn't
                            // exist on the local file disk.
                            if(!$fattach[3])
                            {
                                $fattach[3] = $this->Fattach_model->ps_fas_get_filesize($fattach[0]);
                            }
                        }
                        elseif ($this->Fattach_model->ps_fas_has_tmp_file($fattach[0]))
                        {
                            $fileExists = 3; // on fas but must be a tmp file
                            if(!$fattach[3])
                            {
                                $fattach[3] = $this->Fattach_model->ps_fas_get_tmp_filesize($fattach[0]);
                            }
                        }
                    }
                }
            }
            else
            {
                $fileExists = 2;
                // Since the file exists on the local disk we then need to
                // get the filesize from the disk.
                if(!$fattach[3])
                {
                    $fattach[3] = filesize($fattach[0]);
                }
            }

            if ($fileExists)
            {
                header(gmstrftime('Date: %a, %d %b %Y %H:%M:%S GMT'));
                header(sprintf('Server: RNW/%s', MOD_BUILD_VER));
                header('Accept-Ranges: none');

                //fix to get around IE and Excel 07's warning message, IE and Word 07's pop-under behavior
                //or maximum file size to open 'inline' is 20MB - QA 091226-000001
                //Also added a fix for QA 100413-000101 to force the Content-Disposition to 'attachment' for Android phones
                if (preg_match("/^[[:print:]]*[.]((xl)(.){0,2})|((doc)(.){0,1})$/", $fattach[1]) || ($fattach[3] > 20971520) || stringContainsCaseInsensitive($_SERVER['HTTP_USER_AGENT'], 'Android'))
                    $contentDisp = 'attachment';
                else
                    $contentDisp = 'inline';
                header('Content-Disposition: ' . $contentDisp . '; filename="' . $fattach[1] . '"');
                header('Cache-Control: max-age=2592000'); //thirty days
                if ($fattach[2])
                    header("Content-Type: $fattach[2]");

                if ($fileExists === 1)
                {
                    header(sprintf('Content-Length: %ld', $fattach[3]));
                    ob_end_flush();
                    $this->Fattach_model->ps_fas_get($fattach[0]);
                }
                elseif ($fileExists === 3)
                {
                    if (!$fattach[3])
                        $fattach[3] = fas_get_tmp_filesize($fattach[0]);
                    header(sprintf('Content-Length: %ld', $fattach[3]));
                    ob_end_flush();
                    fas_get_tmp_file($fattach[0]);
                }
                else
                {
                    header(gmstrftime('Last-Modified: %a, %d %b %Y %H:%M:%S GMT', filemtime($fattach[0])));
                    header(sprintf('Content-Length: %ld', $fattach[3]));
                    $attachmentFilePointer = fopen($fattach[0], 'r');
                    ob_end_flush();
                    while(!feof($attachmentFilePointer))
                    {
                        print(fread($attachmentFilePointer, 8192));
                    }
                    fclose($attachmentFilePointer);
                }
            }
            else
            {
                redirectToErrorPage(3, stringContainsCaseInsensitive($_SERVER['HTTP_REFERER'], '/cx/facebook'));
            }
        }
        else
        {
            redirectToErrorPage(4, stringContainsCaseInsensitive($_SERVER['HTTP_REFERER'], '/cx/facebook'));
        }
    }

    /**
     * Renames a temporary uploaded file and returns all
     * information about the file
     *
     * @return array Information about the uploaded file
     */
    function upload()
    {
        $fileInfo = $_FILES['file'];
        if($fileInfo['error'] === UPLOAD_ERR_NO_FILE)
        {
            $this->_uploadError(FILE_PATH_FOUND_MSG, UPLOAD_ERR_NO_FILE);
        }
        if($fileInfo['size'] === false || $fileInfo['size'] === null  || ($fileInfo['error'] > 0))
        {
            $this->_uploadError(getMessage(FILE_SUCC_UPLOADED_FILE_PATH_FILE_MSG), self::GENERIC_ERROR);
        }
        if($fileInfo['size'] === 0)
        {
            $this->_uploadError(null, self::EMPTY_FILE_ERROR);
        }
        if($fileInfo['size'] > getConfig(FATTACH_MAX_SIZE))
        {
            $this->_uploadError(getMessage(SORRY_FILE_TRYING_UPLOAD_MSG));
        }

        $temporaryName = $fileInfo['tmp_name']; 
        $temporaryName = basename(strval($temporaryName)); 
        $temporaryName = substr($temporaryName, 3) . $_SERVER['SERVER_NAME'];
        $newName = fattach_full_path($temporaryName); 

        @unlink($newName); 
        if(@rename($_FILES['file']['tmp_name'], $newName)) 
        { 
            chmod($newName, 0666); 
            fas_put_tmp_file($newName);
        }
        else
        {
            $this->_uploadError(getMessage(FILE_SUCC_UPLOADED_FILE_PATH_FILE_MSG), self::GENERIC_ERROR);
        }

        $fileInfo['tmp_name'] = $temporaryName;
        if(preg_match("@(<|&lt;).*(>|&gt;)@i", $fileInfo['name']))
            $fileInfo['name'] = strtr($fileInfo['name'], $this->rnw->getFileNameEscapeCharacters());

        // The name shouldn't have tag delimiters but it also
        // should not have single or double quotes in it to prevent
        // denial of service attacks.
        $fileInfo['name'] = strtr($fileInfo['name'], "\"'", "--");

        if(strlen($fileInfo['name']) > 100)
        {
            $this->_uploadError(getMessage(NAME_ATTACHED_FILE_100_CHARS_MSG));
        }
        if (!preg_match("@^[a-z0-9.-]+$@i", $fileInfo['tmp_name']))
        {
            $this->_uploadError(getMessage(FILE_SUCC_UPLOADED_FILE_PATH_FILE_MSG), self::GENERIC_ERROR);
        }
        echo json_encode($fileInfo);
    }

    /**
     * Renames a temporary uploaded file and returns all
     * information about the file. This function also
     * alerts the Chat Service of the uploaded file.
     *
     * @return array Information about the uploaded file
     */
    function uploadChat($engagementId, $chatSId)
    {
        // Get max file size; if this upload exceeds size, report error to chat service
        $maxFilesize = getConfig(FATTACH_MAX_SIZE);

        // Get Chat Service URL
        $chatServerHost = getConfig(SRV_CHAT_INT_HOST, 'RNL');
        if($chatServerHost === '')
        {
            $chatServerHost = getConfig(SRV_CHAT_HOST, 'RNL');
            $liveServerAndPath = (isRequestHttps() ? ('https://') : ('http://')) . $chatServerHost . '/Chat/chat/';
        }
        else
        {
            $liveServerAndPath = ('http://') . $chatServerHost . '/Chat/chat/';
        }

        $liveServerAndPath = 'http://' . $chatServerHost . '/Chat/chat/';
        $dbName = getConfig(DB_NAME, 'COMMON');
        $url = $liveServerAndPath . $dbName . '?action=FATTACH_UPLOAD';

        $postData = "&engagementId=$engagementId";
        $postData .= "&sessionId=$chatSId";

        $fileInfo = $_FILES['file'];
        if($fileInfo['error'] === UPLOAD_ERR_NO_FILE)
            $uploadFailure = array('error'=>UPLOAD_ERR_NO_FILE);
        elseif(!isset($fileInfo['size']))
            $uploadFailure = array('error' => 2);
        elseif($fileInfo['size'] == 0)
            $uploadFailure = array('error' => 10); // Magic number; these should be defined somewhere, probably. This means empty file was uploaded.

        if(!isset($uploadFailure))
        {
            $tempName = $fileInfo['tmp_name'];
            $tempName = basename(strval($tempName));
            $tempName = substr($tempName, 3) . $_SERVER['SERVER_NAME'];
            $newName = fattach_full_path($tempName);

            @unlink($newName);
            rename($_FILES['file']['tmp_name'], $newName);
            chmod($newName, 0666);
            fas_put_tmp_file($newName);

            $fileInfo['tmp_name'] = $tempName;
            if (preg_match("@(<|&lt;).*(>|&gt;)@i", $fileInfo['name']))
                $fileInfo['name'] = strtr($fileInfo['name'], $this->rnw->getFileNameEscapeCharacters());

            //The name shouldn't have tag delimiters but it also
            //should not have single or double quotes in it to prevent
            //denial of service attacks.

            //The name shouldn't have tag delimiters but it also
            //should not have single or double quotes in it to prevent
            //denial of service attacks.
            $fileInfo['name'] = strtr($fileInfo['name'], "\"'", "--");
            // -------------

            if(strlen($fileInfo['name']) > 100)
            {
                $uploadFailure = array('errorMessage' => getMessage(NAME_ATTACHED_FILE_100_CHARACTERS_MSG));
                $postData .= '&status=ERROR';
            }
            else if (!preg_match("@^[a-z0-9.-]+$@i", $fileInfo['tmp_name']))
            {
                $uploadFailure = array('error' => 2);
                $postData .= '&status=ERROR';
            }
            else
            {
                if($fileInfo['size'] <= $maxFilesize)
                {
                    $postData .= '&status=RECEIVED';
                    $postData .= '&localFName=' . urlencode($tempName);
                    $postData .= '&userFName=' . urlencode($fileInfo['name']);
                    $postData .= '&contentType=' . $fileInfo['type'];
                    $postData .= '&fileSize=' . $fileInfo['size'];
                }
                else
                {
                    $postData .= '&status=ERROR'; // max filesize exceeded; report error to chat service
                }
            }
        }
        else // error encountered; notify Chat Service and return json error
            $postData .= '&status=ERROR';

        $handle = fopen($url . $postData, 'r');

        if ($handle == FALSE)
        {
            print -1;
            exit;
        }

        $contents = '';
        while (!feof($handle))
            $contents .= fread($handle, 8192);

        fclose($handle);

        if(isset($uploadFailure))
            echo json_encode($uploadFailure);
        else
            echo json_encode($fileInfo);
    }
    
    /*
    * Echoes a JSON-encoded error object. May contain errorMessage or error keys.
    * @param $errorMessage String error message
    * @param $errorCode Int error code (optional)
    * @private
    */
    private function _uploadError($errorMessage, $errorCode = null)
    {
        $uploadFailure = array('errorMessage' => $errorMessage);
        if($errorCode)
        {
            $uploadFailure['error'] = $errorCode;
        }
        echo json_encode($uploadFailure);
        exit;
    }
}
