<rn:meta controller_path="custom/instAgent/output/ProductCategoryDisplay"  
         presentation_css="widgetCss/ProductCategoryDisplay.css" 
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>

<? if ($this->data['value'][0]['label']):?>
<div  id="rn_<?=$this->instanceID;?>" class="rn_ProductCategoryDisplay">
    <? if ($this->data['attrs']['label']): ?>
        <span class="rn_DataLabel"><?=$this->data['attrs']['label'];?> </span>
    <? endif; ?>
    <div class="rn_DataValue<?=$this->data['wrapClass']?>">
    <?
       /* OLD VERSION: Replaced by Eric G with code below
       // display 2nd level heir menu if avail otherwise only display 1st level
       $tmp = ($this->data['attrs']['show_both_levels']) ? $this->data['value'][0]['label'].': '.$this->data['value'][1]['label'] : $this->data['value'][1]['label'];
       print ($this->data['value'][1]['label']) ? $tmp : $this->data['value'][0]['label'];
       */
       
       // EG: trying to clarify/fix logic above
       $display_value = $this->data['value'][0]['label'];
       if ($this->data['attrs']['show_both_levels'] && isset($this->data['value'][1]['label']))
       {
           $display_value .= ": {$this->data['value'][1]['label']}";
       }
       print $display_value;      
    ?>
    
    <?/*
        <ul >
        <? foreach($this->data['value'] as $hier): ?>
            <li>
            <? echo str_repeat('&nbsp;&nbsp;', $hier['level']) ?>
            <? if($this->data['attrs']['report_page_url'] !== ''):?>
                <a href="<?=$this->data['attrs']['report_page_url'] . '/' . $this->data['filterKey'] . '/' . $hier['hier_list'] . $this->data['appendedParameters'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1 + $count++);?>><?=$hier['label'];?></a>
            <? else:?>
                <?=$hier['label'];?>
            <? endif;?>
            </li>
        <? endforeach; ?>
        </ul>
    */?>
    </div>
</div>
<? endif;?>

