RightNow.Widget.LoginDialog2 = function(data, instanceID){
    this.data = data;
    this.instanceID = instanceID;
    this._dialog = this._keyListener = null;
    this._errorDisplay = document.getElementById("rn_" + this.instanceID + "_ErrorMessage");
    this._container = document.getElementById("rn_" + this.instanceID);

    if(this._container)
    {
        if(document.getElementById(this.data.attrs.trigger_element))
            YAHOO.util.Event.addListener(this.data.attrs.trigger_element, "click", this._onLoginTriggerClick, null, this);
        else
            RightNow.UI.addDevelopmentHeaderError("Error with LoginDialog2 widget, trigger_element attribute value was set to '" + this.data.attrs.trigger_element + "', but an element with that ID doesn't exist on the page.");
        RightNow.Event.subscribe("evt_loginFormSubmitResponse", this._onResponseReceived, this);
    }
};

RightNow.Widget.LoginDialog2.prototype = {
    /**
     * Event handler for when login control is clicked
     * @param type Object The event type
     * @param arg Object The event arguments
     */
    _onLoginTriggerClick: function(type, arg){
        if(!this._dialog) {
            this._dialog = RightNow.UI.Dialog.actionDialog(this.data.attrs.label_dialog_title, 
                                document.getElementById("rn_" + this.instanceID), 
                                {buttons: [{text: this.data.attrs.label_login_button, handler: {fn:this._onSubmit, scope:this}},
                                           {text: this.data.attrs.label_cancel_button, handler: {fn:this._onCancel, scope:this}}]}
            );
            // Set up keylistener for <enter> to run onSubmit()
            this._keyListener = RightNow.UI.Dialog.addDialogEnterKeyListener(this._dialog, this._onSubmit, this);
            //override default YUI validation to return false: don't want YUI to try to submit the form
            this._dialog.validate = function() { return false; };
            YAHOO.util.Dom.removeClass("rn_" + this.instanceID, 'rn_Hidden');
            //Perform dialog close cleanup if the [x] cancel button or esc is used
            this._dialog.cancelEvent.subscribe(this._onCancel, null, this);
        }
        else if(this._errorDisplay)
        {
            this._errorDisplay.innerHTML = "";
        }
        
        this._dialog.show();        
        
        this._usernameField = this._usernameField || document.getElementById("rn_" + this.instanceID + "_Username");
        this._passwordField = this._passwordField || document.getElementById("rn_" + this.instanceID + "_Password");
        
        // Focus the open login link, if it exists; otherwise focus first empty-value input field
        var openLoginLink = document.getElementById("rn_" + this.instanceID + "_OpenLoginLink"),
            focusElement = openLoginLink || ((this._usernameField && this._usernameField.value === '') ? this._usernameField : this._passwordField);
        if(focusElement)
        {
            RightNow.UI.Dialog.enableDialogControls(this._dialog, this._keyListener, focusElement);
            focusElement.focus();  // Do this again so it works in IE.  Yes, really.
        }
    },

    /**
     *   User cancelled. Close up shop.
     */
    _onCancel: function(type, arg){
        // Get rid of any existing error message, so it's gone if the user opens the dialog again.
        if(this._errorDisplay)
        {
            this._errorDisplay.innerHTML = "";
            YAHOO.util.Dom.removeClass(this._errorDisplay, 'rn_MessageBox rn_ErrorMessage');
        }
        // disable the key listener & buttons
        if(this._dialog)
        {
            RightNow.UI.Dialog.disableDialogControls(this._dialog, this._keyListener);
            this._dialog.hide();
        }
    },

    /**
     * Event handler for when login form is submitted
     * @param type String The type of event
     * @param args Object The arguments to the event
     */
    _onSubmit: function(type, args){
        //Don't submit if they are using the enter key on certain elements
        if(type === "keyPressed" && (YAHOO.util.Event.getTarget(args[1]).tagName === 'A' || YAHOO.util.Event.getTarget(args[1]).innerHTML === this.data.attrs.label_login_button || YAHOO.util.Event.getTarget(args[1]).innerHTML === this.data.attrs.label_cancel_button))
            return;

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
     * Event handler for when login has returned. Handles either successful login or failed login
     * @param type String Event name
     * @param arg Object Event arguments
     */
    _onResponseReceived: function(type, result){
        result = result[0];
        new YAHOO.util.Anim("rn_" + this.instanceID + "_Content", { opacity: { to: 1 } }, 0.5, YAHOO.util.Easing.easeIn).animate();
        YAHOO.util.Dom.removeClass("rn_" + this.instanceID, 'rn_ContentLoading');
        if(this.instanceID == result.w_id)
        {
            if(result.success == 1)
            {
                this._dialog.setFooter("");
                if(this._container)
                    document.getElementById("rn_" + this.instanceID).innerHTML = result.message;
                RightNow.Url.navigate(this._getRedirectUrl(result));
            }
            else
            {
                this._addErrorMessage(result.message, 'rn_' + this.instanceID + '_Username', result.showLink);
            }
        }
    },

    /**
     * Calculates the URL to redirect the user to after a login
     * @param result Object The result information returned from the server
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
     * Adds an error message to the page and adds the correct CSS classes
     * @param message string The error message to display
     * @param focusElement HTMLElement The HTML element to focus on when the error message link is clicked
     * @param showLink Boolean Denotes if error message should be surrounded in a link tag
     */
    _addErrorMessage: function(message, focusElement, showLink){
        if(this._errorDisplay)
        {
            YAHOO.util.Dom.addClass(this._errorDisplay, 'rn_MessageBox rn_ErrorMessage');
            //add link to message so that it can receive focus for accessibility reasons
            if(showLink === false)
            {
                this._errorDisplay.innerHTML = message;
            }
            else
            {
                this._errorDisplay.innerHTML = '<a href="javascript:void(0);" onclick="document.getElementById(\'' + focusElement + '\').focus(); return false;">' + message + '</a>';
                this._errorDisplay.firstChild.focus();
            }
        }
    }
};
