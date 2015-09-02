<rn:meta controller_path="custom/navigation/NavigationTab2Custom" js_path="custom/navigation/NavigationTab2Custom" base_css="standard/navigation/NavigationTab2" presentation_css="widgetCss/NavigationTab2.css" compatibility_set="November '09+"/>

<span id="rn_<?=$this->instanceID;?>" class="rn_NavigationTab2 <?=$this->data['hiddenClass'];?>">
<? if($this->data['attrs']['subpages']): ?>
    <a id="rn_<?=$this->instanceID;?>_Link" class="<?=$this->data['cssClass'];?> rn_DropDown" href="<?=$this->data['attrs']['link'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> target="<?=$this->data['attrs']['target'];?>">
        <span><?=$this->data['attrs']['label_tab'];?></span>
        <em id="rn_<?=$this->instanceID;?>_DropdownButton" class="rn_ButtonOff"></em>
    </a>
    <span id="rn_<?=$this->instanceID;?>_SubNavigation" class="rn_SubNavigation rn_ScreenReaderOnly">
    <? foreach($this->data['subpages'] as $subpage):?>
        <a href="<?=$subpage['href'];?>" target="<?=$this->data['attrs']['target'];?>"><?=$subpage['title'];?></a>
    <?endforeach;?>
    </span>
<? else:?>
    <a class="<?=$this->data['cssClass'];?>" href="<?=$this->data['attrs']['link'];?>" <?=tabIndex($this->data['attrs']['tabindex'], 1);?> target="<?=$this->data['attrs']['target'];?>">
        <span><?=$this->data['attrs']['label_tab'];?></span>
    </a>
<?endif;?>
</span>
