<rn:meta controller_path="custom/instAgent/search/DateRangeMenu" js_path="custom/instAgent/search/DateRangeMenu" presentation_css="widgetCss/FilterDropdown2.css" compatibility_set="November '09+"/>
<div id="rn_<?=$this->instanceID;?>" class="rn_FilterDropdown2">
    <label for="rn_<?=$this->instanceID;?>_Options"><?=$this->data['js']['name']?></label>
    <select id="rn_<?=$this->instanceID;?>_Options" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> >
        <? foreach($this->data['js']['list'] as $key => $value): ?>
            <option value="<?=$value['id']?>" <?=($value['id'].'' === $this->data['js']['defaultValue'].'') ? 'selected' : '';?>><?=$value['label']?></option>
        <? endforeach;?>
    </select>
</div>
