<?/* Remove PII from title. CR339 */?>
<rn:meta title="News Detail" template="cfpb.php" answer_details="true" clickstream="answer_view" login_required="true"/>

<div id="rn_PageTitle" class="rn_AnswerDetail">
    <h6 id="rn_Summary"><rn:field name="answers.summary" highlight="true"/></h6>
    <div id="rn_AnswerInfo">
        #rn:msg:PUBLISHED_LBL# <rn:field name="answers.created" />
        &nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        #rn:msg:UPDATED_LBL# <rn:field name="answers.updated" />
    </div>
    <rn:field name="answers.description" highlight="true"/>
</div>
<div id="rn_PageContent" class="rn_AnswerDetail">
    <div id="rn_AnswerText">
        <rn:field name="answers.solution" highlight="true"/>
    </div>
    <div id="rn_FileAttach">
        <rn:widget path="output/DataDisplay" name="answers.fattach" />
    </div>
    <br/>
    <?/*
    <rn_:widget path="knowledgebase/RelatedAnswers2" />
    <rn_:widget path="knowledgebase/PreviousAnswers2" />
    */?>
    <rn:condition is_spider="false">
        <div id="rn_DetailTools">
            <rn:widget path="utils/PrintPageLink" />
        </div>
    </rn:condition>
</div>
