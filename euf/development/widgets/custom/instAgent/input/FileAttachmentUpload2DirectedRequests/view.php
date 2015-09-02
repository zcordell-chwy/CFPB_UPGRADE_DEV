<rn:meta controller_path="custom/instAgent/input/FileAttachmentUpload2DirectedRequests" js_path="custom/instAgent/input/FileAttachmentUpload2DirectedRequests" base_css="standard/input/FileAttachmentUpload2" presentation_css="widgetCss/FileAttachmentUpload2.css"  compatibility_set="November '09+"/>

<div id="rn_<?=$this->instanceID;?>" class="rn_FileAttachmentUpload2">
    <label for="rn_<?=$this->instanceID;?>_FileInput"><?=$this->data['attrs']['label_input'];?></label>
    <div class="attach">
        <input class="attach" name="file" id="rn_<?=$this->instanceID;?>_FileInput" type="file" size="38" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> 
            onchange="document.getElementById('file_input_overlay').value = this.value; return true;" />
        <input class="attach-overlay" width="35" type="text" value="" id="file_input_overlay">
        <button class="attach" onclick="RightNow.Event.fire('evt_browseClick');">Attach</button>
    </div>
    <? if($this->data['attrs']['loading_icon_path']):?>
    <img id="rn_<?=$this->instanceID;?>_LoadingIcon" class="rn_Hidden" alt="" src="<?=$this->data['attrs']['loading_icon_path'];?>" />
    <? endif;?>
    <span id="rn_<?=$this->instanceID;?>_StatusMessage"></span>
</div>
