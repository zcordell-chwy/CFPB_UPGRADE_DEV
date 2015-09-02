<rn:meta controller_path="custom/output/FormReviewFieldDisplay4" 
    presentation_css="widgetCss/FormReviewFieldDisplay2.css"
    js_path="custom/output/FormReviewFieldDisplay4"
    compatibility_set="November '09+" 
    required_js_module="november_09,mobile_may_10"/>


<? $hiddenStyle = $this->data['hidden'] ? "rn_Hidden" : ""; ?>

<!-- Inline display style -->
<div id="rn_<?=$this->instanceID;?>" class="rn_FieldDisplay  <?=$hiddenStyle?>" style="clear:both;">

    <? if ($label): ?>
        <span class="rn_DataLabel"><?=$label?></span>
    <? endif; ?>

    <? switch($type):
        case EUF_DT_CHECK: ?>
            <input class="ps_reviewCheckbox" type="checkbox" id="<?=$this->instanceID;?>_DataValue" disabled="" <?=($value === 1) ? 'checked="checked"' : ''?>><label for="<?=$this->instanceID;?>_DataValue"><?=$label_checkbox?></label>
            <? break; ?>

        <? case EUF_DT_FATTACH: ?>
	        <? if($this->data['attrs']['allow_file_download']){?>
	        	<div class="indent rn_FileAttachmentUpload2" >
	        		<?$fattachName = $this->data['attrs']['table'].".".$this->data['attrs']['name'];?>
	        		<rn:widget path="custom/instAgent/output/FileListDisplay" label="" name="#rn:php:$fattachName#"/>
	        	</div>
	        	
        	<?}else{?>
	            <!-- Block display style -->
	            <div class="rn_FileAttachmentUpload2" id="<?=$this->instanceID;?>_DataValue">
                    <ul>
	                <? if (is_array($value)): ?>
	                    <? foreach ($value as $file): ?>
	                        <li><?=$file[1]?> (<?=$file['size']?>)</li>
	                    <? endforeach; ?>
	                <? endif; ?>
                    </ul>
	            </div>
       		<?}?>
            <? break; ?>

        <? case EUF_DT_MEMO: ?>
            <br/>
            <div class="indent"><pre id="<?=$this->instanceID;?>_DataValue" class="ps_Preformatted"><?=$value?></pre></div>
            <? break; ?>

        <? case EUF_DT_RADIO: ?>
            <!-- EUF_DT_RADIO => the field is a Yes/No field -->
            <div class="rn_DataValue rn_RadioDataValue">
                <label for="<?=$this->instanceID;?>_DataValue_Yes">Yes</label><input id="<?=$this->instanceID;?>_DataValue_Yes" type="radio" disabled="" <?=($value == 1) ? 'checked="checked"' : ''?>> 
                <label for="<?=$this->instanceID;?>_DataValue_No">No</label><input id="<?=$this->instanceID;?>_DataValue_No" type="radio" disabled="" <?=($value == 0) ? 'checked="checked"' : ''?>>
            </div>
	    <div style="clear:both"></div>
            <? break; ?>
        
        <? case EUF_DT_SELECT: ?>
          <? if($this->data['attrs']['show_menu_as_radio']):?>
            <div class="rn_DataValue rn_RadioDataValue">
                <label for="<?=$this->instanceID;?>_DataValue_Yes">Yes</label><input id="<?=$this->instanceID;?>_DataValue_Yes" type="radio" disabled="" <?=($value == 1) ? 'checked="checked"' : ''?>> 
            </div>
          <? else:?>
                <div id="<?=$this->instanceID;?>_DataValue" class="rn_DataValue"><?=$value?>&nbsp;</div>
          <? endif;?>
          <? break; ?>

        <? default: ?>
            <div id="<?=$this->instanceID;?>_DataValue" class="rn_DataValue"><?=$value?>&nbsp;</div>
            <? break; ?>
    <? endswitch; ?>
</div>

