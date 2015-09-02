<rn:meta controller_path="standard/input/FormInput" 
    presentation_css="widgetCss/FormInput.css" 
    compatibility_set="November '09+" 
    required_js_module="november_09,mobile_may_10"/>


<? switch($this->field->data_type):
    case EUF_DT_SELECT:
    case EUF_DT_CHECK:
    case EUF_DT_RADIO:?>
        <rn:widget path="input/SelectionInput"/>
        <? break;
    case EUF_DT_DATETIME:
    case EUF_DT_DATE:?>
        <rn:widget path="input/DateInput"/>
        <? break;
    default:?>
        <rn:widget path="custom/instAgent/input/TextInput"/>
        <? break;
endswitch;?>
