<rn:meta controller_path="custom/notifications/SubscribeLink" js_path="custom/notifications/SubscribeLink" base_css="standard/notifications/ProdCatNotificationManager" presentation_css="widgetCss/ProdCatNotificationManager.css" compatibility_set="November '09+"/>

<div id="rn_<?=$this->instanceID;?>" class="rn_ProdCatNotificationManager">
    <div id="rn_<?=$this->instanceID;?>_Subscribe" class="rn_Hidden">
        <button id="rn_<?=$this->instanceID;?>_SubscribeButton" class="rn_AddButton">Subscribe</button>
        <span class="subscribe_detail">
            <?=getLabel('SUBSCRIBE_MSG');?>
        </span>
    </div>
    <div id="rn_<?=$this->instanceID;?>_UnSubscribe" class="rn_Hidden">
        <button id="rn_<?=$this->instanceID;?>_UnSubscribeButton" class="rn_AddButton">Unsubscribe</button>
        <span class="subscribe_detail">
            <?=getLabel('UNSUBSCRIBE_MSG');?>
        </span>
    </div>
</div>
