<rn:meta controller_path="custom/instAgent/search/ProductCategorySearchRoleFilter" js_path="custom/instAgent/search/ProductCategorySearchRoleFilter" base_css="standard/search/ProductCategorySearchFilter" presentation_css="widgetCss/ProductCategorySearchFilter.css" compatibility_set="November '09+"/>

<? $this->addJavaScriptInclude(getYUICodePath('treeview/treeview-min.js'));?>

<div id="rn_<?=$this->instanceID;?>" class="rn_ProductCategorySearchFilter">
    <a href="javascript:void(0);" class="rn_ScreenReaderOnly" id="rn_<?=$this->instanceID?>_LinksTrigger"><?printf($this->data['attrs']['label_screen_reader_accessible_option'], $this->data['attrs']['label_input'])?>&nbsp;<span id="rn_<?=$this->instanceID;?>_TreeDescription"></span></a>
    <? if($this->data['attrs']['label_input']):?>
    <span class="rn_Label"><?=$this->data['attrs']['label_input']?></span>
    <? endif;?>
    <button type="button" id="rn_<?=$this->instanceID;?>_<?=$this->data['attrs']['filter_type'];?>_Button" class="rn_DisplayButton" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>><span class="rn_ScreenReaderOnly"><?=$this->data['attrs']['label_accessible_interface']?></span> <span id="rn_<?=$this->instanceID?>_ButtonVisibleText"><?=$this->data['attrs']['label_nothing_selected'];?></span></button>
    <div class="rn_Hidden" id="rn_<?=$this->instanceID;?>_Links"></div>
    
    <?if($this->data['attrs']['show_confirm_button_in_dialog']):?>
    <div id="rn_<?=$this->instanceID;?>_TreeContainer" class="rn_PanelContainer rn_Hidden">
    <?endif;?>
    
        <div id="rn_<?=$this->instanceID;?>_Tree" class="rn_Panel rn_Hidden"><? /**Product / Category Tree goes here */?></div>
        
    <?if($this->data['attrs']['show_confirm_button_in_dialog']):?>
        <div id="rn_<?=$this->instanceID;?>_SelectionButtons" class="rn_SelectionButtons">
            <button type="button" id="rn_<?=$this->instanceID;?>_<?=$this->data['attrs']['filter_type'];?>_ConfirmButton"><?=$this->data['attrs']['label_confirm_button'];?></button>
            <button type="button" id="rn_<?=$this->instanceID;?>_<?=$this->data['attrs']['filter_type'];?>_CancelButton"><?=$this->data['attrs']['label_cancel_button'];?></button>
        </div>
    </div>
    <?endif;?>
    
</div>
