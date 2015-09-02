<rn:meta controller_path="custom/instAgent/output/IncidentThreadDisplay"  
         presentation_css="widgetCss/IncidentThreadDisplay.css" 
         base_css="standard/output/IncidentThreadDisplay" 
         compatibility_set="November '09+"
         required_js_module="november_09,mobile_may_10,none"/>
<div id="rn_<?=$this->instanceID;?>" class="rn_IncidentThreadDisplay">
<?
if ($this->data['attrs']['label']): ?>
    <span class="rn_DataLabel"><?=$this->data['attrs']['label'];?> </span>
<?
endif;
?>
<? if($this->data['value']): ?>
<? foreach($this->data['value'] as $thread): ?>
    <? $subclass = '';
switch ($thread['entry_type']) {
case (3): case (4): $subclass = 'rn_Customer';
break;
}
?>
    <div class="rn_ThreadHeader <?=$subclass?>">
        <span class="rn_ThreadAuthor">
            <?=$thread['type']?>
            <? if($thread['name']) echo $thread['name'];
?>
            <? if($thread['channel_label']) printf(getMessage((2970)), $thread['channel_label']);
?>
        </span>
        <span class="rn_ThreadTime">
            <?=$thread['time'];?>
        </span>
    </div>
    <div class="rn_ThreadContent">
        <?=$thread['content'];?>
    </div>
<?
endforeach;
?>
<? endif;
?>
</div>
