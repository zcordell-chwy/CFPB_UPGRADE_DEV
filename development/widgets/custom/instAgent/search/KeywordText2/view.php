<rn:meta controller_path="custom/instAgent/search/KeywordText2" 
         js_path="custom/instAgent/search/KeywordText2" 
         presentation_css="widgetCss/KeywordText2.css" 
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10"/>

<div id="rn_<?=$this->instanceID;?>" class="rn_KeywordText2">
    <label for="rn_<?=$this->instanceID;?>_Text"><?=$this->data['attrs']['label_text'];?></label>
    <input id="rn_<?=$this->instanceID;?>_Text" name="rn_<?=$this->instanceID;?>_Text" type="text" maxlength="255" value="<?=$this->data['initialValue'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> />
</div>
