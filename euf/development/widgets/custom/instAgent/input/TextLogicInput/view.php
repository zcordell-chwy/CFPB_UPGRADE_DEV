<rn:meta controller_path="custom/instAgent/input/TextLogicInput" 
    js_path="custom/instAgent/input/TextLogicInput" 
    base_css="standard/input/TextInput" 
    presentation_css="widgetCss/TextInput.css" 
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10"/>

<? if($this->data['readOnly']):?>
<rn:widget path="output/FieldDisplay" left_justify="true"/>
<? else:?>

<div id="rn_<?=$this->instanceID;?>" class="rn_TextInput" <?=$this->data['attrs']['style_custom'];?>>
<?
switch($this->data['js']['type']):
case EUF_DT_VARCHAR:
case EUF_DT_INT:
?>
  <? if($this->data['attrs']['label_before_input']):?>
    <span><?=$this->data['attrs']['label_before_input'];?></span>
  <? endif;?>

  <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_infield">
    <? if($this->data['attrs']['label_input']):?>
      <span class="label"><?=$this->data['attrs']['label_input'];?>
    <? if($this->data['attrs']['required']):?>
        <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
    <? endif;?>
    <? if($this->data['js']['hint']):?>
    <span class="rn_ScreenReaderOnly"> <?=$this->data['js']['hint']?></span>
    <? endif;?>
      </span>
    <? endif;?>
    <input type="text" id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" name="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" class="rn_Text" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> <? if($this->data['maxLength']): echo('maxlength="' . $this->data['maxLength'] . '"'); endif;?> value="<?=$this->data['value'];?>"/>
  </label>
  <? // enable anonymous email ?>
  <? if ($this->data['attrs']['is_anon']): ?>
    <label for="is_anon_check" id="is_anon_check_Label">
      <input type="checkbox" id="is_anon_check" name="is_anon_check" />&nbsp;Submit anonymously
    </label>
  <? endif;?>
<?
break;
case EUF_DT_PASSWORD:
?>
    <? if($this->data['attrs']['label_input']):?>
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Hidden"><?=$this->data['attrs']['label_input'];?>
    <? if($this->data['attrs']['required']):?>
        <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
    <? endif;?>
    <? if($this->data['js']['hint']):?>
    <span class="rn_ScreenReaderOnly"> <?=$this->data['js']['hint']?></span>
    <? endif;?>
    </label>
    <? endif;?>
    <input type="password" id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" class="rn_Password" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> <? if($this->data['maxLength']): echo('maxlength="' . $this->data['maxLength'] . '"'); endif;?>/>
<?
break;
default:
?>
    <? if($this->data['attrs']['label_input']):?>
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
    <? if($this->data['attrs']['required']):?>
        <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
    <? endif;?>
    <? if($this->data['js']['hint']):?>
    <span class="rn_ScreenReaderOnly"> <?=$this->data['js']['hint']?></span>
    <? endif;?>
    </label>
    
    <? // if label_input_sub has text ?>
    <? if($this->data['attrs']['label_input_sub']):?>
        <?=$this->data['attrs']['label_input_sub'];?>
    <? endif;?>
    
    <? endif;?>
    <textarea id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" class="rn_TextArea" rows="7" cols="60" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>><?=$this->data['value'];?></textarea><br />
    <span id="rn_<?=$this->instanceID?>_<?=$this->data['js']['name']?>_count" <?=($this->data['attrs']['max_chars'] <= 0) ? 'class="rn_Hidden"' : 'class="count"'?>><?=$this->data['attrs']['max_chars']?> <?=$this->data['attrs']['max_chars_label']?></span>
<?
break;
endswitch;
?>
</div>

<? endif;?>
