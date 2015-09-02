<? header('Location: /app/error404'); ?>

<rn:meta title="#rn:msg:NOTIFICATIONS_HDG#" template="standard.php" login_required="true"/>
<div id="rn_PageTitle" class="rn_Account">
    <h1>#rn:msg:NOTIFICATIONS_HDG#</h1>
</div>
<div id="rn_PageContent">
    <div class="rn_Padding">
        <h2>#rn:msg:ANSWER_LBL# #rn:msg:NOTIFICATIONS_HDG#</h2>
        <rn:widget path="notifications/AnswerNotificationManager" />
        <h2>#rn:msg:PRODUCT_LBL#/#rn:msg:CATEGORY_LBL# #rn:msg:NOTIFICATIONS_HDG#</h2>
        <rn:widget path="notifications/ProdCatNotificationManager" />
    </div>
</div>
