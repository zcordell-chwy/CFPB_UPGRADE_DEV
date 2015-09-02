<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<rn:meta clickstream="chat_landing" include_chat="true"/>

<html xmlns="http://www.w3.org/1999/xhtml" lang="#rn:language_code#" xml:lang="#rn:language_code#">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="X-UA-Compatible" content="chrome=1" />
    <title>#rn:msg:LIVE_ASSISTANCE_LBL#</title>
    <rn:theme path="/euf/assets/themes/standard" css="site.css,/rnt/rnw/yui_2.7/container/assets/skins/sam/container.css" />
    <rn:head_content/>

</head>
<body class="yui-skin-sam">
    <div id="rn_ChatContainer">
        <a name="rn_MainContent" id="rn_MainContent"></a>
        <div id="rn_PageContent" class="rn_Live">
            <div class="rn_Padding" >
                <div id="rn_ChatDialogContainer">
                    <rn:widget path="chat/ChatOffTheRecordDialog"/>
                    <div id="rn_ChatDialogHeaderContainer">
                        <div id="rn_ChatDialogTitle" class="rn_FloatLeft"><h3>#rn:msg:CHAT_LBL#</h3></div>
                        <div id="rn_ChatDialogHeaderButtonContainer">
                            <rn:widget path="chat/ChatDisconnectButton"/>
                            <rn:widget path="chat/ChatOffTheRecordButton"/>
                            <rn:widget path="chat/ChatPrintButton"/>
                            <rn:widget path="chat/ChatSoundButton"/>
                        </div>
                    </div>
                    <rn:widget path="chat/ChatServerConnect"/>
                    <rn:widget path="chat/ChatEngagementStatus"/>
                    <rn:widget path="chat/ChatQueueWaitTime" type="all"
                            label_estimated_wait_time_not_available=""
                            label_average_wait_time_not_available=""/>
                    <rn:widget path="chat/ChatAgentStatus"/>
                    <rn:widget path="chat/ChatTranscript"/>
                    <div id="rn_PreChatButtonContainer">
                        <rn:widget path="chat/ChatCancelButton"/>
                        <rn:widget path="chat/ChatRequestEmailResponseButton"/>
                    </div>
                    <rn:widget path="chat/ChatPostMessage"/>
                    <div id="rn_InChatButtonContainer">
                        <rn:widget path="chat/ChatSendButton"/>
                        <rn:widget path="chat/ChatAttachFileButton"/>
                        <rn:widget path="chat/ChatCoBrowseButton"/>
                    </div>
                    <div id="rn_ChatQueueSearchContainer">
                        <rn:widget path="chat/ChatQueueSearch" popup_window="true"/>
                    </div>
                </div>
            </div>
        </div>
        <div id="rn_ChatFooter">
            <div class="rn_FloatRight">
                <rn:widget path="utils/RightNowLogo"/>
            </div>
        </div>
    </div>
</body>
</html>
