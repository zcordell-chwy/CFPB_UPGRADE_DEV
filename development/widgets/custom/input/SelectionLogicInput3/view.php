<rn:meta controller_path="custom/input/SelectionLogicInput3"
    js_path="custom/input/SelectionLogicInput3"
    base_css="standard/input/SelectionInput"
    presentation_css="widgetCss/SelectionInput.css"
    compatibility_set="November '09+"
    required_js_module="november_09,mobile_may_10"/>



<? if($this->data['readOnly']):?>
<rn:widget path="output/FieldDisplay" left_justify="true"/>
<? else:?>
<div id="rn_<?=$this->instanceID;?>" class="rn_SelectionInput" <?=$this->data['attrs']['style_custom'];?>>

<? if($this->field->data_type === EUF_DT_SELECT || $this->field->data_type === EUF_DT_CHECK):?>
  <? if($this->data['attrs']['show_menu_as_radio']):?>
    <fieldset>  
    <? if($this->data['attrs']['label_input']):?>
      <legend id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
        <? if($this->data['attrs']['required']&& (strlen($this->data['attrs']['widget_group']) < 1 ) ):?>
          <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
        <? elseif(strlen($this->data['attrs']['label_optional']) > 0):?>
          <span class="ps_labelOptional"><?=$this->data['attrs']['label_optional']?></span><span class="rn_ScreenReaderOnly"><?=$this->data['attrs']['label_optional']?></span>
        <? endif;?>
      </legend>
      <? if($this->data['attrs']['label_note']):?>
        <div class="note"><?=$this->data['attrs']['label_note'];?></div>
      <? endif;?>
    <? endif;?>
    <div id="rn_<?=$this->instanceID;?>_menu_as_radios" style="margin-top: 9px;">
    <? foreach($this->data['menuItems'] as $i => $item):
      $id = "rn_{$this->instanceID}_{$this->data['js']['name']}_$i"; ?>
      <input type="radio" name="rn_<?=$this->instanceID;?>_Radio" id="<?=$id;?>" <?=$checked;?> value="<?=$i;?>"/>
      <label for="<?=$id;?>"><?=$item;?><? if($this->data['js']['hint'] && $i===1):?> <span class="rn_ScreenReaderOnly"><?=$this->data['js']['hint']?></span><?endif;?></label>
    <? endforeach;?>
    </div>
    </fieldset>
  <? else:?>
    
    <? if($this->data['attrs']['label_input']):?>
	    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
	    <? if($this->data['attrs']['required'] && (strlen($this->data['attrs']['widget_group']) < 1 ) ):?>
	        <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
		<? elseif(strlen($this->data['attrs']['label_optional']) > 0):?>
			<span class="ps_labelOptional"><?=$this->data['attrs']['label_optional']?></span><span class="rn_ScreenReaderOnly"><?=$this->data['attrs']['label_optional']?></span>
	    <? endif;?>
	    <? if($this->data['js']['hint']):?>
	        <span class="rn_ScreenReaderOnly">
	        <?=$this->data['js']['hint'];?>
	        </span>
	    <? endif;?>
	    </label>
    <? else: ?>
    	<label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label">
    		<span class="rn_ScreenReaderOnly">
    			<?=$this->data['accesibility_label'];?>
			</span>
		</label>
    <? endif;?>
    <? if($this->data['attrs']['select_required_pos'] > 0):?>
        <span id="select_required_id_<?=$this->instanceID;?>" class="rn_Required" style="position:absolute; left:<?=$this->data['attrs']['select_required_pos'];?>px"> * </span>
    <? endif;?>
    <select id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>>
    <? if(!$this->data['hideEmptyOption']):?>
        <option label="" value=""><?=$this->data['attrs']['label_nothing_selected'];?></option>
    <? endif;?>
    <? if(is_array($this->data['menuItems'])):?>
        <? foreach($this->data['menuItems'] as $key => $item):
            $selected = '';
            if($key==$this->data['value'] || $key==$this->data['js']['prev']) $selected = 'selected="selected"';?>
            <option label="<?=$item?>" value="<?=$key;?>" <?=$selected;?>><?=$item;?></option>
        <? endforeach;?>
    <? endif;?>
    </select>
    
  <? endif;?>

<? elseif($this->field->data_type === EUF_DT_RADIO):?>
    <fieldset>
    <? if(! is_null($this->data['attrs']['radio_group']) ){ ?>
	    <div id="rn_<?=$this->instanceID;?>" class="rn_SelectionInput rn_radioGroupItem" style="clear:both">
		    <? if($this->data['attrs']['label_input']):?>
		    <input type="radio"  name="rn_<?=$this->data['attrs']['radio_group']?>" id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" <?=($this->data['checkedIndex'] === 1) ? 'checked="checked"' : ''?> value="1" />
		    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label" >
		        <?=$this->data['attrs']['label_input'];?>
		    	<? if(strlen($this->data['attrs']['label_optional']) > 0):?>
					<span class="ps_labelOptional"><?=$this->data['attrs']['label_optional']?></span><span class="rn_ScreenReaderOnly"><?=$this->data['attrs']['label_optional']?></span>
		    	<?endif?>
		    </label>
			<? if($this->data['attrs']['label_clarification']):?>
				<div class="ps_clarificationLabel"><?=$this->data['attrs']['label_clarification'];?></div>
			<?endif;?>
	        <? else: ?>
		    	<label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label">
		    		<span class="rn_ScreenReaderOnly">
		    			<?=$this->data['accesibility_label'];?>
					</span>
				</label>
		    <? endif;?>
		</div>
    <? }else if($this->data['attrs']['is_checkbox'] == true){?>

		<div id="rn_<?=$this->instanceID;?>" class="rn_SelectionInput" style="clear:both">
		    <? if($this->data['attrs']['label_input']):?>
		    <input type="checkbox" name="rn_<?=$this->instanceID;?>_CheckBox" id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" <?=($this->data['checkedIndex'] === 1) ? 'checked="checked"' : ''?> value="1" style="float: left; margin-right: 5px;" />
		    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>" id="rn_<?=$this->instanceID;?>_Label" class="rn_Label" style="float:left; left:5px; top:-2px;">
		        <?=$this->data['attrs']['label_input'];?>
		    		<? if($this->data['attrs']['required'] && (strlen($this->data['attrs']['widget_group']) < 1 ) ):?><span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
		    	<? elseif(strlen($this->data['attrs']['label_optional']) > 0):?>
					<span class="ps_labelOptional"><?=$this->data['attrs']['label_optional']?></span><span class="rn_ScreenReaderOnly"><?=$this->data['attrs']['label_optional']?></span>
		    	<?endif;?>
                <? if($this->data['attrs']['label_clarification']):?>
                    <br/><span class="ps_clarificationLabel"><?=$this->data['attrs']['label_clarification'];?></span>
                <?endif;?>
		    </label>
		    <? endif;?>
		</div>
		<br />

	<? }else{?>
	    <? if($this->data['attrs']['label_input']):?>
	        <legend id="rn_<?=$this->instanceID;?>_Label" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
	        	<? if($this->data['attrs']['required']&& (strlen($this->data['attrs']['widget_group']) < 1 ) ):?>
	        		<span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
	        	<? elseif(strlen($this->data['attrs']['label_optional']) > 0):?>
					<span class="ps_labelOptional"><?=$this->data['attrs']['label_optional']?></span><span class="rn_ScreenReaderOnly"><?=$this->data['attrs']['label_optional']?></span>
	        	<? endif;?>
	    	</legend>
            <? if($this->data['attrs']['label_note']):?>
                <div class="note"><?=$this->data['attrs']['label_note'];?></div>
            <? endif;?>
	    <? endif;?>
	    <? for($i = 1; $i >= 0; $i--):
	            $checked = ($i === $this->data['checkedIndex']) ? 'checked="checked"' : '';
	            $id = "rn_{$this->instanceID}_{$this->data['js']['name']}_$i"; ?>
	        <input type="radio" name="rn_<?=$this->instanceID;?>_Radio" id="<?=$id;?>" <?=$checked;?> value="<?=$i;?>"/>
	        <label for="<?=$id;?>"><?=$this->data['radioLabel'][$i];?><? if($this->data['js']['hint'] && $i===1):?> <span class="rn_ScreenReaderOnly"><?=$this->data['js']['hint']?></span><?endif;?></label>
	    <? endfor;?>
    <?}?>
    </fieldset>
<? endif;?>
</div>

<? endif;?>
<? if( $this->data['attrs']['help_text_when_hidden'] ): ?>
    <div id="rn_<?=$this->instanceID;?>_alt_text" class="rn_SelectionInput_altText rn_Hidden"><?= $this->data['attrs']['help_text_when_hidden']; ?></div>
<? endif; ?>
