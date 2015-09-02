<?php /* Originating Release: August 2013 */ ?>
<rn:meta controller_path="custom/input/ProductCategoryInput2" js_path="custom/input/ProductCategoryInput2" base_css="standard/input/ProductCategoryInput" presentation_css="widgetCss/ProductCategoryInput.css" compatibility_set="November '09+"/>

<? if($this->data['js']['readOnly']):?>
<rn:widget path="output/ProductCategoryDisplay" name="#rn:php:$this->data['attrs']['table'] . '.' . $this->data['attrs']['data_type']#" label="#rn:php:$this->data['attrs']['label_input']#" left_justify="true"/>
<? else:?>
<? $this->addJavaScriptInclude(getYUICodePath('treeview/treeview-min.js'));?>

<div id="rn_<?=$this->instanceID;?>" class="rn_ProductCategoryInput">
    <a href="javascript:void(0);" class="rn_ScreenReaderOnly" id="rn_<?=$this->instanceID?>_LinksTrigger"><?printf($this->data['attrs']['label_screen_reader_accessible_option'], $this->data['attrs']['label_input'])?>&nbsp;<span id="rn_<?=$this->instanceID;?>_TreeDescription"></span></a>
    <? if($this->data['attrs']['label_input']):?>
    <span class="rn_Label">
        <?=$this->data['attrs']['label_input']?>
        <? if($this->data['attrs']['required_lvl']):?>
        <span class="rn_Required"> <?=getMessage(FIELD_REQUIRED_MARK_LBL);?></span><span id="rn_<?=$this->instanceID;?>_RequiredLabel" class="rn_RequiredLabel">
            <span class="rn_ScreenReaderOnly">
                <?=getMessage(REQUIRED_LBL);?>
            </span>
        </span>
        <? endif;?>
    </span>
    <? endif;?>
    <button type="button" id="rn_<?=$this->instanceID;?>_<?=$this->data['attrs']['data_type'];?>_Button" class="rn_DisplayButton" <?=tabIndex($this->data['attrs']['tabindex'], 1);?>><span class="rn_ScreenReaderOnly"><?=$this->data['attrs']['label_accessible_interface']?></span> <span id="rn_<?=$this->instanceID?>_Button_Visible_Text"><?=$this->data['attrs']['label_nothing_selected'];?></span></button>
    <div class="rn_Hidden" id="rn_<?=$this->instanceID;?>_Links"></div>
    
    <?if($this->data['attrs']['show_confirm_button_in_dialog']):?>
    <div id="rn_<?=$this->instanceID;?>_TreeContainer" class="rn_PanelContainer rn_Hidden">
    <?endif;?>
    
        <div id="rn_<?=$this->instanceID;?>_Tree" class="rn_Panel rn_Hidden">
        <? /**Product / Category YUI TreeView is created here */?>
        </div>
        
    <?if($this->data['attrs']['show_confirm_button_in_dialog']):?>
        <div id="rn_<?=$this->instanceID;?>_SelectionButtons" class="rn_SelectionButtons">
            <button type="button" id="rn_<?=$this->instanceID;?>_<?=$this->data['attrs']['data_type'];?>_ConfirmButton"><?=$this->data['attrs']['label_confirm_button'];?></button>
            <button type="button" id="rn_<?=$this->instanceID;?>_<?=$this->data['attrs']['data_type'];?>_CancelButton"><?=$this->data['attrs']['label_cancel_button'];?></button>
        </div>
    </div>
    <?endif;?>
    
    <? if($this->data['attrs']['set_button']):?>
    <button type="button" id="rn_<?=$this->instanceID;?>_<?=$this->data['attrs']['data_type'];?>_SetButton"><?=$this->data['attrs']['label_set_button']?></button>
    <? endif;?>
</div>
<? endif;?>
