<rn:meta controller_path="custom/congressional/output/FileListDisplayIA"
         presentation_css="widgetCss/FileListDisplay.css"
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>

<? if(count($this->data['value']) > 0): ?>
    <div  id="rn_<?=$this -> instanceID;?>" class="rn_FileListDisplay">
        <? if ($this->data['attrs']['label']): ?>
            <span class="rn_DataLabel"><?=$this -> data['attrs']['label'];?> </span>
        <? endif;?>
        <div class="rn_DataValue<?=$this->data['wrapClass']?>">
                <? for($i=0; $i<count($this->data['value']); $i++):
                     if ($this->data['fpattern'])
                         $newFrame = preg_match("/{$this->data['fpattern']}/i", $this->data['value'][$i][3]) ? 'target="_blank"' : '';
                ?>
                    <div>
                      <? $created = (stringContains($this->data['attrs']['name'], 'incidents')) ? $this->data['value'][$i][2] : '0' ?>
                      <a href="/cc/fattach/get/<?="{$this->data['value'][$i][0]}/{$created}" . sessionParm() . "/filename/" . urlencode($this->data['value'][$i][1]) ?>" <?=tabIndex($this -> data['attrs']['tabindex'], 1 + $i);?> <?=$newFrame;?> ><?=$this -> data['value'][$i]['icon'];?> <?=$this -> data['value'][$i][1];?> </a>

                       <? if($this->data['attrs']['display_file_size']):?>
                           <span class="rn_FileSize">(<?=$this -> data['value'][$i]['size'];?>)</span>
                       <? endif;?>
                    </div>
                <? endfor;?>
       </div>
    </div>
<? endif;?>

