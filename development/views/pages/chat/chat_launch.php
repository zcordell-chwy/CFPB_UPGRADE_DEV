<rn:meta title="#rn:msg:LIVE_CHAT_LBL#" template="standard.php" clickstream="chat_request"/>

<div id="rn_PageTitle" class="rn_LiveHelp">
    <h1>#rn:msg:LIVE_HELP_HDG#</h1>
</div>

<div id="rn_PageContent" class="rn_Live">
    <div class="rn_Padding" >
        <div class="rn_ChatForm">
            <rn:widget path="chat/ChatLaunchFormOpen" label_form_header="#rn:msg:CHAT_MEMBER_OUR_SUPPORT_TEAM_LBL#"/>
            <div id="rn_ErrorLocation"></div>
                <rn:widget path="input/ContactNameInput" required="false" />
                <rn:widget path="input/FormInput" name="contacts.email" required="false" />
                <rn:widget path="input/ProductCategoryInput" table="incidents" show_confirm_button_in_dialog="true"/>
                <rn:widget path="input/ProductCategoryInput" table="incidents" data_type="categories" label_input="#rn:msg:CATEGORY_LBL#" label_nothing_selected="#rn:msg:SELECT_A_CATEGORY_LBL#" show_confirm_button_in_dialog="true"/>
                <!-- optional fields -->
                <rn:widget path="input/CustomAllInput" table="incidents" chat_visible_only="true" />
            <br />
            <rn:widget path="chat/ChatLaunchButton" error_location="rn_ErrorLocation" />
            <br /><br />
            </form>
       </div>
       <rn:widget path="chat/ChatStatus2"/>
       <rn:widget path="chat/ChatHours2"/>
    </div>
</div>


