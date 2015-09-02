<rn:meta javascript_module="mobile_may_10" clickstream="chat_landing" include_chat="true"/>
<!DOCTYPE html>
<html lang="#rn:language_code#">
    <head>
        <meta name="viewport" content="width=device-width; initial-scale=1.0; minimum-scale=1.0; maximum-scale=1.0; user-scalable=no;"/>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <title>#rn:msg:LIVE_ASSISTANCE_LBL#</title>
        <rn:theme path="/euf/assets/themes/mobile" css="site.css"/>
        <rn:head_content/>
        <link rel="icon" href="images/favicon.png" type="image/png">
    </head>
    <body>
        <noscript><h1>#rn:msg:SCRIPTING_ENABLED_SITE_MSG#</h1></noscript>
        <header>
            <rn:condition is_spider="false">
            <nav id="rn_Navigation">
                <span class="rn_FloatLeft">
                    <rn:widget path="navigation/MobileNavigationMenu" submenu="rn_MenuList"/>
                </span>
                <ul id="rn_MenuList" class="rn_Hidden">
                    <li>
                        <a href="/app/home#rn:session#">#rn:msg:HOME_LBL#</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="rn_ParentMenu">#rn:msg:CONTACT_US_LBL#</a>
                        <ul class="rn_Submenu rn_Hidden">
                            <li><a href="/app/chat/chat_launch#rn:session#">#rn:msg:CHAT_LBL#</a></li>
                            <li><a href="/app/ask#rn:session#">#rn:msg:EMAIL_US_LBL#</a></li>
                            <li><a href="javascript:void(0);">#rn:msg:CALL_US_DIRECTLY_LBL#</a></li>
                            <li><a href="javascript:void(0);">#rn:msg:ASK_THE_COMMUNITY_LBL#</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="rn_ParentMenu">#rn:msg:YOUR_ACCOUNT_LBL#</a>
                        <ul class="rn_Submenu rn_Hidden">
                            <rn:condition logged_in="false">
                            <li><a href="/app/utils/create_account#rn:session#">#rn:msg:SIGN_UP_LBL#</a></li>
                            <li><a href="/app/utils/login_form#rn:session#">#rn:msg:LOG_IN_LBL#</a></li>
                            <li><a href="/app/utils/account_assistance#rn:session#">#rn:msg:ACCOUNT_ASSISTANCE_LBL#</a></li>
                            </rn:condition>
                            <li><a href="/app/account/questions/list#rn:session#">#rn:msg:VIEW_YOUR_SUPPORT_HISTORY_CMD#</a></li>
                            <li><a href="/app/account/profile#rn:session#">#rn:msg:CHANGE_YOUR_ACCOUNT_SETTINGS_CMD#</a></li>
                        </ul>
                    </li>
                </ul>
                <span class="rn_FloatRight">
                    <rn:widget path="chat/ChatDisconnectButton"
                        close_icon_path=""
                        disconnect_icon_path=""
                        mobile_mode="true"/>
                </span>
                <span class="rn_FloatRight rn_Search">
                    <rn:widget path="chat/ChatCancelButton"/>
                </span>
            </nav>
            </rn:condition>
        </header>
        
        <div id="rn_ChatContainer">
            <a name="rn_MainContent" id="rn_MainContent"></a>
            <div id="rn_PageContent" class="rn_Live">
                <div id="rn_ChatDialogContainer">
                    <rn:widget path="chat/ChatServerConnect"/>
                    <rn:widget path="chat/ChatEngagementStatus"/>
                    <rn:widget path="chat/ChatQueueWaitTime" type="all"
                            label_estimated_wait_time_not_available=""
                            label_average_wait_time_not_available=""/>
                    <rn:widget path="chat/ChatAgentStatus"/>
                    <rn:widget path="chat/ChatTranscript" mobile_mode="true"/>
                    <div id="rn_PreChatButtonContainer">
                        <rn:widget path="chat/ChatRequestEmailResponseButton"/>
                    </div>
                    <rn:widget path="chat/ChatPostMessage" label_send_instructions="#rn:msg:TYPE_YOUR_MESSAGE_AND_SEND_LBL#" mobile_mode="true"/>
                    <div id="rn_InChatButtonContainer">
                        <rn:widget path="chat/ChatSendButton"/>
                    </div>
                </div>
            </div>
        </div>
        
        <footer>
            <rn:condition is_spider="false">
                <div>
                    <rn:condition logged_in="true">
                    <rn:field name="contacts.email"/><rn:widget path="login/LogoutLink2"/>
                    <rn:condition_else />
                    <a href="/app/utils/login_form#rn:session#">#rn:msg:LOG_IN_LBL#</a>
                    </rn:condition>
                    <br/><br/>
                </div>
                <rn:condition hide_on_pages="utils/guided_assistant">
                    <rn:widget path="utils/PageSetSelector"/>
                </rn:condition>
                <div class="rn_FloatLeft"><a href="javascript:window.scrollTo(0, 0);">#rn:msg:ARR_BACK_TO_TOP_LBL#</a></div>
            </rn:condition>
            <div class="rn_FloatRight">Powered by <a href="http://www.rightnow.com">RightNow</a></div>
            <br/><br/>
        </footer>
    </body>
</html>
