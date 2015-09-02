<rn:meta controller_path="custom/instAgent/input/SelectionInput" 
    js_path="standard/input/SelectionInput" 
    base_css="standard/input/SelectionInput" 
    presentation_css="widgetCss/SelectionInput.css" 
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10"/>
<? if($this->data['readOnly']):?>
<rn:widget path="instAgent/output/FieldDisplayIA" left_justify="true"/>
<? else:?>
<div id="rn_<?=$this->instanceID;?>" class="rn_SelectionInput">
<?
if($this->field->data_type === (4) || $this->field->data_type === (12)):?>
    <? if($this->data['attrs']['label_input']):?>
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
    <?
if($this->data['attrs']['required']):?>
        <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage((5883))?></span>
    <? endif;?>
    <?
if($this->data['js']['hint']):?>
        <span class="rn_ScreenReaderOnly">
        <?=$this->data['js']['hint'];?>
        </span>
    <?
endif;?>
    </label>
    <?
endif;?>
    <select id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" <?=tabIndex($this->data['attrs']['tabindex'],
1);?>>
    <?
if(!$this->data['hideEmptyOption']):?>
        <option value="">--</option>
    <? endif;?>
    <?
if(is_array($this->data['menuItems'])):?>
        <? foreach($this->data['menuItems'] as $key => $item): $selected = '';
if($key==$this->data['value']) $selected = 'selected="selected"';?>
            <option value="<?=$key;?>" <?=$selected;?>><?=$item;?></option>
        <?
endforeach;?>
    <?
endif;?>
    </select>
<?
elseif($this->field->data_type === (3)):?>
    <fieldset>
    <? if($this->data['attrs']['label_input']):?>
        <legend id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?><?
if($this->data['attrs']['required']):?><span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage((5883))?></span><? endif;?></legend>
    <?
endif;?>
    <?
for($i = 1;
$i >= 0;
$i--): $checked = ($i === $this->data['checkedIndex']) ? 'checked="checked"' : '';
$id = "rn_{$this->instanceID}_{$this->data['js']['name']}_$i";
?>
        <input type="radio" name="rn_<?=$this->instanceID;?>_Radio" id="<?=$id;?>" <?=$checked;?> value="<?=$i;?>"/>
        <label for="<?=$id;?>"><?=$this->data['radioLabel'][$i];?><?
if($this->data['js']['hint'] && $i===1):?> <span class="rn_ScreenReaderOnly"><?=$this->data['js']['hint']?></span><?endif;?></label>
    <?
endfor;?>
    </fieldset>
<?
endif;?>
</div>
<?
endif;?>
