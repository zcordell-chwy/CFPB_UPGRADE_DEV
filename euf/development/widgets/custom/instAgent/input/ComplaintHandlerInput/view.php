<rn:meta controller_path="custom/instAgent/input/ComplaintHandlerInput" 
    js_path="custom/instAgent/input/ComplaintHandlerInput" 
    base_css="standard/input/SelectionInput" 
    presentation_css="widgetCss/SelectionInput.css" 
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10"/>

<? if($this->data['readOnly']):?>
<rn:widget path="output/FieldDisplay" left_justify="true"/>
<? else:?>

<div id="rn_<?=$this->instanceID;?>" class="rn_SelectionInput" <?=$this->data['attrs']['style_custom'];?>>

<? if($this->field->data_type === EUF_DT_SELECT || $this->field->data_type === EUF_DT_CHECK):?>
    <? if($this->data['attrs']['label_input']):?>
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
    <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>

    <? if($this->data['js']['hint']):?>
        <span class="rn_ScreenReaderOnly">
        <?=$this->data['js']['hint'];?>
        </span>
    <? endif;?>
    </label>
    <? endif;?>
    <? if($this->data['attrs']['select_required_pos'] > 0):?>
        <span id="select_required_id_<?=$this->instanceID;?>" class="rn_Required" style="position:absolute; left:<?=$this->data['attrs']['select_required_pos'];?>px"> * </span>
    <? endif;?>
    <select id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>>
    <? if(!$this->data['hideEmptyOption']):?>
        <option value=""><?=$this->data['attrs']['label_nothing_selected'];?></option>
    <? endif;?>
    <? if(is_array($this->data['menuItems'])):?>
        <? foreach($this->data['menuItems'] as $key => $item):
             $selected = '';
             if($key==$this->data['value'] || $key==$this->data['js']['prev']) $selected = 'selected="selected"';?>
            <option value="<?=$key;?>" <?=$selected;?>><?=$item;?></option>
        <? endforeach;?>
    <? endif;?>
    </select>

<? elseif($this->field->data_type === EUF_DT_RADIO):?>
    <fieldset>
    <? if($this->data['attrs']['is_checkbox'] == true):?>
    
<div id="rn_<?=$this->instanceID;?>" class="rn_SelectionInput" style="clear:both">
    <? if($this->data['attrs']['label_input']):?>
    <input type="checkbox" name="rn_<?=$this->instanceID;?>_CheckBox" id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" <?=($this->data['checkedIndex'] === 1) ? 'checked="checked"' : ''?> value="1" style="float: left; margin-right: 5px;" />
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label" style="float:left; left:5px; top:-2px;">
        <?=$this->data['attrs']['label_input'];?><? if($this->data['attrs']['required']):?><span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span><?endif?>
    </label>
    <? endif;?>
</div>
<br />
    
    <? else:?>

    <? if($this->data['attrs']['label_input']):?>
        <legend id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?><? if($this->data['attrs']['required']):?><span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span><? endif;?></legend>
    <? endif;?>
    <? for($i = 1; $i >= 0; $i--):
            $checked = ($i === $this->data['checkedIndex']) ? 'checked="checked"' : '';
            $id = "rn_{$this->instanceID}_{$this->data['js']['name']}_$i"; ?>
        <input type="radio" name="rn_<?=$this->instanceID;?>_Radio" id="<?=$id;?>" <?=$checked;?> value="<?=$i;?>"/>
        <label for="<?=$id;?>"><?=$this->data['radioLabel'][$i];?><? if($this->data['js']['hint'] && $i===1):?> <span class="rn_ScreenReaderOnly"><?=$this->data['js']['hint']?></span><?endif;?></label>
    <? endfor;?>

    <? endif;?>
    </fieldset>
<? endif;?>
</div>

<? endif;?>
