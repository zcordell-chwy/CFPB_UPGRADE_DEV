<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('DataDisplay'))
    requireWidgetController('custom/instAgent/output/DataDisplayIA');

class FileListDisplayIA extends DataDisplayIA
{
    function __construct()
    {
        parent::__construct();
        $this->attrs['display_file_size'] = new Attribute(getMessage(DISPLAY_FILE_SIZE_CMD), 'BOOL', getMessage(IF_TRUE_THE_FILE_SIZE_IS_DISPLAYED_MSG), true);
        unset($this->attrs['highlight']);
        $this->attrs['name'] = new Attribute(getMessage(NAME_LBL), 'STRING', getMessage(INDICATES_TB_FLD_DISP_ATTACHED_MSG), '');
    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
        $this->info['notes'] = getMessage(DISP_FILE_ATTACHMENTS_INC_ANS_MSG);
        unset($this->parms['kw']);
    }

    function getData()
    {
        if(parent::retrieveAndInitializeData() === false)
            return false;

        // Validate data type
        if ($this->field->data_type !== EUF_DT_FATTACH)
        {
            echo $this->reportError(getMessage(FILELISTDISPLAY_DISP_FILE_ATTACH_MSG));
            return false;
        }
        //Check for empty data.
        if (!$this->data['value'])
            return false;

        //Get standard file pattern
        $this->data['fpattern'] = trim(getConfig(EU_FA_NEW_WIN_TYPES, 'RNW_UI'));

        // Set up label-value justification
        $this->data['wrapClass'] = ($this->data['attrs']['left_justify']) ? ' rn_LeftJustify' : '';
    }
}
