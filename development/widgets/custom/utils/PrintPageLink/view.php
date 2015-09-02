<rn:meta controller_path="custom/utils/PrintPageLink" base_css="standard/utils/PrintPageLink" presentation_css="widgetCss/PrintPageLink.css"  compatibility_set="November '09+"/>

<span id="rn_<?=$this->instanceID;?>" class="rn_PrintPageLink">
        <a onclick="window.print(); return false;" href="javascript:void(0);" title="<?=$this->data['attrs']['label_tooltip'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>>
        <? if($this->data['attrs']['icon_path']):?>
            <img src="<?=$this->data['attrs']['icon_path'];?>" alt="<?=$this->data['attrs']['label_icon_alt']?>"/>
        <? endif;?>
        <span><?=$this->data['attrs']['label_link'];?></span>
    </a>
</span>
