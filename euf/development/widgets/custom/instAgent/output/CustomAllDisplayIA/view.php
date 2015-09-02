<rn:meta controller_path="custom/instAgent/output/CustomAllDisplayIA"  
        presentation_css="widgetCss/CustomAllDisplay.css" 
        compatibility_set="November '09+" 
        required_js_module="november_09,mobile_may_10,none"/>

<? for($i=0; $i<count($this->data['fields']); $i++): ?>
    <rn:widget path="custom/instAgent/output/DataDisplayIA" name="#rn:php:$this->data['attrs']['table']. '.' . $this->data['fields']['cf_item'.$i]['col_name']#" />
<? endfor; ?>
