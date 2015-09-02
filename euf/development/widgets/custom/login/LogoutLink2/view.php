<rn:meta controller_path="custom/login/LogoutLink2" 
    js_path="standard/login/LogoutLink2" 
    presentation_css="widgetCss/LogoutLink2.css" 
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10"/>

<span id="rn_<?=$this->instanceID;?>" class="rn_LogoutLink2">
    [<a id="rn_<?=$this->instanceID;?>_LogoutLink" href="javascript:void(0);" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>><?=$this->data['attrs']['label'];?></a>]
</span>
