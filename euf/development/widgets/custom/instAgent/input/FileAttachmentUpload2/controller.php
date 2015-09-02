<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class FileAttachmentUpload2 extends Widget
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['label_input'] = new Attribute(getMessage(INPUT_LABEL_LBL), 'STRING', getMessage(LABEL_DISPLAY_INPUT_CONTROL_LBL), getMessage(ATTACH_DOCUMENTS_LBL));
        $this->attrs['label_remove'] = new Attribute(getMessage(REMOVE_ATTACHMENT_LABEL_CMD), 'STRING', getMessage(LABEL_DISPLAY_LINK_REMOVE_ATTACH_LBL), getMessage(REMOVE_CMD));
        $this->attrs['max_attachments'] = new Attribute(getMessage(MAXIMUM_ATTACHMENTS_LBL), 'INT', getMessage(SPECIFIES_ATTACHMENTS_UPLOAD_SNGL_MSG), 0);
        $this->attrs['label_max_attachment_limit'] = new Attribute(getMessage(MAX_ATTACHMENT_LIMIT_LABEL_LBL), 'STRING', getMessage(ERR_MSG_DISP_REACHES_UPLOAD_LIMIT_LBL), getMessage(REACHD_LIMIT_FILES_UPLOADED_ADD_MSG));
        $this->attrs['label_generic_error'] = new Attribute(getMessage(GENERIC_ERROR_MESSAGE_LBL), 'STRING', getMessage(GENERIC_ERR_DISP_UNKNOWN_ERR_MSG), getMessage(FILE_SUCC_UPLOADED_FILE_PATH_FILE_MSG));
        $this->attrs['loading_icon_path'] = new Attribute(getMessage(LOADING_ICON_PATH_LBL), 'STRING', getMessage(FILE_PATH_IMG_DISP_SUBMITTING_FORM_LBL), 'images/indicator.gif');
        $this->attrs['restricted_file_extensions'] = new Attribute("Restricted File Extensions", 'STRING', "Comma-separated list of restricted file extensions.", 'exe');
        $this->attrs['label_invalid_file_error'] = new Attribute("Label Invalid File Error", 'STRING', "Message to display for an invalid file extension.", "Cannot upload this type of file");
        $this->attrs['table'] = new Attribute('Table name', 'STRING', 'Custom object table name', 'incidents');
    }

    function generateWidgetInformation()
    {
        $this->info['notes'] =  getMessage(WIDGET_ALLOWS_USERS_ATTACH_FILES_MSG);
        $this->parms['i_id'] = new UrlParam(getMessage(INCIDENT_ID_LBL), 'i_id', false, getMessage(INCIDENT_ID_GET_INFORMATION_LBL), 'i_id/7');
    }

    function getData()
    {
        //Check if incident already has max number of file attachments
        $compId = getUrlParm('comp_id');
        if ($compId) {
            $this->CI->load->model('custom/instagent_model');
            $this->data['js']['attachmentCount'] = $this->CI->instagent_model->getFileAttachmentCount($compId);
        }
    }
}

