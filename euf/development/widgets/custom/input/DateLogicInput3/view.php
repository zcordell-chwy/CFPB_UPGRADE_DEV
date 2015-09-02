<rn:meta controller_path="custom/input/DateLogicInput3" 
    js_path="custom/input/DateLogicInput3" 
    base_css="standard/input/DateInput" 
    presentation_css="widgetCss/DateInput.css" 
    compatibility_set="November '09+" 
    required_js_module="november_09,mobile_may_10"/>

<? if($this->data['readOnly']):?>
<rn:widget path="output/FieldDisplay" left_justify="true"/>
<? else:?>

<!--CSS file (default YUI Sam Skin) -->
<link rel="stylesheet" type="text/css" href="/rnt/rnw/yui_2.7/calendar/assets/skins/sam/calendar.css">
<style>
.yui-skin-sam .yui-calcontainer .yui-cal-nav .yui-cal-nav-btn.yui-default,
.yui-skin-sam .yui-calcontainer .yui-cal-nav .yui-cal-nav-btn {
    -moz-border-radius: 4px 4px 4px 4px;
    -moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    background: url("/euf/assets/themes/cfpb/images/buttonGradientCombo.png") repeat-x scroll 0 0 #6AC344;
    border: 1px solid #71BF4C;
    color: #FFFFFF;
    cursor: pointer;
    font: bold 13px Helvetica,Arial,sans-serif;
    margin-right: 6px;
    text-decoration: none;
    text-shadow: 2px 2px 2px rgba(0, 0, 0, 0.25);
    width: 53px;
}
.yui-skin-sam .yui-calcontainer .yui-cal-nav .yui-cal-nav-btn {
    width: 63px;
}
.yui-skin-sam .yui-calcontainer .yui-cal-nav .yui-cal-nav-btn button {
    color: #FFFFFF;
}
</style>
<!-- Dependencies -->
<script src="/rnt/rnw/yui_2.7/yahoo-dom-event/yahoo-dom-event.js"></script>
<!-- Source file -->
<script src="/rnt/rnw/yui_2.7/calendar/calendar-min.js"></script>

<div id="rn_<?=$this->instanceID;?>" class="rn_DateInput">
<fieldset>
<? if($this->data['attrs']['label_input']):?>
    <legend id="rn_<?=$this->instanceID;?>_Legend" class="rn_Label"><?=$this->data['attrs']['label_input'];?>
    <? if($this->data['attrs']['required']):?>
        <span class="rn_Required"> * </span><span class="rn_ScreenReaderOnly"><?=getMessage(REQUIRED_LBL)?></span>
    <? endif;?>
    </legend>
    <div class="note"><?=$this->data['attrs']['label_note'];?></div>
<? endif;?>

<div id="rn_<?=$this->instanceID?>_calendarContainer" style="<?=$this->data['attrs']['style_custom'];?>" class="calendarContainer">
<input type="text" id="rn_<?=$this->instanceID?>_dateString" class="rn_Text" value="" maxlength="10" style="float:left; width:100px;" readonly />
<img src="/euf/assets/themes/cfpb/images/intake/calendar-icon-hi.png" border="0" style="float: left; margin: -2px 10px 0 5px" id="rn_<?=$this->instanceID?>_calendarIcon" />
<div id="rn_<?=$this->instanceID?>_datePicker" class="rn_Hidden" style="float: left; display: none; left: 350px; position: absolute; z-index: 5;"></div>
</div>

<? if($this->field->data_type === EUF_DT_DATETIME):?>

    <?if($this->data['attrs']['hide_hours_mins']){?>
    <div class="rn_Hidden">
    <?}?>

    <? /**Hour*/ ?>
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>_Hour" class="rn_ScreenReaderOnly"><?=$this->data['hourLabel'];?></label>
    <select id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>_Hour" <?=tabIndex($this->data['attrs']['tabindex'], 1 + $i);?>>
        <option value=''>--</option>
        <? for($j = 0; $j < 24; $j++):?>
        <? if($this->data['defaultValue']) $selected = ($this->data['value'][3] == $j) ? 'selected="selected"' : '';?>
        <option value="<?=$j;?>" <?=$selected;?>><?=$j;?></option>
        <? endfor;?>
    </select>

    <? /**Minute*/ ?>
    <label for="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>_Minute" class="rn_ScreenReaderOnly"><?=$this->data['minuteLabel'];?></label>
    <select id="rn_<?=$this->instanceID;?>_<?=$this->data['js']['name'];?>_Minute" <?=tabIndex($this->data['attrs']['tabindex'], 2 + $i);?>>
        <option value=''>--</option>
        <? for($j = 0; $j < 60; $j++):?>
        <? if($this->data['defaultValue']) $selected = ($this->data['value'][4] == $j) ? 'selected="selected"' : '';?>
        <option value="<?=$j;?>" <?=$selected;?>><?=$j;?></option>
        <? endfor;?>
    </select>
    
    <?if($this->data['attrs']['hide_hours_mins']){?>
    </div>
    <?}?>
    
<? endif;?>
</fieldset>
</div>

<? endif;?>
