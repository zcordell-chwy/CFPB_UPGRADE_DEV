RightNow.Widget.SelectionInput = function(data, instanceID){

    this.data = data;
    this.instanceID = instanceID;
    this._formErrorLocation = null;

    if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO)
        this._inputField = [document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_1"),
            document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_0")];
    else
        this._inputField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name);
    if(!this._inputField || (YAHOO.lang.isArray(this._inputField) && (!this._inputField[0] || !this._inputField[1])))
        return;

    if(this.data.js.hint)
        this._initializeHint();

    if(this.data.attrs.initial_focus)
    {
        if(this._inputField[0] && this._inputField[0].focus)
            this._inputField[0].focus();
        else if(this._inputField.focus)
            this._inputField.focus();
    }

    if(this.data.attrs.validate_on_blur && this.data.attrs.required)
        YAHOO.util.Event.addListener(this._inputField, "blur",
            function() { this._formErrorLocation = null; this._validateRequirement(); }, null, this);

    RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidate, this);
    //specific events for specific fields:
    var fieldName = this.data.js.name;
    //province changing
    if(fieldName === "country_id")
        YAHOO.util.Event.addListener(this._inputField,"change", this._countryChanged, null, this);
    else if(fieldName === "prov_id")
        RightNow.Event.subscribe("evt_formFieldProvinceResponse", this._onProvinceResponse, this);
};

RightNow.Widget.SelectionInput.prototype = {
/**
 * ----------------------------------------------
 * Form / UI Events and Functions:
 * ----------------------------------------------
 */
    /**
     * Event handler executed when form is being submitted
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _onValidate: function(type, args)
    {
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

            if(this._validateRequirement())
            {
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
        RightNow.Event.fire("evt_formFieldCountRequest");
    },

    /**
    * Returns the String (Radio/Select) or Boolean value (Check) of the element.
    * @return String/Boolean that is the field value
    */
    _getValue: function()
    {
        if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO)
        {
            if(this._inputField[0].checked)
                return this._inputField[0].value;
            if(this._inputField[1].checked)
                return this._inputField[1].value;
        }
        else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_CHECK)
        {
            return this._inputField.value === "1";
        }
        else
        {
            //select value
            return this._inputField.value;
        }
    },

    /**
     * Validation routine to check if field is required, and if so, ensure it has a value
     * @return Boolean denoting if required check passed
     */
    _validateRequirement: function()
    {
        if(this.data.attrs.required)
        {
            if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO)
            {
                if((this._inputField[0] && this._inputField[1]) && (!this._inputField[0].checked && !this._inputField[1].checked))
                {
                    this._displayError(this.data.attrs.label_required);
                    return false;
                }
            }
            else if(this._inputField.value === "")
            {
                this._displayError(this.data.attrs.label_required);
                return false;
            }
        }
        YAHOO.util.Dom.removeClass(this._inputField, "rn_ErrorField");
        YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
        return true;
    },

    /**
     * Creates the hint overlay that shows / hides when
     * the input field is focused / blurred.
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
            YAHOO.util.Dom.insertAfter(hint, (YAHOO.lang.isArray(this._inputField) && this._inputField.length) ? this._inputField[this._inputField.length - 1] : this._inputField);
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
        if(YAHOO.lang.isArray(this._inputField))
        {
            //radio buttons
            YAHOO.util.Dom.setStyle(overlay, "margin-left", "2em");
            YAHOO.util.Dom.insertAfter(overlay, this._inputField[this._inputField.length - 1]);
        }
        else
        {
            YAHOO.util.Dom.insertAfter(overlay, this._inputField);
        }

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

            var elementId = (YAHOO.util.Lang.isArray(this._inputField)) ? this._inputField[0].id : this._inputField.id,
            errorLink = "<div><b><a href='javascript:void(0);' onclick='document.getElementById(\"" + elementId +
                "\").focus(); return false;'>" + this.data.attrs.label_input;

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
     * Event handler executed when country dropdown is changed
     */
    _countryChanged: function()
    {
        if(this._inputField.options)
        {
            var evtObj = new RightNow.Event.EventObject();
            evtObj.data = {"country_id" : this._inputField.options[this._inputField.selectedIndex].value,
                                 "w_id" : this.instanceID};
            RightNow.Event.fire("evt_formFieldProvinceRequest", evtObj);
        }
    },

    /**
     * Event handler executed when province/state data is returned from the server
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _onProvinceResponse: function(type, args)
    {

        var evtObj = args[0],
            options = this._inputField.options,
            aNewOption, i;
        if(evtObj.states)
        {
            options.length = 0;
            evtObj.states.unshift({val: "--", id: ""});
            for(i = 0; i < evtObj.states.length; i++)
            {
                aNewOption = document.createElement("option");
                aNewOption.text = evtObj.states[i].val;
                aNewOption.value = evtObj.states[i].id;
                options.add(aNewOption);
            }
        }
    }
};
