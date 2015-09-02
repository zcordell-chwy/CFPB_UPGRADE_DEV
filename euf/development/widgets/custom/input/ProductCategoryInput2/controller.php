<?php /* Originating Release: August 2013 */

  if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!class_exists('ProductCategoryInput'))
	requireWidgetController('standard/input/ProductCategoryInput');
	
class ProductCategoryInput2 extends ProductCategoryInput
{
    function __construct()
    {
        parent::__construct();
        
        $this->attrs['restrict_to_ids'] = new Attribute('restrict_to_ids', 'STRING', "A comma-separated list of IDs to use instead of relaying on the defaults.", null);
        $this->attrs['show_description_in'] = new Attribute('show_description_in', 'STRING', "The ID of an HTML element where the selected item's description should be shown.", null);

        // ConnectPHP
        require_once( get_cfg_var( 'doc_root' ).'/include/ConnectPHP/Connect_init.phph' );
        initConnectAPI();
    }

    function generateWidgetInformation()
    {
        parent::generateWidgetInformation();
    }

    function getData()
    {
        parent::getData();

				// Restrict IDs
				if($this->data['attrs']['restrict_to_ids'] != '')
				{
					$restrict_to_ids = explode(",",$this->data['attrs']['restrict_to_ids']);
					foreach($this->data['js']['hierData'] as $level => $levelItems)
					{
						$newLevel = array();
						foreach($levelItems as $levelItemIDX => $levelItem)
						{
							if(($levelItem["value"] == 0) || in_array($levelItem["value"],$restrict_to_ids))
							{
								// Keep
								$newLevel[] = $levelItem;
							}
						}
						$this->data['js']['hierData'][$level] = $newLevel;
						$this->data['js']['hierDataNone'][$level] = $newLevel;
					}
				}

				// Show description
				$descriptions = array();
				if($this->data['attrs']['show_description_in'] != '')
				{
					foreach($this->data['js']['hierData'] as $level => $levelItems)
					{
						foreach($levelItems as $levelItemIDX => $levelItem)
						{
							$descriptions[$levelItem["value"]] = "";
						}
					}
					
					$objectType = (($this->data['js']['hm_type'] == HM_PRODUCTS) ? "ServiceProduct" : "ServiceCategory");
					$descriptions = $this->getDescriptionsForIDs(array_keys($descriptions),$objectType);
				}
				
				$this->data['js']['descriptions'] = $descriptions;
    }
    
    function getDescriptionsForIDs($arrIDs,$objectType,$langID = 1)
		{
			$whereClauses = array();
			foreach($arrIDs as $ID)
			{
				$whereClauses[] = "ID=".intval($ID);
			}
			$all = array();
			$rs = RightNow\Connect\v1_2\ROQL::query("SELECT ID,Descriptions.LabelText FROM {$objectType} WHERE (".implode(" OR ",$whereClauses).") AND Descriptions.Language={$langID}")->next();
			while($obj = $rs->next())
			{
				$all[$obj["ID"]] = $obj["LabelText"];
			}
			return $all;
		}
}
