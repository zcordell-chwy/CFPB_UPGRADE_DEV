<rn:meta controller_path="standard/search/SearchButton2" 
         js_path="custom/instAgent/search/SearchButton2" 
         presentation_css="widgetCss/SearchButton2.css" 
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10"/>

<div id="rn_<?=$this->instanceID;?>" class="rn_SearchButton2">
    <? if ($this->data['attrs']['icon_path']):?>
        <input type="image" class="rn_SubmitImage" id="rn_<?=$this->instanceID;?>_SubmitButton" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> src="<?=$this->data['attrs']['icon_path'];?>" alt="<?=$this->data['attrs']['icon_alt_text'];?>" title="<?=$this->data['attrs']['label_button'];?>"/>
    <? else:?>
        <input type="submit" class="rn_SubmitButton" id="rn_<?=$this->instanceID;?>_SubmitButton" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> value="<?=$this->data['attrs']['label_button'];?>" />
    <? endif;?>
    <? if($this->data['isIE']): ?>
    <label for="rn_<?=$this->instanceID;?>_HiddenInput" class="rn_Hidden">&nbsp;</label>
    <input id="rn_<?=$this->instanceID;?>_HiddenInput" type="text" class="rn_Hidden" disabled="disabled" />
    <? endif;?>
</div>
