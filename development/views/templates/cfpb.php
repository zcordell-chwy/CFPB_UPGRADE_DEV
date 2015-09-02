<? header("X-UA-Compatible: IE=Edge"); ?>
<?$this->load->helper('config_helper');?>
<?$this->load->helper('label_helper');?>
<?$this->load->helper('custom_conditions_helper');?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if IE 6 ]> <html class="ie6" xmlns="http://www.w3.org/1999/xhtml" lang="#rn:language_code#" xml:lang="#rn:language_code#"> <![endif]-->
<!--[if IE 7 ]> <html class="ie7" xmlns="http://www.w3.org/1999/xhtml" lang="#rn:language_code#" xml:lang="#rn:language_code#"> <![endif]-->
<!--[if IE 8 ]> <html class="ie8" xmlns="http://www.w3.org/1999/xhtml" lang="#rn:language_code#" xml:lang="#rn:language_code#"> <![endif]-->
<!--[if IE 9 ]> <html class="ie9" xmlns="http://www.w3.org/1999/xhtml" lang="#rn:language_code#" xml:lang="#rn:language_code#"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html xmlns="http://www.w3.org/1999/xhtml" lang="#rn:language_code#" xml:lang="#rn:language_code#"> <!--<![endif]-->
<head>
    <meta charset="UTF-8" />
        <meta property="og:title" content="Get Help Now &#8211; Consumer Financial Protection Bureau"/>
        <meta property="og:type" content="government"/>
        <meta property="og:url" content="https://secure.consumerfinance.org/"/>
        <meta property="og:site_name" content="Consumer Financial Protection Bureau"/>
        <meta property="fb:page_id" content="141576752553390" />
        <meta property="fb:app_id" content="210516218981921" />
        <link rel="shortcut icon" href="/euf/assets/themes/cfpb/images/favicona.png">
        <link rel="apple-touch-icon" href="/euf/assets/themes/cfpb/images/faviconb.png">

    <rn:theme path="/euf/assets/themes/cfpb" css="site_rnt.css,/rnt/rnw/yui_2.7/container/assets/skins/sam/container.css" />
    <rn:head_content/>
    <title><?=getLabel('COMPANY_PORTAL_LBL');?> - <rn:page_title/></title>

    <link rel="stylesheet" type="text/css" media="all" href="/euf/assets/themes/cfpb/style.css" />
    <!-- this is the style.css file in the root directory -->

    <!--[if IE]>
        <link rel="stylesheet" type="text/css" media="all" href="/euf/assets/themes/cfpb/css/ie.css" />
    <![endif]-->
    <!--[if IE 6]>
      <link rel="stylesheet" type="text/css" href="/euf/assets/themes/cfpb/css/ie6.css" />
    <![endif]-->
    <!--[if IE 8]>
      <link rel="stylesheet" type="text/css" href="/euf/assets/themes/cfpb/css/ie8.css" />
    <![endif]-->

    <script type="text/javascript" src="/euf/assets/themes/cfpb/js/jquery.min.js"></script>
    <script type="text/javascript" src="/euf/assets/themes/cfpb/js/clean.js"></script>
    <script type="text/javascript" src="/euf/assets/themes/cfpb/js/clean_rnt.js"></script>
    <rn:condition show_on_pages="ask_cc_complaint">
    <!-- Script to enable back/forward buttons on the ask_cc_complaint page -->
    <script type="text/javascript" src="/euf/assets/themes/cfpb/js/jquery.ba-bbq.min.js"></script>
    </rn:condition>

    <rn:condition logged_in="true">
        <rn:condition hide_on_pages="account/exports/list,account/exports/detail">
            <rn:widget path="custom/export/JobNotification" />
        </rn:condition>
    </rn:condition>

</head>
<body class="home yui-skin-sam page page-id-12 page-parent page-template page-template-default">
<div class="skip"><a href="#content">skip to page content</a></div>
<div id="head_wrapper" class="page2-body">

    <div class="nav">
      <?/* display announcement */?>
      <rn:condition logged_in="true">
        <div class="ps_Announcement">
            <rn:widget path="reports/Multiline2" report_id="#rn:php:getSetting('ANNOUNCEMENTS_REPORT_ID')#" headers="false" hide_when_no_results="true" per_page="1" truncate_size="260"/>
        </div>
      </rn:condition>

        <ul id="most_top_nav">
            <li><a href="http://www.consumerfinance.gov" id="CFPBlogo"><img src="/euf/assets/themes/cfpb/images/wV2-logo.png" /></a></li>
        </ul>
    </div>


</div>

<div id="container">
  <div id="topglow">
    <a name="content" id="content"></a>
    <div id="pageBox" class="page-boxv2">
        <div id="pageBody" class="page-bodyv2">
            <?/*
            <div id="breadcrumbs" class="page-box" >
              <div class="breadcrumbs ">
                <ul>
                <li><a href="http://www.consumerfinance.gov">Home</a></li>
                <li><span id="page_title"><rn:page_title/></span></li>
                </ul>
              </div>
            </div><!-- end pageBody -->
            */?>
      <div id="main_content2" class="one_column">

        <rn:condition is_spider="false">
          <rn:condition logged_in="true">
          <div id="ps_StatusNav">
            <div id="rn_LoginStatus">
                     #rn:msg:WELCOME_BACK_LBL#
                    <strong>
                        <rn:field name="contacts.full_name"/>
                    </strong>
                    <rn:field name="contacts.organization_name"/>
                    <rn:widget path="custom/login/LogoutLink2" redirect_url="/app/utils/login_form" />
            </div>

            <?/* display top nav */?>
            <div id="rn_Navigation">
              <div id="rn_NavigationBar" role="navigation">
                <ul>
                <li><rn:widget path="navigation/NavigationTab2"
                    label_tab="#rn:php:getLabel('HOME_LBL')#" link="/"
                    pages="instAgent/list_active, instAgent/list_review, instAgent/list_archive, government/active/list, government/active/detail, government/closed/list, government/closed/detail"/></li>
                <li><rn:widget path="navigation/NavigationTab2"
                    label_tab="#rn:php:getLabel('HELP_LBL')#" link="/app/answers/help/list"
                    pages="ask, report_issue, ask_confirm, answers/help/list, answers/help/detail, answers/help/listnews, answers/help/detailnews, answers/top10list"/>
                    <?/*subpages="#rn:php:getLabel('FAQ_LBL')# > /app/answers/list,
                        #rn:php:getLabel('HELP_ASK')# > /app/ask,
                        #rn:php:getLabel('HELP_REPORT')# > /app/report_issue"*/?></li>
<!--                <li><rn:widget path="navigation/NavigationTab2"
                    label_tab="#rn:php:getLabel('NEWS_LBL')#" link="/app/news/list"
                    pages="news/list, news/detail"/></li>-->
                <li><rn:widget path="navigation/NavigationTab2"
                    label_tab="#rn:php:getLabel('TRAINING_LBL')#" link="/app/answers/training/traininglist"
                    pages="answers/training/traininglist, answers/training/trainingdetail, answers/training/trainingdocslist"/>
                <li><rn:widget path="navigation/NavigationTab2"
                    label_tab="#rn:msg:SUPPORT_HISTORY_LBL#" link="/app/account/questions/list"
                    pages="account/questions/list, account/questions/detail"/></li>
                   
                 </li> 
                 <rn:widget path="custom/export/Navigation" />
                </ul>
              </div>
            </div>

          </div>
          </rn:condition>
        </rn:condition>

        <div class="column colfull">
          <div id="rn_Body">

            <?/* show two column layout for certain pages */?>
            <rn:condition show_on_pages="answers/top10list, answers/help/list, answers/help/detail, answers/help/listnews, answers/help/detailnews, ask, report_issue">
              <div id="rn_MainColumn" role="main">
            <rn:condition_else />
              <div id="xrn_MainColumn" role="main">
            </rn:condition>
                <a name="rn_MainContent" id="rn_MainContent"></a>
                <rn:page_content/>
                <br/>
                <br/>
              </div>

            <rn:condition show_on_pages="answers/top10list, answers/help/list, answers/help/listnews, answers/detail, answers/help/detailnews, ask, report_issue">
            <div id="rn_SideBar" role="navigation">
                <div class="rn_Padding">
                <?/*
                    <rn:condition hide_on_pages="answers/list, home, account/questions/list">
                    <div class="rn_Module" role="search">
                        <h2>#rn:msg:FIND_ANS_HDG#</h2>
                        <rn:widget path="search/SimpleSearch"/>
                    </div>
                    </rn:condition>
                */?>
<?/*                    <div class="rn_Module">
                        <h2><?=getLabel('TRAINING_CENTER_LBL');?></h2>
                        <rn:widget path="custom/instAgent/reports/StaticReport" report_id="#rn:php:getSetting('QUICK_LINK_REPORT_ID')#" headers="false" hide_when_no_results="true" per_page="3" highlight="false" />
                    </div>
                    */?>
                    <div class="rn_Module">
                        <h2>#rn:msg:CONTACT_US_LBL#</h2>
                        <div class="rn_HelpResources">
                            <div class="rn_Questions">
                                <a href="/app/ask#rn:session#">#rn:msg:ASK_QUESTION_LBL#</a>
                                <span>#rn:msg:SUBMIT_QUESTION_OUR_SUPPORT_TEAM_CMD#</span>
                            </div>
                            <?php /*<div class="rn_Contact">
                                <a href="/app/report_issue#rn:session#"><?=getLabel('HELP_REPORT');?></a>
                                <span></span>
                            </div> */ ?>
                            <?/*<div class="rn_Feedback">
                                <rn_:widget path="feedback/SiteFeedback2" />
                                <span>#rn:msg:SITE_USEFUL_MSG#</span>
                            </div>*/?>
                        </div>
                    </div>
                </div>
            </div>
            </rn:condition>

          </div>
        </div><!--/section-->
        <div class="clear-both"></div>
      </div><!--/main_content-->

    </div> <!-- end pageBox-->
  </div><!-- end topglow -->

</div><!-- end Container -->
</body>
</html>

