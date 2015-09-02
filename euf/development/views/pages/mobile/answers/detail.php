<rn:meta title="#rn:php:SEO::getDynamicTitle('answer', getUrlParm('a_id'))#" template="mobile.php" answer_details="true" clickstream="answer_view"/>
<section id="rn_PageTitle" class="rn_AnswerDetail">
    <h1 id="rn_Summary"><rn:field name="answers.summary" highlight="true"/></h1>
    <div id="rn_AnswerInfo">
        #rn:msg:PUBLISHED_LBL# <rn:field name="answers.created" />
        <br/>
        #rn:msg:UPDATED_LBL# <rn:field name="answers.updated" />
    </div>
    <rn:field name="answers.description" highlight="true"/>
</section>
<section id="rn_PageContent" class="rn_AnswerDetail">
    <div id="rn_AnswerText">
        <rn:field name="answers.solution" highlight="true"/>
    </div>
    <div id="rn_FileAttach">
        <rn:widget path="output/DataDisplay" name="answers.fattach" />
    </div>
    <rn:widget path="knowledgebase/GuidedAssistant" popup_window_url="/app/utils/guided_assistant"/>
    <br/>
    <rn:widget path="feedback/MobileAnswerFeedback" />
    <rn:widget path="knowledgebase/RelatedAnswers2" />
    <rn:widget path="utils/MobileEmailAnswerLink" label_link="#rn:msg:EMAIL_THIS_PAGE_ARROW_LBL#"/>
</section>
