<rn:meta controller_path="custom/congressional/output/DataDisplayIA"
         presentation_css="widgetCss/DataDisplay.css"
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>

<? switch($this->field->data_type):
    case EUF_DT_THREAD: ?>
        <rn:widget path="custom/congressional/output/IncidentThreadDisplayIA" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" />
        <? break;
    case EUF_DT_HIERMENU: ?>
        <rn:widget path="custom/congressional/output/ProductCategoryDisplay" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" />
        <? break;
    case EUF_DT_FATTACH: ?>
        <rn:widget path="custom/congressional/output/FileListDisplayIA" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" />
        <? break;
    default: ?>
        <rn:widget path="custom/congressional/output/FieldDisplayIA" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" />
<? endswitch; ?>
