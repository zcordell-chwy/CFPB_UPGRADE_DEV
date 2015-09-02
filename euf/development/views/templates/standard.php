<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="#rn:language_code#" xml:lang="#rn:language_code#">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="X-UA-Compatible" content="chrome=1" />
    <title><rn:page_title/></title>
    <rn:widget path="search/BrowserSearchPlugin" pages="home, answers/list, answers/detail" />
    <rn:theme path="/euf/assets/themes/standard" css="site.css,/rnt/rnw/yui_2.7/container/assets/skins/sam/container.css" />
    <rn:head_content/>
    <link rel="icon" href="images/favicon.png" type="image/png"/>
</head>
<body class="yui-skin-sam">
<div id="rn_Container" >
    <div id="rn_SkipNav"><a href="#rn_MainContent">#rn:msg:SKIP_NAVIGATION_CMD#</a></div>
    <div id="rn_Header" role="banner">
    <noscript><h1>#rn:msg:SCRIPTING_ENABLED_SITE_MSG#</h1></noscript>
        <div id="rn_Logo"><a href="/app/home#rn:session#"><span class="rn_LogoTitle">#rn:msg:SUPPORT_LBL# <span class="rn_LogoTitleMinor">#rn:msg:CENTER_LBL#</span></span></a></div>
        <rn:condition is_spider="false">
            <div id="rn_LoginStatus">
                <rn:condition logged_in="true">
                     #rn:msg:WELCOME_BACK_LBL#
                    <strong>
                        <rn:field name="contacts.full_name"/>
                    </strong>
                    <div>
                        <rn:field name="contacts.organization_name"/>
                    </div>
                    <rn:widget path="login/LogoutLink2"/>
                <rn:condition_else />
                    <a href="javascript:void(0);" id="rn_LoginLink">#rn:msg:LOG_IN_LBL#</a>&nbsp;|&nbsp;<a href="/app/utils/create_account#rn:session#">#rn:msg:SIGN_UP_LBL#</a>
                    <rn:condition hide_on_pages="utils/create_account, utils/login_form, utils/account_assistance">
                        <rn:widget path="login/LoginDialog2" trigger_element="rn_LoginLink" open_login_url="/app/utils/login_form" label_open_login_link="#rn:msg:LOG_EXISTING_ACCOUNTS_LBL# <span class='rn_ScreenReaderOnly'>(Facebook, Twitter, Google, OpenID) #rn:msg:CONTINUE_FOLLOWING_FORM_LOG_CMD#</span>"/>
                    </rn:condition>
                    <rn:condition show_on_pages="utils/create_account, utils/login_form, utils/account_assistance">
                        <rn:widget path="login/LoginDialog2" trigger_element="rn_LoginLink" redirect_url="/app/account/overview" open_login_url="/app/utils/login_form" label_open_login_link="#rn:msg:LOG_EXISTING_ACCOUNTS_LBL# <span class='rn_ScreenReaderOnly'>(Facebook, Twitter, Google, OpenID) #rn:msg:CONTINUE_FOLLOWING_FORM_LOG_CMD#</span>"/>
                    </rn:condition>
                </rn:condition>
            </div>
        </rn:condition>
    </div>
    <div id="rn_Navigation">
    <rn:condition hide_on_pages="utils/help_search">
        <div id="rn_NavigationBar" role="navigation">
            <ul>
                <li><rn:widget path="navigation/NavigationTab2" label_tab="#rn:msg:SUPPORT_HOME_TAB_HDG#" link="/app/home" pages="home, "/></li>
                <li><rn:widget path="navigation/NavigationTab2" label_tab="#rn:msg:ANSWERS_HDG#" link="/app/answers/list" pages="answers/list, answers/detail"/></li>
                <rn:condition config_check="RNW:COMMUNITY_ENABLED == true">
                    <li><rn:widget path="navigation/NavigationTab2" label_tab="#rn:msg:COMMUNITY_LBL#" link="#rn:php:getConfig(COMMUNITY_HOME_URL, 'RNW')#" external="true"/></li>
                </rn:condition>
                <li><rn:widget path="navigation/NavigationTab2" label_tab="#rn:msg:ASK_QUESTION_HDG#" link="/app/ask" pages="ask, ask_confirm"/></li>
                <li><rn:widget path="navigation/NavigationTab2" label_tab="#rn:msg:YOUR_ACCOUNT_LBL#" link="/app/account/overview" pages="utils/account_assistance, account/overview, account/profile, account/notif, account/change_password, account/questions/list, account/questions/detail, account/notif/list, utils/login_form, utils/create_account, utils/submit/password_changed, utils/submit/profile_updated"
                subpages="#rn:msg:ACCOUNT_OVERVIEW_LBL# > /app/account/overview, #rn:msg:SUPPORT_HISTORY_LBL# > /app/account/questions/list, #rn:msg:ACCOUNT_SETTINGS_LBL# > /app/account/profile, #rn:msg:NOTIFICATIONS_LBL# > /app/account/notif/list"/></li>
            </ul>
        </div>
    </rn:condition>
    </div>
    <div id="rn_Body">
        <div id="rn_MainColumn" role="main">
            <a name="rn_MainContent" id="rn_MainContent"></a>
            <rn:page_content/>
        </div>
        <rn:condition is_spider="false">
            <div id="rn_SideBar" role="navigation">
                <div class="rn_Padding">
                    <rn:condition hide_on_pages="answers/list, home, account/questions/list">
                    <div class="rn_Module" role="search">
                        <h2>#rn:msg:FIND_ANS_HDG#</h2>
                        <rn:widget path="search/SimpleSearch"/>
                    </div>
                    </rn:condition>
                    <div class="rn_Module">
                        <h2>#rn:msg:CONTACT_US_LBL#</h2>
                        <div class="rn_HelpResources">
                            <div class="rn_Questions">
                                <a href="/app/ask#rn:session#">#rn:msg:ASK_QUESTION_LBL#</a>
                                <span>#rn:msg:SUBMIT_QUESTION_OUR_SUPPORT_TEAM_CMD#</span>
                            </div>
                        <rn:condition config_check="RNW:COMMUNITY_ENABLED == true">
                            <div class="rn_Community">
                                <a href="javascript:void(0);">#rn:msg:ASK_THE_COMMUNITY_LBL#</a>
                                <span>#rn:msg:SUBMIT_QUESTION_OUR_COMMUNITY_CMD#</span>
                            </div>
                        </rn:condition>
                        <rn:condition config_check="COMMON:MOD_CHAT_ENABLED == true">
                            <div class="rn_Chat">
                                <a href="/app/chat/chat_launch#rn:session#">#rn:msg:LIVE_CHAT_LBL#</a>
                                <span>#rn:msg:CHAT_DIRECTLY_SUPPORT_TEAM_MSG#</span>
                            </div>
                        </rn:condition>
                            <div class="rn_Contact">
                                <a href="javascript:void(0);">#rn:msg:CONTACT_US_LBL#</a>
                                <span>#rn:msg:CANT_YOURE_LOOKING_SITE_CALL_MSG#</span>
                            </div>
                            <div class="rn_Feedback">
                                <rn:widget path="feedback/SiteFeedback2" />
                                <span>#rn:msg:SITE_USEFUL_MSG#</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </rn:condition>
    </div>
    <div id="rn_Footer" role="contentinfo">
        <div id="rn_RightNowCredit">
            <div class="rn_FloatRight">
                <rn:widget path="utils/RightNowLogo"/>
            </div>
        </div>
    </div>
</div>
</body>
</html>
