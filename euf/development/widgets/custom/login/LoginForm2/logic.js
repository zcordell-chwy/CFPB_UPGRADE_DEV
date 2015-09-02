RightNow.Widget.LoginForm2 = function(data, instanceID){
    this.data = data;
    this.instanceID = instanceID;
    if(document.getElementById("rn_" + this.instanceID))
    {
        YAHOO.util.Event.addListener("rn_" + this.instanceID + "_Submit", "click", this._onSubmit, null, this);
		RightNow.Event.subscribe("on_before_ajax_request", this._onBeforeAjaxRequest, this);
        RightNow.Event.subscribe("evt_loginFormSubmitResponse", this._onLoginResponse, this);
        this._usernameField = document.getElementById("rn_" + this.instanceID + "_Username");
        this._passwordField = document.getElementById("rn_" + this.instanceID + "_Password");
        this._acceptAndContinue = document.getElementById("rn_" + this.instanceID + "_acceptAndContinue");
		this._userType = "unauthorized";
        if(this._usernameField)
        {
            if(this._usernameField.value !== '')
            {
                if(this.data.attrs.initial_focus && this._passwordField)
                    this._passwordField.focus();
            }
            else if(this.data.attrs.initial_focus)
            {
                this._usernameField.focus();
            }
        }
    }
};
RightNow.Widget.LoginForm2.prototype = {

    /**
     * Function used to parse out the URL where we should redirect to
     * after a successful login
     * @param result Object The response object returned from the server
      * @return String The URL to redirect to
      */
     _getRedirectUrl: function(result){
         var redirectUrl;
         if(this.data.js && this.data.js.redirectOverride)
             redirectUrl = RightNow.Url.addParameter(this.data.js.redirectOverride, 'session', result.sessionParm.substr(result.sessionParm.lastIndexOf("/") + 1));
         else
             redirectUrl = (this.data.attrs.redirect_url || result.url) + ((result.addSession) ? result.sessionParm : "");

         return redirectUrl + this.data.attrs.append_to_url;
     },

    /**
     * Event handler for when login has returned. Handles either successful login or failed login
     * @param type String Event name
     * @param arg Object Event arguments
     */
    _onLoginResponse : function(type, result)
    {
        result = result[0];
        var body = document.getElementById("rn_" + this.instanceID + "_Content");
        new YAHOO.util.Anim(body, { opacity: { to: 1 } }, 0.5, YAHOO.util.Easing.easeIn).animate();
        YAHOO.util.Dom.removeClass("rn_" + this.instanceID, 'rn_ContentLoading');
        if(this.instanceID == result.w_id)
        {
	    this._userType = result.userType;
            if(result.success == 1)
            {
                if( result.user_type !== 'congressional' )
                {
                    if( body )
                        body.innerHTML = result.message;

                    RightNow.Url.navigate(this._getRedirectUrl(result));
                }
                else
                {
                    // Hide the form contents and submit button - we only need the disclaimer now that we're logged in.
                    YAHOO.util.Dom.addClass( 'rn_' + this.instanceID + '_FormContent', 'rn_Hidden' );
                    YAHOO.util.Dom.addClass( 'rn_' + this.instanceID + '_SubmitWrapper', 'rn_Hidden' );
                    YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_acceptAndContinueWrapper", 'rn_Hidden');

                    // Congressional Portal users must accept a disclaimer before they're allowed to continue.
                    YAHOO.util.Event.addListener( 
                        'rn_' + this.instanceID + '_DisclaimerSubmit', 
                        'click', 
                        function()
                        {
                            var disclaimer = YAHOO.util.Dom.get( 'rn_' + this.instanceID + '_acceptAndContinue' );
                            if( disclaimer && disclaimer.checked === true )
                            {
                                window.onbeforeunload = null;
                                RightNow.Url.navigate(this._getRedirectUrl(result));
                            }
                        },
                        null,
                        this
                    );
                    
                    // Add onbeforeunload event handler in case they try to bail before accepting the required disclaimer.
                    window.onbeforeunload = function( e )
                    {
                        // Log the user out in this case. We'll get rid of this event handler if they accept the disclaimer.
                        var eo = new RightNow.Event.EventObject();
                        eo.data.currentUrl = '/app/utils/login_form';
                        eo.data.redirectUrl = '/';
                        RightNow.Event.fire( 'evt_logoutRequest', eo );

                        return "Your current session will be ended.";
                    };
                }
            }
            else
            {
                this._addErrorMessage(result.message, 'rn_' + this.instanceID + '_Username', result.showLink);
            }
        }
    },

	/**
	 * Event handler to redirect ajax request to log in the user.
	 */
    _onBeforeAjaxRequest: function (type, args) {
		if (args[0].url == "/ci/ajaxRequest/doLogin") {
			args[0].url = "/cc/ajaxCustom/doLogin";
		}
		args[0].acceptAndContinue = 1;
	},

    /**
     * Event handler for when login button is clicked
     */
    _onSubmit : function()
    {
        var error = document.getElementById("rn_" + this.instanceID + "_ErrorMessage");
        if(error){
                error.innerHTML = "";
                YAHOO.util.Dom.removeClass(error, 'rn_MessageBox');
                YAHOO.util.Dom.removeClass(error, 'rn_ErrorMessage');
                YAHOO.util.Dom.addClass(error, 'rn_Hidden');
        }

        var eo = new RightNow.Event.EventObject();
        eo.w_id = this.instanceID;
        eo.data.username = (this._usernameField) ? YAHOO.lang.trim(this._usernameField.value) : "";
        var errorMessage = "";
        if(eo.data.username.indexOf(' ') > -1)
            errorMessage = RightNow.Interface.getMessage("USERNAME_FIELD_CONTAIN_SPACES_MSG");
        else if(eo.data.username.indexOf("'") > -1 || eo.data.username.indexOf('"') > -1)
            errorMessage = RightNow.Interface.getMessage("USERNAME_FIELD_CONT_QUOTE_CHARS_MSG");

        if(errorMessage !== "")
        {
            this._addErrorMessage(errorMessage, 'rn_' + this.instanceID + '_Username');
            return false;
        }
        if(!this.data.attrs.disable_password && this._passwordField)
            eo.data.password = this._passwordField.value;
        else
            eo.data.password = "";
        eo.data.url = window.location.pathname;

        eo.data.acceptAndContinue = this._acceptAndContinue?this._acceptAndContinue:0;

        RightNow.Event.fire("evt_loginFormSubmitRequest", eo);
        new YAHOO.util.Anim("rn_" + this.instanceID + "_Content", { opacity: { to: 0 } }, 0.5, YAHOO.util.Easing.easeOut).animate();
        YAHOO.util.Dom.addClass("rn_" + this.instanceID, 'rn_ContentLoading');
        //since this form is submitted by script, force ie to do auto_complete
        if(YAHOO.env.ua.ie > 0)
        {
            if(window.external && "AutoCompleteSaveForm" in window.external)
            {
                var form = document.getElementById("rn_" + this.instanceID + "_Form");
                if(form)
                    window.external.AutoCompleteSaveForm(form);
            }
        }
        return false;
    },

    /**
     * Utility function to display an error message
     * @param message String  The error message to display
     * @param focusElement String The ID of the element to focus when clicking on the error message
     * @param showLink [optional] Boolean Denotes if error message should be surrounded in a link tag
     */
    _addErrorMessage: function(message, focusElement, showLink){
        var error = document.getElementById("rn_" + this.instanceID + "_ErrorMessage");
        if(error)
        {
            YAHOO.util.Dom.removeClass(error, 'rn_Hidden');
            YAHOO.util.Dom.addClass(error, 'rn_MessageBox rn_ErrorMessage');
            //add link to message so that it can receive focus for accessibility reasons
            if(showLink === false)
            {
                error.innerHTML = message;
            }
            else
            {
                error.innerHTML = '<span href="javascript:void(0);" onclick="document.getElementById(\'' + focusElement + '\').focus(); return false;">' + message + '</span>';
                error.firstChild.focus();
            }
        }
    }
};