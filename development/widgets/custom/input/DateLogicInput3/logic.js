RightNow.Widget.DateLogicInput3 = function(data, instanceID){

    this.data = data;
    this.instanceID = instanceID;
    this._formErrorLocation = null;
    this._errorNodes = null;
    this._selectedDate = null;
    
    var widgetContainer = document.getElementById("rn_" + this.instanceID);
    if(!widgetContainer) return;
    this._selectNodes = YAHOO.util.Dom.getElementsBy(function(node){return node.tagName === "SELECT";}, "SELECT", "rn_" + this.instanceID);
    if(!this._selectNodes) return;

    this._calendarContainer = YAHOO.util.Dom.get("rn_" + this.instanceID + "_calendarContainer");
    if (!this._calendarContainer) return;

    // setup the calendar control
    this._datePicker = YAHOO.util.Dom.get("rn_" + this.instanceID + "_datePicker");
    if (!this._datePicker) return;
    
    // make maxdate an attribute
    if (this.data.attrs.maxdate)
        this._calendarCtrl = new YAHOO.widget.Calendar("calendarCtrl", this._datePicker, {maxdate: new Date, mindate: "1/1/1970", navigator:true, iframe:true});
    else
        this._calendarCtrl = new YAHOO.widget.Calendar("calendarCtrl", this._datePicker, {mindate: "1/1/1970", navigator:true, iframe:true});

    this._calendarCtrl.selectEvent.subscribe(this._onSelectEvent, this, true);
    this._calendarCtrl.render();

    this._dateString = YAHOO.util.Dom.get("rn_" + this.instanceID + "_dateString");
    if (!this._dateString) return;

    YAHOO.util.Event.on(this._dateString, "focus", this._showCalendar, null, this);

    this._dateIcon = YAHOO.util.Dom.get("rn_" + this.instanceID + "_calendarIcon");
    YAHOO.util.Event.on(this._dateIcon, "click", this._showCalendar, null, this);
    // YAHOO.util.Event.on(this._dateString, "blur", this._hideCalendar, null, this);
    // end calendar control

    if(this.data.js.hint)
        this._initializeHint();

    if(this.data.attrs.initial_focus)
    {
        if(this._selectNodes[0] && this._selectNodes[0].focus)
            this._selectNodes[0].focus();
    }
    if(this.data.attrs.validate_on_blur)
        YAHOO.util.Event.addListener(this._selectNodes[this._selectNodes.length - 1], "blur", this._blurValidate, null, this);

    RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidate, this);

    // Hide Calendar if we click anywhere in the document other than the calendar
    YAHOO.util.Event.on(document, "click", function(e) {
        var el = YAHOO.util.Event.getTarget(e);
        // var calEl = this._calendarCtrl.element;
        var calEl = this._calendarContainer;
        if (el != calEl && !YAHOO.util.Dom.isAncestor(calEl, el) && el != this._dateString && el != this._dateIcon) {
            this._calendarCtrl.hide();
        }
    }, null, this);
    
    // Compare Against another field's value?
    this._targetFieldValues = new Array();
    if(this.data.attrs.validate_against_target_field && this.data.attrs.validate_against_target_field_criteria)
    {
	this._targetFieldValues[this.data.attrs.validate_against_target_field] = '';
        RightNow.Event.subscribe("evt_toggleFormElement", this._getFieldValues, this);
    }
};

RightNow.Widget.DateLogicInput3.prototype = {
    /**
     * Retrives the entered value of the field
     * @return String Value in various formats depending on its type
     */
    _getValue: function()
    {
        var fieldValue = "";
        if (this._selectedDate) 
        {
            fieldValue = this._selectedDate[0] + "-" + this._selectedDate[1] + "-" + this._selectedDate[2];
            if (fieldValue === "--") return "";

            if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_DATETIME)
            {
		   var hourField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_Hour"),
		       minuteField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_Minute");
		    
		    if(this.data.attrs.hide_hours_mins)
		    {
			hourField.value = '0';
                        minuteField.value = '0';
		    }
		    
                    if (hourField && minuteField)
                    {
                        fieldValue += " " + hourField.options[hourField.selectedIndex].value + ":" +
                                  minuteField.options[minuteField.selectedIndex].value;
                    }
            }
        }
        return fieldValue;
    },

    /**
     * Event handler for when form is being submitted
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
                   "form" : this._parentForm,
                   "namespace" : this.data.js.namespace, // CBO specific
                   "is_cbo" : this.data.js.is_cbo        // CBO specific
            };
        if (RightNow.UI.Form.form === this._parentForm)
        {
            this._formErrorLocation = args[0].data.error_location;
            YAHOO.util.Dom.removeClass(this._errorNodes, "rn_ErrorField");
            YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Legend", "rn_ErrorLabel");

            if(this._checkRequired() && this._checkValue())
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
                eo.w_id = this.instanceID;
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
    * Validates that a proper date has been entered.
    */
    _blurValidate: function()
    {
        YAHOO.util.Dom.removeClass(this._errorNodes, "rn_ErrorField");
        YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Legend", "rn_ErrorLabel");
        this._formErrorLocation = null;
        this._checkRequired();
        this._checkValue();
    },

    /**
     * Validation routine to check if field is required, and if so, ensure it has a value
     * @return Boolean denoting if required check passed
     */
    _checkRequired: function()
    {
        if(this.data.attrs.required)
        {
            this._errorNodes = [];
            for(var i = 0; i < this._selectNodes.length; i++)
            {
                if(this._selectNodes[i].value === "")
                {
                    this._errorNodes.push(this._selectNodes[i].id);
                }
            }
            if (!this._selectedDate) this._errorNodes.push(this._dateString.id);
            if(this._errorNodes.length > 0)
            {
                this._displayError(this.data.attrs.label_required);
                return false;
            }
        }
        return true;
    },

    /**
     * Validation routine to check if field passes type and size requirements
     * @return Boolean denoting if value is acceptable
     */
    _checkValue: function()
    {
        this._errorNodes = [];
        var numberFilledIn = 0,
              numberChecked = 0;

        //check if all of the date fields have been set (only all or none is allowed)
        for(var i = 0; i < this._selectNodes.length; i++)
        {
            if(this._selectNodes[i].value === "")
                this._errorNodes.push(this._selectNodes[i].id);
            else
                numberFilledIn++;
            numberChecked++;
        }
        if(numberFilledIn > 0 && numberFilledIn !== numberChecked)
        {
            this._displayError(RightNow.Interface.getMessage("PCT_S_IS_NOT_COMPLETELY_FILLED_IN_MSG"));
            return false;
        }
        
        // Validate against another field?
        if(this.data.attrs.validate_against_target_field && this.data.attrs.validate_against_target_field_criteria)
        {
            if(!this._validateTargetField())
	    {
		this._errorNodes.push('rn_' + this.instanceID + '_dateString');
		var msg = (this.data.attrs.validate_against_target_field_error) ? this.data.attrs.validate_against_target_field_error : 'Invalid Date';
		this._displayError(msg);
                return false;
	    }
        }
        
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
                YAHOO.util.Event.addListener(this._selectNodes, "focus", function(){overlay.show();});
                YAHOO.util.Event.addListener(this._selectNodes, "blur", function(){overlay.hide();});
            }
        }
        else
        {
            //display hint inline if YUI container code isn't being included
            var hint = document.createElement("span");
            hint.className = "rn_HintText";
            hint.innerHTML = this.data.js.hint;
            YAHOO.util.Dom.insertAfter(hint, this._selectNodes[this._selectNodes.length - 1]);
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
        YAHOO.util.Dom.insertAfter(overlay, this._selectNodes[this._selectNodes.length - 1]);

        overlay = new YAHOO.widget.Overlay(overlay, {visible: visibility});
        overlay.setBody(this.data.js.hint);
        overlay.render();
        
        return overlay;
    },

    /**
     * Displays error by appending message above submit button
     * @param error String Message to display
     */
    _displayError: function(errorMessage)
    {
        var commonErrorDiv = document.getElementById(this._formErrorLocation);
        if(commonErrorDiv)
        {
            RightNow.UI.Form.errorCount++;
            if(RightNow.UI.Form.chatSubmit && RightNow.UI.Form.errorCount === 1)
                commonErrorDiv.innerHTML = "";

            var errorLink = "<div><b><a href='javascript:void(0);' onclick='document.getElementById(\"" + this._errorNodes[0] +
                "\").focus(); return false;'>" + this.data.attrs.label_input;
            if(errorMessage.indexOf("%s") > -1 || arguments.length > 1)
            {
                //pass to sprintf any additional arguments that may have been specified.
                var args = Array.prototype.slice.call(arguments);
                errorLink = RightNow.Text.sprintf(errorMessage, errorLink, args.slice(1));
            }
            else
            {
                errorLink = errorLink + errorMessage;
            }
            errorLink += "</a></b></div> ";
            commonErrorDiv.innerHTML += errorLink;
        }
        YAHOO.util.Dom.addClass(this._errorNodes, "rn_ErrorField");
        YAHOO.util.Dom.addClass("rn_" + this.instanceID + "_Legend", "rn_ErrorLabel");
    },

    _showCalendar: function()
    {
        this._calendarCtrl.show();
    },
    
    _onSelectEvent: function(type, args, obj)
    {
        this._selectedDate = args[0][0];
        this._dateString.value = this._selectedDate[1] + "/" + this._selectedDate[2] + "/" + this._selectedDate[0];
        // set review date value
        //this._setObjVal(type, 'review_dtccissuehappen', this._dateString.value);
        var eo = new RightNow.Event.EventObject();
        eo.data = {"name" : this.data.js.name,
                   "value" : this._dateString.value,
                   "table" : this.data.js.table,
                   "required" : (this.data.attrs.required ? true : false),
                   "prev" : this.data.js.prev,
                   "custom" : true,
                   "namespace" : this.data.js.namespace,    // CBO specific
                   "is_cbo" : this.data.js.is_cbo           // CBO specific
                  };
        
        RightNow.Event.fire("evt_toggleFormElement", eo);

        this._calendarCtrl.hide();
    },
    
    /**
     * Sets review value
     * @param type String Event name
     * @param args Object Event arguments
     * @param val Value
     */
    _setObjVal: function(type, args, val)
    {
        var obj = document.getElementById(args);
        var objSpan = document.getElementById(args + '_span');
        if (obj) {
            if (val != null || val != '')
            {
                obj.innerHTML = val;
                if (objSpan)
                    objSpan.className = "review";
            }
        }
    },
    
    _getFieldValues : function (type, args)
    {
        var eoData = args[0].data;
        var formField = eoData.table + '.' + eoData.name;
        
	// Add more fields here ..........................
        switch(formField)
        {
            case 'incidents.transfer_date':
                if(eoData.value)
                {
                    this._targetFieldValues[formField] = eoData.value;
                }
                break;
            default:
                break;
        }
    },
    
    _validateTargetField : function ()
    {
//console.log('validateTargetField() start');
	var status = true;
	var targetFieldValue = this._targetFieldValues[this.data.attrs.validate_against_target_field];
	var thisFieldValue = this._getValue();
	
        if(targetFieldValue.length > 0 && thisFieldValue.length > 0)
	{
//console.log('time to do comparison');
	    // M/D/YYYY
	    var tmp = targetFieldValue.split('/');
	    targetFieldValue = tmp[2] + ("00" + tmp[0]).slice(-2) + ("00" + tmp[1]).slice(-2);
	    
	    // YYYY-M-D H:M
	    tmp = thisFieldValue.split(' ');
	    tmp = tmp[0].split('-');
	    thisFieldValue = tmp[0] + ("00" + tmp[1]).slice(-2) + ("00" + tmp[2]).slice(-2);
//console.log(targetFieldValue + ' vs ' + thisFieldValue);
	    
	    // Add more comparisons here .....................
	    switch(this.data.attrs.validate_against_target_field_criteria)
	    {
		case '>=':
	            status = (thisFieldValue >= targetFieldValue) ? true : false;
//console.log('passed the test');
		    break;
		default:
		    break;
	    }
	    
	    return status;
        }
	
	return status;
    }
};
