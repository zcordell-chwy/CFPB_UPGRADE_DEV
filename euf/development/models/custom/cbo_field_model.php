<?php
use RightNow\Connect\v1_2 as RNCPHP;
use PS\Log\v1_1 as PSLog;

if (!defined('CUSTOM_SCRIPT'))
    define("CUSTOM_SCRIPT", true);

class CBO_Field_model extends Model
{
    function __construct()
    {
        require_once get_cfg_var("doc_root") . "/ConnectPHP/Connect_init.php";
        initConnectAPI();

        parent::__construct();

        $this->load->helper('config');
        $this->load->helper('debug');
        // has the "debug_log" function for easy debug output
        debug_log("constructor");

        // PSLog reusable tool initialization.
        $this->load->library("CustomLog");
        PSLog\Log::init(PSLog\Type::CP, "CBO_Field_model");
    }
	
	//store data for attachment created on the company portal (CR 437)
	public function checkAttachments($comp_id)
	{
		$CAO = RNCPHP\CO\ComplaintAgainstOrg::fetch($comp_id);
		$incident = RNCPHP\Incident::fetch($CAO->Incident->ID);
		if(isset($CAO->ID))
		{
			foreach($CAO->Incident->FileAttachments as $file)
			{
				//if file attachment was created in the last five seconds (meaning via this update)
				if(time() - $file->UpdatedTime < 5)
				{
					$count = $count+1;
					$incident->CustomFields->c->company_portal_fattach .= $CAO->Incident->CustomFields->c->company_portal_fattach.$file->ID.";";
					
				}
			}
			if($count > 0)
				$incident->save(RNCPHP\RNObject::SuppressAll);
		}
		return true;
	}


    /**
     * Generic function to handle form submission. Handles a CBO create/update
     *
     * @param $data array All submitted form data
     * @param $incidentID int Current Incident ID
     * @return int status of operation
     */
	 //OSG PS 140228-000012 added support for a complaint ID
    public function sendForm(&$data, $comp_id)
    {
        debug_log("Starting cbo_field_model sendForm");

        //debug_log($data, "sendForm DATA: ");
        // Test the security synchronization token to ensure that
        // it matches with the source side.  Return the error
        // condition indicating a token error.
        /*
         $formToken = $this->input->post('f_tok');
         if (isValidSecurityToken($formToken, 0) === false)
         {
         return array(
         'status' => '-1',
         'sessionParm' => sessionParm()
         );
         }
         */

        $cbo = null;
        $md = null;
        $saveFlag = false;

        foreach ($data as $key => $fieldObject)
        {
            $field_name = $fieldObject->name;

            // skip non-cbo fields (because we have the full set of submitted fields)
            if (!empty($fieldObject->table) && !empty($fieldObject->name) && !empty($fieldObject->is_cbo))
            {
                //debug_log( sprintf( "Table: '%s', Field: '%s', Value: '%s'", $fieldObject->table, $fieldObject->name, print_r( $fieldObject->value, true ) ) );
                if (is_null($cbo))
                {
					//OSG PS 140228-000012 added support for a complaint ID
                    $cbo = $this->getCBOInstance($fieldObject->namespace, $fieldObject->table, $comp_id);
                    $md = $cbo::getMetadata();
                }

                // skip fields that are empty, but only if they are not required
                if (empty($fieldObject->value) && $fieldObject->value !== 0 && $fieldObject->value !== false)
                {
                    // EG: Changed to ===  need to do a strict comparison else null becomes "No"
                    if ($md->$field_name->type_name == 'bool' && $fieldObject->value === '0')// sometimes this is boolean, others it's a string
                        {
                        $fieldObject->value = false;
                    }
                    else if (empty($fieldObject->value))
                    {
                        continue;
                    }
                }

                try
                {
                    // TODO: Is there some better way to figure this out? I don't see it in the metadata
                    // Need to handle select lists differently
                    if (stristr($md->$field_name->type_name, 'RightNow\\Connect\\v1_2'))
                    {
                        if ($md->$field_name->type_name == 'RightNow\Connect\v1_2\NamedIDLabel')
                        {
                            $menu_obj = new $md->$field_name->type_name();
                            $menu_obj->ID = $fieldObject->value;
                            $selected_menu_item = $menu_obj;
                        }
                        else
                        {
                            $menu_obj = $md->$field_name->type_name;
                            $selected_menu_item = $menu_obj::fetch($fieldObject->value);
                        }
                        if (!empty($selected_menu_item))
                        {
                            $cbo->{$field_name} = $selected_menu_item;
                        }
                        $saveFlag = true;
                        $status['message'] .= "{$field_name} value set to {$fieldObject->value}<br />";
                    }
                    else if ($md->$field_name->COM_type == "Date" || $md->$field_name->COM_type == "DateTime")
                        {
                            $dt = strtotime($fieldObject->value);
                            $cbo->{$field_name} = $dt;
                            $saveFlag = true;
                            $status['message'] .= "{$field_name} set to {$fieldObject->value} ($dt) <br />";
                        }
                    else
                    {
                        $cbo->{$field_name} = $fieldObject->value;
                        $saveFlag = true;
                        $status['message'] .= "{$field_name} set to {$fieldObject->value}<br />";
                    }
                    //$status['fields_updated']++;
                }
                catch (Exception $e)
                {
                    $status['message'] .= "Unable to set field [{$field_name}] to [{$fieldObject->value}] " . $e->getMessage() . "<br />";
                    $status['errors']++;
                    $status['status'] = -1;
                    $saveFlag = false;
                    debug_log($status, "!!! EXCEPTION !!! ");
                    PSLog\Log::error("Unable to set field [{$field_name}] to [{$fieldObject->value}] " . $e->getMessage());
                    RNCPHP\ConnectAPI::commit();
                }
            }
        }

        if (!is_null($cbo) && $saveFlag)
        {
            try
            {
                $cbo->save();
                RNCPHP\ConnectAPI::commit();
                $status['cbo_id'] = $cbo->ID;
                $status['status'] = 1;
            }
            catch (Exception $e)
            {
                $status['message'] .= "Unable to save object " . $e->getMessage() . "<br />";
                $status['errors']++;
                $saveFlag = false;
                $status['status'] = -1;
                debug_log($status, "!!! EXCEPTION !!! ");
                PSLog\Log::fatal("Line ({$e->getLine()}): Error saving CBO: {$e->getMessage()}");
                RNCPHP\ConnectAPI::commit();
            }
        }
        //Ensure CBO is null if no save happened.
        else {
            $cbo = null;
        }
        /*
         //If the current token being used will no longer be valid on the next submit, pass the new token back
         //to the client so they can resubmit
         if($newFormToken !== '') {
         $result['newFormToken'] = $newFormToken;
         }
         $status['sessionParm'] = sessionParm();
         */

        debug_log($status, "STATUS at end of cbo_field_model sendForm");
        return array($status, $cbo);

    }


    public function getCBOInstance($namespace, $cbo_name, $cboID = null)
    {
        $cboID = ($cboID) ? $cboID : getUrlParm('cbo_id');
        if ($cboID === null)
        {
            return $this->getBlankCBO($namespace, $cbo_name);
        }
        else
        {
            return $this->getCBOById($cboID, $namespace, $cbo_name);
        }
    }


    private function getBlankCBO($namespace, $cbo_name)
    {
        define('CO_PATH', 'RightNow\Connect\v1_2\\');

        $cbo_full_name = CO_PATH . $namespace . "\\" . $cbo_name;
        $cbo = new $cbo_full_name;

        return $cbo;
    }
	//OSG PS 140228-000012 added support for a complaint ID
		public function getComplaint($comp_id)
	{
		$comp = RNCPHP\CO\ComplaintAgainstOrg::fetch($comp_id);
		return $comp->Incident->CustomFields->Complaints->DebtCollection->ID;
		//$dc_obj = RNCPHP\Complaints\DebtCollection::fetch($comp->Incident->ID)
	}

    /**
     * Used to retrieve a custom business object by incident id.
     * This method provides security functionality and
     * is the only appropriate way to retrieve this data.
     *
     * @param int $i_id incident id
     */
    private function getCBOByIncidentId($i_id, $namespace, $cbo_name)
    {

        if (is_null($i_id) || !is_numeric($i_id) || $i_id < 1)
        {
            return false;
        }

        $cbo_path = 'RightNow\Connect\v1_2\\' . $namespace . "\\" . $cbo_name;
        try
        {
            $inc = RNCPHP\Incident::fetch($i_id);
            $cbo = $inc->CustomFields->$namespace->$cbo_name;
        }
        catch (Exception $e)
        {
            debug_log("EXCEPTION retrieving CBO by i_id ($i_id) " . $e->getMessage());
            return false;
        }
        debug_log($cbo, "RETURNING CBO: ");

        return $cbo;
    }


    /**
     * Used to retrieve a custom object.  This method provides security functionality and
     * is the only appropriate way to retrieve this data.
     *
     * @param int $cbo_id
     */
    private function getCBOById($cbo_id, $namespace, $cbo_name)
    {

        if (is_null($cbo_id) || !is_numeric($cbo_id) || $cbo_id < 1)
        {
            return false;
        }
        try
        {
            if (!defined('CO_PATH'))
            {
                define('CO_PATH', 'RightNow\Connect\v1_2\\');
            }

            $co = CO_PATH . $namespace . "\\" . $cbo_name;
            $cbo_obj = $co::fetch($cbo_id);
        }
        catch (Exception $e)
        {
            $this->add_error($e->getMessage(), __LINE__);
            PSLog\Log::fatal("Line ({$e->getLine()}): Error getting CBO: {$e->getMessage()}");
            RNCPHP\ConnectAPI::commit();
            return false;
        }

        return $cbo_obj;
    }


}


?>