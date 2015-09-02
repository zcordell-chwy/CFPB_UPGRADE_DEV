<rn:meta controller_path="custom/instAgent/search/SearchTypeList2" js_path="custom/instAgent/search/SearchTypeList2" presentation_css="widgetCss/SearchTypeList2.css" compatibility_set="November '09+"/>
<div id="rn_<?=$this->instanceID;?>" class="rn_SearchTypeList2">
    <label for="rn_<?=$this->instanceID;?>_Options" ><?=$this->data['attrs']['label_text']?></label>
    <select id="rn_<?=$this->instanceID;?>_Options" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>>
        <? foreach($this->data['js']['filters'] as $key => $value): ?>
            <option value='<?=$value['fltr_id']?>' <?=($value['fltr_id'] == $this->data['js']['defaultFilter']) ? 'selected="selected"' : '';?>><?=$value['prompt']?></option>
        <? endforeach;?>
    </select>
</div>
