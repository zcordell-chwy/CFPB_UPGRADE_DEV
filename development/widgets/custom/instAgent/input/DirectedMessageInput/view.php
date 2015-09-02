<rn:meta controller_path="custom/instAgent/input/DirectedMessageInput" 
    js_path="custom/instAgent/input/DirectedMessageInput" 
    base_css="standard/input/TextInput" 
    presentation_css="widgetCss/TextInput.css" 
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10"/>

<div id="rn_<?=$this->instanceID;?>" class="rn_TextInput" >
    <? if($this->data['attrs']['label_input']):?>
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
    <? if($this->data['attrs']['required']):?>
        <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
    <? endif;?>
    <? if($this->data['js']['hint']):?>
    <span class="rn_ScreenReaderOnly"> <?=$this->data['js']['hint']?></span>
    <? endif;?>
    </label>
    <? endif;?>    
    
    <textarea id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" class="rn_TextArea" rows="7" cols="60" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>><?=$this->data['value'];?></textarea><br />
    <span id="rn_<?=$this->instanceID?>_<?=$this->data['js']['name']?>_count" <?=($this->data['attrs']['max_chars'] <= 0 || $this->data['attrs']['max_chars_label'] == "") ? 'class="rn_Hidden"' : 'class="count"'?>><?=$this->data['attrs']['max_chars']?> <?=$this->data['attrs']['max_chars_label']?></span>

</div>

