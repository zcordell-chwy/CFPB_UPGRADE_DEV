<rn:meta controller_path="custom/instAgent/output/DataDisplayIA"
         presentation_css="widgetCss/DataDisplay.css"
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>

<? switch($this->field->data_type):
    case EUF_DT_THREAD: ?>
        <rn:widget path="custom/instAgent/output/IncidentThreadDisplayIA" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" />
        <? break;
    case EUF_DT_HIERMENU: ?>
        <rn:widget path="custom/instAgent/output/ProductCategoryDisplay" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" />
        <? break;
    case EUF_DT_FATTACH: ?>
        <rn:widget path="custom/instAgent/output/FileListDisplayIA" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" />
        <? break;
    default: ?>
<rn:widget path="custom/instAgent/output/FieldDisplayIA" name="#rn:php:$this->data['attrs']['name']#" label="#rn:php:$this->data['attrs']['label']#" dt_fields_to_convert='incidents.c$initial_sent_to_company,incidents.c$response_due,incidents.c$respond_by_60d,incidents.c$company_initial_response,incidents.c$company_response_date_2,incidents.c$company_response_date_3,incidents.c$sent_consumer_date'/>
<? endswitch; ?>
