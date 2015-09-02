<rn:meta controller_path="custom/instAgent/reports/Paginator" 
         js_path="standard/reports/Paginator" 
         base_css="standard/reports/Paginator" 
         presentation_css="widgetCss/Paginator.css" 
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10"/>

<div id="rn_<?=$this->instanceID?>" class="rn_Paginator <?=$this->data['hideWidgetClass'];?>">
    <a href="<?=$this->data['js']['backPageUrl'];?>" id="rn_<?=$this->instanceID;?>_Back" class="<?=$this->data['backClass'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>>
    <? if($this->data['attrs']['back_icon_path']):?>
        <img src="<?=$this->data['attrs']['back_icon_path'];?>" alt="<?=$this->data['attrs']['label_back'];?>"/>
    <? else:?>
        <?=$this->data['attrs']['label_back'];?>
    <? endif;?>
    </a>
    <span id="rn_<?=$this->instanceID;?>_Pages" <?=tabIndex($this->data['attrs']['tabindex'], 2);?> class="rn_PageLinks">
        <? for($i = $this->data['js']['startPage']; $i <= $this->data['js']['endPage']; $i++):?>
            <? if($i == $this->data['js']['currentPage']):?>
                <span class="rn_CurrentPage"><?=$i;?></span>
            <? else:?>
                <a id="rn_<?=$this->instanceID . '_PageLink_' . $i;?>" href="<?=$this->data['js']['pageUrl'] . $i;?>" title="<?printf($this->data['attrs']['label_page'], $i, $this->data['totalPages']);?>" <?=tabIndex($this->data['attrs']['tabindex'], $i + 1);?>><?=$i;?></a>
            <? endif;?>
        <? endfor;?>
    </span>
    <a href="<?=$this->data['js']['forwardPageUrl'];?>" id="rn_<?=$this->instanceID;?>_Forward" class="<?=$this->data['forwardClass'];?>" <?=tabIndex($this->data['attrs']['tabindex'], $i + 1);?>>
    <? if($this->data['attrs']['forward_icon_path']):?>
        <img src="<?=$this->data['attrs']['forward_icon_path']?>" alt="<?=$this->data['attrs']['label_forward']?>"/>
    <? else:?>
        <?=$this->data['attrs']['label_forward']?>
    <? endif;?>
    </a>
</div>
