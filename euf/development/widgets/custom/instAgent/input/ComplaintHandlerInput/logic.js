RightNow.Widget.ComplaintHandlerInput = function(data, instanceID){

    this.data = data;
    this.instanceID = instanceID;
    this._formErrorLocation = null;
    this._reviewField = null;
    
    if (this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO)
    {
        if (this.data.attrs.is_checkbox == true) {
            this._inputField = YAHOO.util.Dom.get("rn_" + this.instanceID + "_" + this.data.js.name);
        } else {
            this._inputField = [document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_1"),
            document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_0")];
        }
    }
    else
    {
        this._inputField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name);
    }
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
            function() {
                this._formErrorLocation = null;
                this._validateRequirement();
            }, null, this);

    RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidate, this);

    YAHOO.util.Event.addListener(this._inputField, 'change', this._fireInteractionEvent, 'evt_toggleFormElement', this);

    //specific events for specific fields:
    var fieldName = this.data.js.name;

    var evtObj = new RightNow.Event.EventObject();
    evtObj.data = {
    	"inputField" : this._inputField,
    	"w_id" : this.instanceID
    };

    // Catch events fired that determine if this field is required.
    RightNow.Event.subscribe( 'evt_showOrgComplaintHandler', this._setRequired, this );
    RightNow.Event.subscribe( 'evt_hideOrgComplaintHandler', this._unsetRequired, this );
};

RightNow.Widget.ComplaintHandlerInput.prototype = {
/**
 * ----------------------------------------------
 * Form / UI Events and Functions:
 * ----------------------------------------------
 */

    /**
     * Set fields to required
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _setRequired: function(type, args)
    {
      if (this.data.attrs.required == false) {
        this.data.attrs.required = true;
      }
    },
    _unsetRequired: function(type, args)
    {
      if (this.data.attrs.required == true) {
        this.data.attrs.required = false;
        this._inputField.value = '';
      }
    },

    /**
     * require Field to validate a section at a time
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _setRequire: function(type, args)
    {
        this.data.attrs.required = true;
    },
    
    /**
     * Listener for populating the review fields (only used for manual population
     * of fields, such as on the account/complaints/review page)
     * 
     * @param fieldname Name of the field to populate on the page.
     */
    _populateListener: function(fieldname)
    {
        this._reviewField = fieldname;
        RightNow.Event.subscribe("evt_populateReview", this._populateField, this);
    },
    
    /**
     * Populates the review field using the stored review element
     */
    _populateField: function(type, args)
    {
        if (this._reviewField !== null) {
            this._setObjVal(type, this._reviewField);
        }
    },
    /**
     * Event handler executed when form is being submitted
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _discrimChanged: function(type, args)
    {
        this._setObjVal(type, 'review_cmqillegaldiscriminiation');
        if (this._inputField[0].checked == true) {
            document.getElementById("complaint_discrim_base").className = "";
            document.getElementById("review_discrim_basis_span").className = "review2";
        } else {
            document.getElementById("complaint_discrim_base").className = "rn_Hidden";
            document.getElementById("review_discrim_basis_span").className = "rn_Hidden";
        }
    },
    _setObjVal: function(type, args)
    {   
        if (this.data.attrs.select_required_pos > 0) {
            //console.log(this.data.js.name + ':' + this._inputField.selectedIndex);
            if (this._inputField.selectedIndex == 0)
                document.getElementById('select_required_id_' + this.instanceID).className = "rn_Required";
            else
                document.getElementById('select_required_id_' + this.instanceID).className = "rn_Hidden";
        }
        var obj = document.getElementById(args);
        var objSpan = document.getElementById(args + '_span');
        var checked = "";
        var checked1 = "";
        var checked2 = "";
        var innerHTML = "";
        //show checkbox values
        if (this.data.attrs.is_checkbox == true) {
            // determine to show contact attempts label (review_attempts_span
            switch (this.data.js.name) {
                case "rcontactedccissuer":
                case "rcontactedcfpb":
                case "rcontactedgovagency":
                case "rretainedattorney":
                case "rfiledlegalaction":
                    if (this._inputField.checked == true) 
                        contactedAttempts++;
                    else 
                        contactedAttempts--;
                    break;
            }
            if (this._inputField.checked == true) {
                checked = "checked";
                innerHTML = "<input type='checkbox' " + checked + " disabled /> " + this.data.attrs.label_input;
            }
        } else { 
            if (this._inputField.options) { // show menu drop downs
                if (this._inputField.selectedIndex > 0) {
                    innerHTML = this._inputField.options[this._inputField.selectedIndex].text;
                }
            } else { // show radio values
                if (this._inputField[0].checked == true) {
                    checked1 = "checked";
                    checked2 = "";
                } else {
                    checked1 = "";
                    checked2 = "checked";
                }
                innerHTML = "<b>" + this.data.attrs.label_input + "</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" 
                + "Yes <input type='radio' " + checked1 + " disabled /> "
                + "No <input type='radio' " + checked2 + " disabled /> ";
            }
        }
        if (obj)
            obj.innerHTML = innerHTML;
        if (objSpan && innerHTML)
            objSpan.className = "review";
    },

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
            if (this.data.attrs.is_checkbox == true) {
                if (this._inputField.checked === true) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if(this._inputField[0].checked)
                    return this._inputField[0].value;
                if(this._inputField[1].checked)
                    return this._inputField[1].value;
            }
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
                YAHOO.util.Event.addListener(this._inputField, "focus", function(){
                    overlay.show();
                });
                YAHOO.util.Event.addListener(this._inputField, "blur", function(){
                    overlay.hide();
                });
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

        overlay = new YAHOO.widget.Overlay(overlay, {
            visible: visibility
        });
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

            var errorNavStr = (this.data.attrs.error_nav) ? this.data.attrs.error_nav : "";
            var labelStr = (this.data.attrs.error_msg) ? this.data.attrs.error_msg : this.data.attrs.label_input;
            var elementId = (YAHOO.util.Lang.isArray(this._inputField)) ? this._inputField[0].id : this._inputField.id,
            errorLink = "<div><b><a href='javascript:void(0);' onclick='" + 
                errorNavStr + "document.getElementById(\"" + elementId +
                "\").focus(); return false;'>" + labelStr;
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
     * Fires a specified interaction event
     *
     * @param mixed  event     Event
     * @param string args      Args
     * @param string eventName Event name
     */
    _fireInteractionEvent : function(evt, args, eventName) {
        eventName = eventName ? eventName : args;

        this._parentForm = this._parentForm || RightNow.UI.findParentForm("rn_" + this.instanceID);
        var eo = new RightNow.Event.EventObject();
        var type, value;

        if(this.data.js.is_checkbox)
            type = RightNow.Interface.Constants.EUF_DT_CHECK;
        else
            type = this.data.js.type;

        if(this.data.js.type == RightNow.Interface.Constants.EUF_DT_SELECT) {
            this.hideReqLabel();
            value = this._inputField.options[this._inputField.selectedIndex].label;
        } else {
            value = this._getValue();
        }

        eo.data = {
            'name' : this.data.js.name,
            'value' : value,
            'table' : this.data.js.table,
            'form' : this._parentForm,
            'field' : this._inputField,
            'type' : type,
            'w_id' : this.data.info.w_id
        };
        if(this.data.js.customID) {
            eo.data.custom = true;
            eo.data.customID = this.data.js.customID;
            eo.data.customType = this.data.js.type;
        } else {
            eo.data.custom = false;
        }

        if(this.data.js.widget_group && this.data.js.widget_group.length > 0) {
            eo.data.widget_group = this.data.js.widget_group;

            // Determine if we went from invalid to valid or valid to invalid.
            if(this.doesFulfilGrpReq())
                eo.data.numValid = ++this.group.numValid;
            else
                eo.data.numValid = (this.group.numValid > 0 ) ? --this.group.numValid : 0;
        }
        if(this.data.js.radio_group && this.data.js.radio_group.length > 0) {
            eo.data.radio_group = this.data.js.radio_group;
        }

        RightNow.Event.fire(eventName, eo);
    },

    hideReqLabel : function() {
        if(this.data.js.type == RightNow.Interface.Constants.EUF_DT_SELECT) {
            if(this.data.attrs.select_required_pos > 0) {
                if(this._inputField.selectedIndex == 0)
                    document.getElementById('select_required_id_' + this.instanceID).className = "rn_Required";
                else {
                    document.getElementById('select_required_id_' + this.instanceID).className = "rn_Hidden";
                    YAHOO.util.Dom.addClass('rn_' + this.instanceID + "_" + this.data.js.name, 'filled');
                }
            }
        }
    }

/**
 * --------------------------------------------------------
 * Business Rules Events and Functions:
 * --------------------------------------------------------
 */

};
