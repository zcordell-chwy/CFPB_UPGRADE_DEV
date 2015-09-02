RightNow.Widget.DirectedMessageInput = function(data, instanceID) {
    this.data = data;
    this.instanceID = instanceID;
    this._formErrorLocation = null;
    this._validated = false;

    this._inputField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name);
    this._countField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_count");
    this._reviewField = null;

    if(!this._inputField) return;

    // subscribe to the keydown/keyup events
    if (this.data.attrs.max_chars && this.data.attrs.max_chars > 0) {
        YAHOO.util.Event.on(this._inputField, "keydown", this._countChars, null, this);
        YAHOO.util.Event.on(this._inputField, "keyup", this._countChars, null, this);
        YAHOO.util.Event.on(this._inputField, "change", this._countChars, null, this);
    }

    //YAHOO.util.Event.on(this._inputField, "blur", this._onLabelHandlerBlur, null, this);
    //YAHOO.util.Event.on(this._inputField, "focus", this._onLabelHandlerFocus, null, this);
    //this._onLabelHandlerBlur(); // will set all the labels programtically - real labels are still in document, just hidden with CSS; this allows the validation dialog to continue working without modification

    if(this.data.attrs.initial_focus && this._inputField.focus)
        this._inputField.focus();

    RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidate, this);
    //specific events for specific fields:
    this._fieldName = this.data.js.name;

};
RightNow.Widget.DirectedMessageInput.prototype = {
/**
 * ----------------------------------------------
 * Form / UI Events and Functions:
 * ----------------------------------------------
 */

    /**
    * Set field to be required
    */
    _setRequired: function(type, args)
    {
      //switch(this._fieldName) 
      //{
      //  case "onbehalf_first":
      //  case "onbehalf_last":
          if (this.data.attrs.required == false) {
            var divReq = document.createElement("span");
            divReq.id = "rn_" + this.instanceID + "_DivReq";
            var spanReq = document.createElement("span");
            var spanScreen = document.createElement("span");
            spanReq.className = "rn_Required";
            spanScreen.className = "rn_ScreenReaderOnly";
            spanReq.innerHTML = " * ";
            spanScreen.innerHTML = "Required";
            divReq.appendChild(spanReq);
            divReq.appendChild(spanScreen);
            //var lblObj = document.getElementById("rn_" + this.instanceID + "_Label").getElementsByTagName("span")[0];
            var lblObj = document.getElementById("rn_" + this.instanceID + "_Label");
            lblObj.appendChild(divReq);
            this.data.attrs.required = true;
          }
      //    break;
      //  default: break;
      //}
    },
    _unsetRequired: function(type, args)
    {
      //switch(this._fieldName) {
        //case "onbehalf_first":
        //case "onbehalf_last":
          if (this.data.attrs.required == true) {
            //var lblObj = document.getElementById("rn_" + this.instanceID + "_Label").getElementsByTagName("span")[0];
            var lblObj = document.getElementById("rn_" + this.instanceID + "_Label");
            // unset req
            if (lblObj.childNodes[1]) {
                lblObj.removeChild(lblObj.childNodes[1]);
            }
            this.data.attrs.required = false;
          }
        //  break;
        //default: break;
      //}
    },

    /**
     * Require Field to validate a section at a time
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _setRequire: function(type, args)
    {
        //console.log('setRequried: ' + this._fieldName);
        this.data.attrs.required = true;
    },
    
    /**
     * Event handler executed when form is being submitted
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _onValidate: function(type, args)
    {
        this._validated = true;
        this._parentForm = this._parentForm || RightNow.UI.findParentForm("rn_" + this.instanceID);
        var eo = new RightNow.Event.EventObject();
        eo.data = {
            "name" : this.data.js.name,
            "value" : this._getValue(),
            "table" : this.data.js.table,
            "required" : (this.data.attrs.required ? true : false),
            "prev" : this.data.js.prev,
            "form" : this._parentForm
            };
        if (RightNow.UI.Form.form === this._parentForm)
        {
            this._formErrorLocation = args[0].data.error_location;
            this._trimField();

            if(this._checkRequired() && this._checkData() && this._checkValue() && this._checkUrl())
            {
                YAHOO.util.Dom.removeClass(this._inputField, "rn_ErrorField");
                YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
                if(this.data.js.profile)
                    eo.data.profile = true;
                if(this.data.js.customID)
                {
                    eo.data.custom = true;
                    eo.data.customID = this.data.js.customID;
                    eo.data.customType = this.data.js.type;
                }
                else
                {
                    eo.data.custom = false;
                }
                if(this.data.js.channelID)
                {
                    eo.data.channelID = this.data.js.channelID;
                }
                eo.w_id = this.data.info.w_id;
                RightNow.Event.fire("evt_formFieldValidateResponse", eo);
            }
            else
            {
                RightNow.UI.Form.formError = true;
            }
        }
        else
        {
            RightNow.Event.fire("evt_formFieldValidateResponse", eo);
        }
        this._validated = false;
        RightNow.Event.fire("evt_formFieldCountRequest");
    },

    /**
    * Validates that the input field has a value (if required) and that the value is
    * of the correct format.
    */
    _blurValidate: function()
    {
        this._formErrorLocation = null;
        if(this._onAccountExistsResponse._dialogShowing) return;
        
        this._trimField();
        if(this._checkRequired() && this._checkData() && this._checkValue() && this._checkEmail())
        {
            if(this._fieldName === "login" || this._fieldName === "email" || this._fieldName === "email_alt1" || this._fieldName === "email_alt2")
            {
                this._checkExistingAccount();
            }
            YAHOO.util.Dom.removeClass(this._inputField, "rn_ErrorField");
            YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
            return true;
        }
    },

    /**
    * Checks that the value entered doesn't exceed its expected bounds
    */
    _checkValue: function()
    {
        if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_INT)
        {
            //make sure it's a valid int
            if(this._inputField.value !== "" && (isNaN(Number(this._inputField.value)) || parseInt(this._inputField.value) !== parseFloat(this._inputField.value)))
            {
                this._displayError(RightNow.Interface.getMessage('VALUE_MUST_BE_AN_INTEGER_MSG'));
                return false;
            }
            //make sure it's value is in bounds
            if(this.data.js.maxVal || this.data.js.minVal)
            {
                var value = parseInt(this._inputField.value);
                if(this.data.js.maxVal && value > parseInt(this.data.js.maxVal))
                {
                    this._displayError(RightNow.Interface.getMessage('VALUE_IS_TOO_LARGE_MAX_VALUE_MSG') + this.data.js.maxVal + ")");
                    return false;
                }
                if(this.data.js.minVal && value < parseInt(this.data.js.minVal))
                {
                    this._displayError(RightNow.Interface.getMessage('VALUE_IS_TOO_SMALL_MIN_VALUE_MSG') + this.data.js.minVal + ")");
                    return false;
                }
            }
        }
        else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_PASSWORD && this.data.js.name !== "password" && this.data.js.name !== "organization_password" && this.data.js.passwordLength)
        {
            var length = RightNow.Text.Encoding.utf8Length(this._inputField.value),
            minLength = this.data.js.passwordLength;
            if(length < minLength)
            {
                this._displayError(RightNow.Text.sprintf(RightNow.Interface.getMessage("PCT_D_CHARACTERS_MSG"), minLength));
                return false;
            }
        }
        if(this.data.js.fieldSize)
        {
            //make sure it's within the max field size
            var length = RightNow.Text.Encoding.utf8Length(this._inputField.value),
            maxLength = this.data.js.field_size;

            if(maxLength < length)
            {
                var extra, errorString,
                roughMBCS = parseInt(length / (this._inputField.value.length)),
                numtokExp  = new RegExp("%d");

                if(length % (this._inputField.value.length) !== 0)
                    roughMBCS++;

                extra = parseInt((length - maxLength) / roughMBCS);
                errorString = RightNow.Text.sprintf(RightNow.Interface.getMessage("EXCEEDS_SZ_LIMIT_PCT_D_CHARS_PCT_D_LBL"), numtokExp, parseInt(maxLength / roughMBCS));

                if((length - maxLength) % (roughMBCS) !== 0)
                    extra++;
                errorString = errorString.replace(numtokExp, extra);
                this._displayError(errorString);
                return false;
            }
        }
        return true;
    },

    /**
    * Validation routine to check for valid strings in certain fields i.e. first_name, last_name and login
    *
    * @param silent Boolean Optional parameter: set to true if the caller wishes to perform
    * the validation check without displaying error messages.
    */
    _checkData: function(silent)
    {
        var spacesRe = /\s/;
        if(this._inputField.value !== "")
        {
            if(this._fieldName === "login")
            {
                var quotesRe = /["']/;

                //check if username contains spaces
                if(spacesRe.test(this._inputField.value))
                {
                    if(!silent)
                        this._displayError(RightNow.Interface.getMessage('CONTAIN_SPACES_PLEASE_TRY_MSG'));
                    return false;
                }
                //check if username contains double quotes
                if(quotesRe.test(this._inputField.value))
                {
                    if(!silent)
                        this._displayError(RightNow.Interface.getMessage('CONTAIN_QUOTE_CHARS_PLEASE_TRY_MSG'));
                    return false;
                }
            }
            else if(this._fieldName === "ph_office" || this._fieldName === "ph_fax" || this._fieldName === "ph_home" || this._fieldName === "ph_asst" ||
                this._fieldName === "ph_mobile" || this._fieldName === "postal_code")
                {
                var validInput = new RegExp("^[-A-Za-z0-9,# +.()]+$");
                if(!validInput.test(this._inputField.value))
                {
                    if(!silent)
                    {
                        if(this._fieldName === "postal_code")
                            this._displayError(RightNow.Interface.getMessage("PCT_S_IS_AN_INVALID_POSTAL_CODE_MSG"));
                        else
                            this._displayError(RightNow.Interface.getMessage("PCT_S_IS_AN_INVALID_PHONE_NUMBER_MSG"));
                    }
                    return false;
                }
            }
            //Check for space characters on channel fields
            else if(this.data.js.channelID && spacesRe.test(this._inputField.value))
            {
                if(!silent)
                    this._displayError(RightNow.Interface.getMessage('CONTAIN_SPACES_PLEASE_TRY_MSG'));
                return false;
            }
        }
        return true;
    },

    /**
    * Validation routine to check for valid url custom fields
    *
    * @param silent Boolean Optional parameter: set to true if the caller wishes to perform
    * the validation check without displaying error messages.
    */
    _checkUrl: function(silent)
    {
        if((this.data.js.customID) && (this.data.js.url) && !(this._inputField.value === ""))
        {
            if(!RightNow.Text.isValidUrl(this._inputField.value))
            {
                if(!silent)
                    this._displayError(RightNow.Interface.getMessage("IS_NOT_A_VALID_URL_MSG"));
                return false;
            }
        }
        return true;
    },

    /**
     * Validation routine to check if field is required, and if so, ensure it has a value
     * @return Boolean denoting if required check passed
     */
    _checkRequired: function()
    {
        if(this.data.attrs.required)
        {
            //label infield logic
            //if(this._inputField.value === "" || this._inputField.value == this.data.attrs.label_input)
            if(this._inputField.value === "")
            {
                this._displayError(this.data.attrs.label_required);
                return false;
            }
        }
        return true;
    },

    /**
    * Returns the field's value
    * @return Mixed String or Int (for Int data type)
    */
    _getValue: function()
    {
        if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_INT)
        {
            if(this._inputField.value !== "")
                return parseInt(this._inputField.value);
        }
        if(this.data.js.mask)
            return this._stripMaskFromFieldValue();
        //label infield logic
        //return (this._inputField.value == this.data.attrs.label_input) ? "" : this._inputField.value;
        return this._inputField.value;

    },

    /**
    * Trims the value of the input field (removes leading / trailing whitespace).
    */
    _trimField: function()
    {
        if(this._inputField.value !== "" && this.data.js.type !== RightNow.Interface.Constants.EUF_DT_PASSWORD)
            this._inputField.value = YAHOO.lang.trim(this._inputField.value);
        return true;
    },

    /**
     * Displays error by appending message above submit button
     * @param errorMessage String Message to display
     */
    _displayError: function(errorMessage)
    {
        var commonErrorDiv = document.getElementById(this._formErrorLocation);
        if(commonErrorDiv)
        {
            RightNow.UI.Form.errorCount++;
            if(RightNow.UI.Form.chatSubmit && RightNow.UI.Form.errorCount === 1)
                commonErrorDiv.innerHTML = "";
            
            var errorNavStr = (this.data.attrs.error_nav) ? this.data.attrs.error_nav : "";
            var errorMsg = (this.data.attrs.error_msg) ? this.data.attrs.error_msg : 
                (this.data.attrs.error_validation_label) ? this.data.attrs.error_validation_label : this.data.attrs.label_input;
            errorMessage = (this.data.attrs.error_msg) ? "" : errorMessage;
            
            var errorLink = "<div><b><a href='javascript:void(0);' onclick='" + 
                errorNavStr + "document.getElementById(\"" + this._inputField.id +
                "\").focus(); return false;'>" + errorMsg + " ";

            if(errorMessage.indexOf("%s") > -1)
                errorLink = RightNow.Text.sprintf(errorMessage, errorLink);
            else
                errorLink = errorLink + errorMessage;

            errorLink += "</a></b></div> ";
            commonErrorDiv.innerHTML += errorLink;
        }
        YAHOO.util.Dom.addClass(this._inputField, "rn_ErrorField");
        YAHOO.util.Dom.addClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
    },

/**
 * --------------------------------------------------------
 * Business Rules Events and Functions:
 * --------------------------------------------------------
 */
    /**
     * Event handler for when email or login field blurs
     * Check to see if the username/email is unique
     */
    _checkExistingAccount: function()
    {
        if(this._inputField.value === "" || this._inputField.value === this.data.js.prev ||
            (this._fieldName.indexOf("email") > -1 && this._inputField.value.toLowerCase() === this.data.js.prev))
            return false;
        //static copy so we don't do a bunch of requests onblur if the value hasn't changed
        if(!this._checkExistingAccount._seenValue)
            this._checkExistingAccount._seenValue = this._inputField.value;
        else if(this._checkExistingAccount._seenValue === this._inputField.value)
            return false;
        else this._checkExistingAccount._seenValue = this._inputField.value;

        var evtObj = new RightNow.Event.EventObject();
        if(this._fieldName.indexOf("email") > -1)
            evtObj.data.email = this._inputField.value;
        else if(this._fieldName === "login")
            evtObj.data.login = this._inputField.value;
        evtObj.data.contactToken = this.data.js.contactToken;
        RightNow.Event.subscribe("evt_formFieldAccountExistsResponse", this._onAccountExistsResponse, this);
        RightNow.Event.fire("evt_formFieldAccountExistsRequest", evtObj);
    },

    /**
    * If args has a message and we aren't in the process of submitting
    * then alert the message; otherwise no duplicate account exists
    * @param type String Event name
    * @param args Object Event arguments
    */
    _onAccountExistsResponse: function(type, args)
    {
        RightNow.Event.unsubscribe("evt_formFieldAccountExistsResponse", this._onAccountExistsResponse);
        var results = args[0];
        if(results !== false && this._validated === false)
        {
            //add error indicators
            YAHOO.util.Dom.addClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
            YAHOO.util.Dom.addClass(this._inputField, "rn_ErrorField");
            //create action dialog with link to acct assistance page
            var handleOK = function(){
                warnDialog.hide();
                this._onAccountExistsResponse._dialogShowing = false;
                this._inputField.focus();
            };
            var buttons = [ {
                text: RightNow.Interface.getMessage("OK_LBL"), 
                handler: {
                    fn: handleOK, 
                    scope: this
                }, 
                isDefault: true
            } ];
            var dialogBody = document.createElement("div");
            dialogBody.innerHTML = results.message;
            var warnDialog = RightNow.UI.Dialog.actionDialog(RightNow.Interface.getMessage("WARNING_LBL"), dialogBody, {
                "buttons" : buttons, 
                "width" : "250px"
            });
            this._onAccountExistsResponse._dialogShowing = true;
            warnDialog.show();
        }
        else
        {
            //remove error indicators
            YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
            YAHOO.util.Dom.removeClass(this._inputField, "rn_ErrorField");
            this._validated = false;
        }
        return false;
    },

/**
 * --------------------------------------------------------
 * Mask Functions
 * --------------------------------------------------------
 */

    /**
     * Count characters and ensure the max
     */
    _countChars: function()
    {
        if (this._inputField.value.length > this.data.attrs.max_chars)
            this._inputField.value = this._inputField.value.substring(0, this.data.attrs.max_chars);

        this._countField.innerHTML = (this.data.attrs.max_chars - this._inputField.value.length) + " " + this.data.attrs.max_chars_label;
    },

    /**
     * adds/removes the label from the field, depending on it's value
     */
    _onLabelHandlerFocus: function()
    {
        if (this._inputField.value == this.data.attrs.label_input) {
            this._inputField.value = "";
        }
    },

    _onLabelHandlerBlur: function()
    {
        if (this._inputField.value.match(/^\s*$/) && this._inputField.tagName != "TEXTAREA") {
            this._inputField.value = this.data.attrs.label_input;
        }
    }
};
