<rn:meta controller_path="custom/instAgent/output/CompanyNameDisplay"
         presentation_css="widgetCss/FieldDisplay.css"
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>

<div  id="rn_<?=$this->instanceID;?>" class="rn_FieldDisplay">
    <? if ($this->data['attrs']['label']): ?>
        <span class="rn_DataLabel"><?=$this->data['attrs']['label'];?> </span>
    <? endif; ?>
    <div class="rn_DataValue<?=$this->data['wrapClass']?>">
        <?=$this->data['value']?>
    </div>
</div>

