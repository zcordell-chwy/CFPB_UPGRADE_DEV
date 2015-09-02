<rn:meta controller_path="custom/instAgent/output/DataDisplay"  
         presentation_css="widgetCss/DataDisplay.css" 
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>
     
<? switch($this->field->data_type): ?>
<? case (10): ?>
        <rn:widget path="instAgent/output/IncidentThreadDisplay" />
        <? break;
case (9): ?>
        <rn:widget path="instAgent/output/ProductCategoryDisplay" />
        <? break;
case (11): ?>
        <rn:widget path="instAgent/output/FileListDisplay" />
        <? break;
default: ?>
        <rn:widget path="instAgent/output/FieldDisplay" />
<? endswitch;
?>
