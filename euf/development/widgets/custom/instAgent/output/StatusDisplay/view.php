<rn:meta controller_path="custom/instAgent/output/StatusDisplay"
         presentation_css="widgetCss/DataDisplay.css"
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>

<? switch($this->field->data_type): ?>
<?
    case EUF_DT_THREAD: ?>
        <rn:widget path="output/IncidentThreadDisplay" />
        <? break;
    case EUF_DT_HIERMENU: ?>
        <rn:widget path="output/ProductCategoryDisplay" />
        <? break;
    case EUF_DT_FATTACH: ?>
        <rn:widget path="output/FileListDisplay" />
        <? break;
    default: ?>
        <rn:widget path="output/FieldDisplay" />
<? endswitch; ?>
