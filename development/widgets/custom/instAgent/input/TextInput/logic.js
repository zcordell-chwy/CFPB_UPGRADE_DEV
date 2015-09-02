RightNow.Widget.TextInput = function(data, instanceID) {
    this.data = data;
    this.instanceID = instanceID;
    this._formErrorLocation = null;
    this._validated = false;

    this._inputField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name);

    if(!this._inputField) return;

     if(this.data.js.hint)
        this._initializeHint();

    if(this.data.attrs.initial_focus && this._inputField.focus)
        this._inputField.focus();

    //setup mask
    if(this.data.js.mask)
        this._initializeMask();

    RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidate, this);
    //specific events for specific fields:
    this._fieldName = this.data.js.name;
    //province changing : update phone/postal masks
    if(this._fieldName === "postal_code" || this._fieldName === "ph_office" || this._fieldName === "ph_mobile" || this._fieldName === "ph_fax" ||
        this._fieldName == "ph_asst" || this._fieldName === "ph_home")
                RightNow.Event.subscribe("evt_formFieldProvinceResponse", this._onProvinceChange, this);
    //check for existing username/email
    if(this.data.attrs.validate_on_blur)
        YAHOO.util.Event.addListener(this._inputField, "blur", this._blurValidate, null, this);
    
        switch(this._fieldName) {
            case "bank_resolution":
                RightNow.Event.subscribe("evt_copyFieldValue", this._copyFieldValue, this);
                break;
            case "thread":
                YAHOO.util.Event.addListener(this._inputField, "change", function() {
                    RightNow.Event.fire("evt_copyFieldValue", this);
                }, null, this);
                break;
            case "comp_dollar_amount":
            case "cfpb_dollar_amount":
                YAHOO.util.Event.addListener(this._inputField, "blur", function() {
                    if (this._inputField.value != '' && !/^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/.test(this._inputField.value)) {
                        alert('Please enter a valid amount\n\t1000\n\t1,000\n\t1,000.00');
                        this._inputField.focus();
                        this._inputField.value = '';
                    }
                }, null, this);
                break;
            default:
                break;
    }
};
RightNow.Widget.TextInput.prototype = {
/**
 * ----------------------------------------------
 * Form / UI Events and Functions:
 * ----------------------------------------------
 */

    /**
     * Event handler to copy thread into custom field 
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _copyFieldValue: function(type, args)
    {
        this._inputField.value = args[0]._inputField.value;
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
        eo.data = {"name" : this.data.js.name,
                   "value" : this._getValue(),
                   "table" : this.data.js.table,
                   "required" : (this.data.attrs.required ? true : false),
                   "prev" : this.data.js.prev,
                   "form" : this._parentForm};
        if (RightNow.UI.Form.form === this._parentForm)
        {
            this._formErrorLocation = args[0].data.error_location;
            this._trimField();

            if(this._compareInputToMask(true) && this._checkRequired() && this._checkData() && this._checkValue() && this._checkEmail() && this._checkUrl())
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
    * Validation routine to check for valid email addresses
    *
    * @param silent Boolean Optional parameter: set to true if the caller wishes to perform
    * the validation check without displaying error messages.
    */
    _checkEmail: function(silent)
    {
        if(!(this._fieldName === 'email' || this._fieldName === 'email_alt1' || this._fieldName === 'email_alt2'|| this._fieldName === 'alternateemail' || this.data.js.email) || this._inputField.value === "")
            return true;
        if (this._fieldName === 'alternateemail')
        {
            var status = true;
            var emailArray = this._inputField.value.split(";");
            for (var i = 0; i < emailArray.length; i++)
            {
                emailArray[i] = YAHOO.lang.trim(emailArray[i]);
                status = (this._validateEmail(emailArray[i], silent) && status) ? true : false;
            }
            return status;
        }
        else
        {
            return this._validateEmail(this._inputField.value, silent);
        }
    },
    
    /**
    * subroutine to validate against the regex
    * @param value String the email address
    * @param silent Boolean Optional parameter: set to true if the caller wishes to perform
    */
    _validateEmail: function(value, silent)
    {
        if(!RightNow.Text.isValidEmailAddress(value))
        {
            if(!silent)
                this._displayError(RightNow.Interface.getMessage("PCT_S_IS_INVALID_MSG"));
           return false;
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
     * Shows hint on the input field's focus
     * and hides the hint on the field's blur.
     */
    _initializeHint: function()
    {
        if(YAHOO.widget.Overlay)
        {
            if (this.data.attrs.always_show_hint)
            {
                var overlay = this._createHintElement(true);
            }
            else
            {
                var overlay = this._createHintElement(false);
                YAHOO.util.Event.addListener(this._inputField, "focus", function(){overlay.show();});
                YAHOO.util.Event.addListener(this._inputField, "blur", function(){overlay.hide();});
            }
        }
        else
        {
            //display hint inline if YUI container code isn't being included
            var hint = document.createElement("span");
            hint.className = "rn_HintText";
            hint.innerHTML = this.data.js.hint;
            YAHOO.util.Dom.insertAfter(hint, this._inputField);
        }
    },

    /**
     * Creates the hint element.
     * @param visibility Boolean whether the hint element is initially visible
     * @return Object representing the hint element
     */
    _createHintElement: function(visibility)
    {
        var overlay = document.createElement("span");
        overlay.id = "rn_" + this.instanceID + "_Hint";
        YAHOO.util.Dom.addClass(overlay, "rn_HintBox");
        if (visibility)
            YAHOO.util.Dom.addClass(overlay, "rn_AlwaysVisibleHint");
        YAHOO.util.Dom.insertAfter(overlay, this._inputField);

        overlay = new YAHOO.widget.Overlay(overlay, {visible: visibility});
        overlay.setBody(this.data.js.hint);
        overlay.render();
        
        return overlay;
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

            var errorLink = "<div><b><a href='javascript:void(0);' onclick='document.getElementById(\"" + this._inputField.id +
                "\").focus(); return false;'>" + this.data.attrs.label_input + " ";

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
            var buttons = [ { text: RightNow.Interface.getMessage("OK_LBL"), handler: {fn: handleOK, scope: this}, isDefault: true } ];
            var dialogBody = document.createElement("div");
            dialogBody.innerHTML = results.message;
            var warnDialog = RightNow.UI.Dialog.actionDialog(RightNow.Interface.getMessage("WARNING_LBL"), dialogBody, {"buttons" : buttons, "width" : "250px"});
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
     * Event handler for when province/state data is returned from the server
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _onProvinceChange: function(type, args)
    {
        var eventObj = args[0],
              resetMask = false;

        if(!eventObj.states.length)
            this.data.js.mask = "";

        if((this._fieldName === "postal_code") && ("postal_mask" in eventObj))
        {
            resetMask = true;
            this.data.js.mask = eventObj.postal_mask;
        }
        else if("phone_mask" in eventObj)
        {
            resetMask = true;
            this.data.js.mask = eventObj.phone_mask;
        }

        if(resetMask && this.data.js.mask)
            this._initializeMask();
        else if(this._maskNodeOnPage)
            this._maskNodeOnPage.parentNode.removeChild(this._maskNodeOnPage);
    },
/**
 * --------------------------------------------------------
 * Mask Functions
 * --------------------------------------------------------
 */
    /**
    * Creates a mask overlay
    */
     _initializeMask: function()
     {
            YAHOO.util.Event.addListener(this._inputField,"keyup", this._compareInputToMask, null, this);
            YAHOO.util.Event.addListener(this._inputField, "blur", this._hideMaskMessage, null, this);
            YAHOO.util.Event.addListener(this._inputField, "focus", this._compareInputToMask, null, this);
            this.data.js.mask = this._createMaskArray(this.data.js.mask);
            //Set up mask overlay
            var overlay = document.createElement("div");
            YAHOO.util.Dom.addClass(overlay, "rn_MaskOverlay");
            if(YAHOO.widget.Overlay)
            {
                this._maskNode = YAHOO.util.Dom.insertAfter(overlay, this._inputField);
                this._maskNode = new YAHOO.widget.Overlay(this._maskNode, { visible:false });
                this._maskNode.cfg.setProperty("context", [this._inputField, "tl", "bl", ["windowScroll"]]);
                this._maskNode.setBody("");
                this._maskNode.render();
            }
            else
            {
                YAHOO.util.Dom.addClass(overlay, "rn_Hidden");
                this._maskNode = overlay;
                YAHOO.util.Dom.insertAfter(this._maskNode, this._inputField);
            }

            if(this.data.attrs.always_show_mask)
            {
                //Write mask onto the page
                var maskMessageOnPage = this._getSimpleMaskString(),
                widgetContainer = document.getElementById("rn_" + this.instanceID);
                if(maskMessageOnPage && widgetContainer)
                {
                    var messageNode = document.createElement("div");
                    messageNode.innerHTML = RightNow.Interface.getMessage("EXPECTED_INPUT_LBL") + ": " + maskMessageOnPage;
                    YAHOO.util.Dom.addClass(messageNode, 'rn_Mask' + (YAHOO.util.Dom.hasClass(widgetContainer.lastChild, 'rn_HintText') ? ' rn_MaskBuffer' : ''));
                    this._maskNodeOnPage = widgetContainer.appendChild(messageNode);
                }
            }
     },
    /**
     * Creates a mask array based on the passed-in
     * string mask value.
     * @param mask String The new mask to apply to the field
     * @return Array the newly created mask array
     */
    _createMaskArray: function(mask)
    {
        if(!mask) return;
        var maskArray = [];
        for(var i = 0, j = 0, size = mask.length / 2; i < size; i++)
        {
            maskArray[i] = mask.substring(j, j + 2);
            j += 2;
        }
        return maskArray;
    },

    /**
    * Removes the mask from the field value.
    * @return String The value without the mask
    */
    _stripMaskFromFieldValue: function()
    {
        if(!this.data.js.mask || this._inputField.value === "")
            return this._inputField.value;

        var result = "";
        for(var i = 0; i < this._inputField.value.length; i++)
        {
            if(i < this.data.js.mask.length && this.data.js.mask[i].charAt(0) !== 'F')
                result += this._inputField.value.charAt(i);
        }
        return result;
    },

     /**
     * Builds up simple mask string example based off of mask characters
     */
    _getSimpleMaskString: function()
    {
        if(!this.data.js.mask) return "";
        var maskString = "";
        for(var i = 0; i < this.data.js.mask.length; i++)
        {
            switch(this.data.js.mask[i].charAt(0)) {
                case "F":
                    maskString += this.data.js.mask[i].charAt(1);
                    break;
                case "U":
                    switch(this.data.js.mask[i].charAt(1)) {
                        case "#":
                            maskString += "#";
                            break;
                        case "A":
                        case "C":
                            maskString += "@";
                            break;
                        case "L":
                            maskString += "A";
                            break;
                    }
                    break;
                case "L":
                    switch(this.data.js.mask[i].charAt(1)) {
                        case "#":
                            maskString += "#";
                            break;
                        case "A":
                        case "C":
                            maskString += "@";
                            break;
                        case "L":
                            maskString += "a";
                            break;
                    }
                    break;
                case "M":
                    switch(this.data.js.mask[i].charAt(1)) {
                        case "#":
                            maskString += "#";
                            break;
                        case "A":
                        case "C":
                        case "L":
                            maskString += "@";
                            break;
                    }
                    break;
            }
        }
        return maskString;
    },

    /**
     * Compares entered value to required mask format
     * @param submitting Boolean Whether the form is submitting or not;
     * don't display the mask message if the form is submitting.
     * @return Boolean denoting of value coforms to mask
     */
    _compareInputToMask: function(submitting)
    {
        if(!this.data.js.mask) return true;
        var error = [];
        if(this._inputField.value.length > 0)
        {
            for(var i = 0, tempRegExVal; i < this._inputField.value.length; i++) {
                if(i < this.data.js.mask.length) {
                    tempRegExVal = "";
                    switch(this.data.js.mask[i].charAt(0)) {
                        case 'F':
                            if(this._inputField.value.charAt(i) !== this.data.js.mask[i].charAt(1))
                                error.push([i,this.data.js.mask[i]]);
                            break;
                        case 'U':
                            switch(this.data.js.mask[i].charAt(1)) {
                                case '#':
                                    tempRegExVal = /^[0-9]+$/;
                                    break;
                                case 'A':
                                    tempRegExVal = /^[0-9A-Z]+$/;
                                    break;
                                case 'L':
                                    tempRegExVal = /^[A-Z]+$/;
                                    break;
                                case 'C':
                                    tempRegExVal = /^[^a-z]+$/;
                                    break;
                            }
                            break;
                        case 'L':
                            switch(this.data.js.mask[i].charAt(1)) {
                                case '#':
                                    tempRegExVal = /^[0-9]+$/;
                                    break;
                                case 'A':
                                    tempRegExVal = /^[0-9a-z]+$/;
                                    break;
                                case 'L':
                                    tempRegExVal = /^[a-z]+$/;
                                    break;
                                case 'C':
                                    tempRegExVal = /^[^A-Z]+$/;
                                    break;
                            }
                            break;
                        case 'M':
                            switch(this.data.js.mask[i].charAt(1)) {
                                case '#':
                                    tempRegExVal = /^[0-9]+$/;
                                    break;
                                case 'A':
                                    tempRegExVal = /^[0-9a-zA-Z]+$/;
                                    break;
                                case 'L':
                                    tempRegExVal = /^[a-zA-Z]+$/;
                                    break;
                                default:
                                    break;
                            }
                            break;
                        default:
                            break;
                    }
                    if((tempRegExVal !== "") && !(tempRegExVal.test(this._inputField.value.charAt(i))))
                        error.push([i,this.data.js.mask[i]]);
                }
                else
                {
                    error.push([i,"LEN"]);
                }
            }
            //input matched mask but length didn't match up
            if((!error.length) && (this._inputField.value.length < this.data.js.mask.length) && (!this.data.attrs.always_show_mask || submitting === true))
            {
                for(var i = this._inputField.value.length; i < this.data.js.mask.length; i++)
                    error.push([i,"MISS"]);
            }
            if(error.length > 0)
            {
                //input didn't match mask
                this._showMaskMessage(error);
                if(submitting === true)
                    this._displayError(RightNow.Interface.getMessage("PCT_S_DIDNT_MATCH_EXPECTED_INPUT_LBL"));
                return false;
            }
            //no mask errors
            this._showMaskMessage(null);
            return true;
        }
        //haven't entered anything yet...
        if(!this.data.attrs.always_show_mask && submitting !== true)
            this._showMaskMessage(error);
        return true;
    },

    /**
     * Actually shows the error message to the user
     * @param error Array Collection of details about error to display
     */
    _showMaskMessage: function(error)
    {
        if(error === null)
        {
            this._hideMaskMessage();
        }
        else
        {
            if(!this._showMaskMessage._maskMessages)
            {
                //set a static variable containing error messages so it's lazily defined once across widget instances
                this._showMaskMessage._maskMessages = {"F" : RightNow.Interface.getMessage('WAITING_FOR_CHARACTER_LBL'),
                    "U#" : RightNow.Interface.getMessage('PLEASE_TYPE_A_NUMBER_MSG'),
                    "L#" : RightNow.Interface.getMessage('PLEASE_TYPE_A_NUMBER_MSG'),
                    "M#" : RightNow.Interface.getMessage('PLEASE_TYPE_A_NUMBER_MSG'),
                    "UA" : RightNow.Interface.getMessage('PLEASE_ENTER_UPPERCASE_LETTER_MSG'),
                    "UL" : RightNow.Interface.getMessage('PLEASE_ENTER_AN_UPPERCASE_LETTER_MSG'),
                    "UC" : RightNow.Interface.getMessage('PLS_ENTER_UPPERCASE_LETTER_SPECIAL_MSG'),
                    "LA" : RightNow.Interface.getMessage('PLEASE_ENTER_LOWERCASE_LETTER_MSG'),
                    "LL" : RightNow.Interface.getMessage('PLEASE_ENTER_A_LOWERCASE_LETTER_MSG'),
                    "LC" : RightNow.Interface.getMessage('PLS_ENTER_LOWERCASE_LETTER_SPECIAL_MSG'),
                    "MA" : RightNow.Interface.getMessage('PLEASE_ENTER_A_LETTER_OR_A_NUMBER_MSG'),
                    "ML" : RightNow.Interface.getMessage('PLEASE_ENTER_A_LETTER_MSG'),
                    "MC" : RightNow.Interface.getMessage('PLEASE_ENTER_LETTER_SPECIAL_CHAR_MSG'),
                    "LEN" : RightNow.Interface.getMessage('THE_INPUT_IS_TOO_LONG_MSG'),
                    "MISS" : RightNow.Interface.getMessage('THE_INPUT_IS_TOO_SHORT_MSG') };
            }
            var message = "",
                  sampleMaskString = this._getSimpleMaskString().split("");
            for(var i = 0, type; i < error.length; i++)
            {
                type = error[i][1];
                //F means format char should have followed
                if(type.charAt(0) === "F")
                {
                    message += "<b>" + RightNow.Interface.getMessage('CHARACTER_LBL') + " " + (error[i][0] + 1) + "</b> " + RightNow.Interface.getMessage('WAITING_FOR_CHARACTER_LBL') + type.charAt(1) + " ' <br/>";
                    sampleMaskString[(error[i][0])] = "<span style='color:#F00;'>" + sampleMaskString[(error[i][0])] + "</span>";
                }
                else
                {
                    if(type !== "MISS")
                    {
                        message += "<b>" + RightNow.Interface.getMessage('CHARACTER_LBL') + " " + (error[i][0] + 1) + "</b> " + this._showMaskMessage._maskMessages[type] + "<br/>";
                        if(type !== "LEN")
                        {
                            sampleMaskString[(error[i][0])] = "<span style='color:#F00;'>" + sampleMaskString[(error[i][0])] + "</span>";
                        }
                        else
                        {
                            break;
                        }
                    }
                }
            }
            sampleMaskString = sampleMaskString.join("");
            this._setMaskMessage(RightNow.Interface.getMessage('EXPECTED_INPUT_LBL') + ": "  + sampleMaskString + "<br/>" + message);
            this._showMask();
        }
    },

    /**
    * Sets mask message.
    * @param message String message to set
    */
    _setMaskMessage: function(message)
    {
        ((this._maskNode.body) ? this._maskNode.body : this._maskNode).innerHTML = message;
    },

    /**
    * Shows mask message.
    */
    _showMask: function()
    {
        if(this._maskNode.show)
            this._maskNode.show();
        else
            YAHOO.util.Dom.removeClass(this._maskNode, "rn_Hidden");
    },

    /**
     * Hides mask message.
     */
    _hideMaskMessage: function()
    {
        if(this._maskNode.cfg && this._maskNode.cfg.getProperty("visible") !== false)
              this._maskNode.hide();
        else if(!this._maskNode.cfg)
            YAHOO.util.Dom.addClass(this._maskNode, "rn_Hidden");
    }
};
