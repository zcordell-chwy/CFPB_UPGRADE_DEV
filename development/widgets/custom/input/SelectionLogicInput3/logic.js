RightNow.Widget.SelectionLogicInput3 = function(data, instanceID) {

	this.data = data;
	this.instanceID = instanceID;
	this._formErrorLocation = null;
	this._reviewField = null;
	this._hidden = false;
	this._container = YAHOO.util.Dom.get( "rn_" + this.instanceID );
	this._altText = YAHOO.util.Dom.get( "rn_" + this.instanceID + "_alt_text" );
	/**
	 * used to save the requirement state when the element is hidden.  If optional_on_hide is set.
	 */
	this.savedRequiredState = this.data.attrs.required;
	this.group = {};
	/**
	 * Indicates the total number of valid elements in the group
	 */
	this.group.numValid = 0;
	this.group.numRequired = 1;

	if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO) {
		if(this.data.js.is_checkbox == true || this.data.attrs.radio_group) {
			this._inputField = YAHOO.util.Dom.get("rn_" + this.instanceID + "_" + this.data.js.name);
		} else {
			this._inputField = [document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_1"), document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_0")];
		}
	} else {
                // NEW
		// DT_SELECT && show_menu_as_radio
                if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_SELECT && this.data.attrs.show_menu_as_radio)
                {
                     this._inputField = YAHOO.util.Dom.getElementsBy(function(){return true;}, 'input', "rn_" + this.instanceID);
                }
                else
                {
		     this._inputField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name); // ORIG
                }
                // END NEW
	}
	
	if(!this._inputField || (YAHOO.lang.isArray(this._inputField) && (!this._inputField[0] || !this._inputField[1])))
		return;

	if(this.data.js.hint)
		this._initializeHint();

	if(this.data.attrs.initial_focus) {
		if(this._inputField[0] && this._inputField[0].focus)
			this._inputField[0].focus();
		else if(this._inputField.focus)
			this._inputField.focus();
	}

	if(this.data.attrs.validate_on_blur && this.data.attrs.required)
		YAHOO.util.Event.addListener(this._inputField, "blur", function() {
			this._formErrorLocation = null;
			this._validateRequirement();
		}, null, this);

	RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidate, this);
        
	//specific events for specific fields:
        this._fieldName = this.data.js.name;

	//province changing
	if(this._fieldName === "country_id") {
		YAHOO.util.Event.addListener(this._inputField, "change", this._countryChanged, null, this);
		if(this._inputField.value != "")
			this._countryChanged();
	} else if(this._fieldName === "prov_id") {
		RightNow.Event.subscribe("evt_formFieldProvinceResponse", this._onProvinceResponse, this);
	}
    
    switch(this._fieldName) {
        case "is_not_mycard":
            YAHOO.util.Event.addListener(this._inputField,"click", function() {
                if (this._inputField.checked == true) {
                    RightNow.Event.fire("evt_ccInfoEnable");
                } else {
                    RightNow.Event.fire("evt_ccInfoTryRevert");
                }
            }, null, this);
            //YAHOO.util.Event.addListener(this._inputField, "change", this._setObjVal, 'review_is_not_mycard', this);
            RightNow.Event.subscribe("evt_ccInfoUpdated", function() {
                this._inputField.checked = false;
            }, this);
            break;
		case "onbehalf_myself":
		case "onbehalf_someone":
			RightNow.Event.subscribe( 'ps_formSubmitContext', this._onFormSubmitContextChange, this );
		break;
        default: break;
    }  
    // Autorouting dynamic update of incident address
    if (this.data.attrs.enable_autoroutelookup) {
      switch(this._fieldName) {
        case "cc_issuer_state": // company state
        case "cc_issuer_country": // company country
            RightNow.Event.subscribe("evt_autorouteUpdate", this._autorouteUpdate, this);
            break;
        default: break;
      }
    }
	
    YAHOO.util.Event.addListener(this._inputField, 'change', this._fireInteractionEvent, 'evt_toggleFormElement', this);
    YAHOO.util.Event.addListener(this._inputField, 'click', this.handleOnClick, null, this);
    RightNow.Event.subscribe('evt_clickErrorMessage', this._onClickErrorMessage, this);
    
    RightNow.Event.subscribe('evt_toggleFormElement', this.onToggleFormElement, this);
    RightNow.Event.subscribe('evt_hideElement', this.onVisibilityChange, this);
    RightNow.Event.subscribe('evt_showElement', this.onVisibilityChange, this);
    
    if(this.data.js.optional_on_hide) {
		//if the widget is not visible initially, act like it was hidden
		var hidingAncestor = YAHOO.util.Dom.getAncestorBy(document.getElementById("rn_" + this.instanceID), this.getAncestorByCallback, this);

		if(hidingAncestor) {
			this.data.attrs.required = false;
		}
    }
    
    //set initial state
    this.hideReqLabel();
    
    //we only want to fire an interaction events for elements that have a default value - otherwise the hidden elements get all sorts of confused.
    if(this._getValue())
		YAHOO.util.Event.onDOMReady(this._fireInteractionEvent, 'evt_toggleFormElement', this);
};

RightNow.Widget.SelectionLogicInput3.prototype = {
	/**
	 * ----------------------------------------------
	 * Form / UI Events and Functions:
	 * ----------------------------------------------
	 */

	/**
	 * IE doesn't fire the change event until selection fields are blurred, work around this.
	 */
	handleOnClick : function(evt, args) {
		//if we're focusing on a radio button, the fieldset is the _inputField property, so we don't want to focus on that
		//find the actual button by using activeElement (not supported in old browsers)
		//otherwise try to blur this._inputField
		//otherwise....
		var element = (document.activeElement) ? document.activeElement : this._inputField;
		if(element){
			//select elements will close if unfocused
			if(element.nodeName && element.nodeName.toLowerCase() != "input"){
				return;
			}
			if(element.blur){
				element.blur();
				element.focus();
			}
		}
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
			// NEW
			//value = this._inputField.options[this._inputField.selectedIndex].label;
			value = (this.data.attrs.show_menu_as_radio) ? this._getValue() : this._inputField.options[this._inputField.selectedIndex].label;
			// END NEW
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
                        'w_id' : this.data.info.w_id,
                        'namespace' : this.data.js.namespace, // CBO specific
                        'is_cbo' : this.data.js.is_cbo        // CBO specific
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

                // console.log( "Event: " + eventName );
                // console.log( eo );

		RightNow.Event.fire(eventName, eo);
	},

	getAncestorByCallback : function(parent) {
		if(YAHOO.util.Dom.hasClass(parent, "rn_Hidden")) {
			return true;
		} else {
			return false;
		}
	},

	hideReqLabel : function() {
		if(this.data.js.type == RightNow.Interface.Constants.EUF_DT_SELECT) {
			if(this.data.attrs.select_required_pos > 0) {
				if(this._inputField.selectedIndex == 0) {
					document.getElementById('select_required_id_' + this.instanceID).className = "rn_Required";
				} else {
					document.getElementById('select_required_id_' + this.instanceID).className = "rn_Hidden";
                		        YAHOO.util.Dom.addClass('rn_' + this.instanceID + "_" + this.data.js.name, 'filled'); 
                		}
			}
		}
	},

	/**
	 * Event handler for when a form element changes
	 *
	 * handles making elements required or not.
	 *
	 *
	 */
	onToggleFormElement : function(type, args) {
		var eoData = args[0].data;
		//handle widget group functionality
		//if we're part of the group
		if(this.data.js.widget_group && this.data.js.widget_group.length > 0 && (this.data.js.widget_group == eoData.widget_group)) {
			this.group.numValid = eoData.numValid;

			//all elements of the group are required or no elements of the group are required
			if(this.group.numValid >= this.group.numRequired) {
				//remove the required state for all elements in the group since the group requirement is satisfied
				this.data.attrs.required = false;
				this.savedRequiredState = false;
			} else {
				this.data.attrs.required = true;
				this.savedRequiredState = true;
			}

                        /*
                        // Is only one item in this group allowed to be checked?
                        if( this.data.attrs.one_selection_per_widget_group )
                        {
                            console.log( this.data.js.name );
                            console.log( eoData );
                            
                            if( this.data.js.name != eoData.name && this._inputField && this._getValue() )
                            {
                                this._inputField.click();
                                // this._clearValue();
                                // this._fireInteractionEvent( 'evt_toggleFormElement', 'evt_toggleFormElement', 'evt_toggleFormElement' );
                            }
                        }
                        */
		}

		// Check to see if this widget's required state is conditional to another field's value...but only if it's currently visible.
		var hidingAncestor = YAHOO.util.Dom.getAncestorBy(document.getElementById("rn_" + this.instanceID), this.getAncestorByCallback, this);
		hidingAncestor = (this.data.js.name == 'state_sender' || this.data.js.name == 'state_recipient') ? false : hidingAncestor;
		if( !hidingAncestor || this.data.js.name == 'state_sender')
		{
			if( this.data.attrs.required_when_parent_equals && this.data.attrs.required_when_field_equals )
			{
				// var requiredElement = YAHOO.util.Dom.get( 'select_required_id_' + this.instanceID );
				var eoParent = eoData.table + '.' + eoData.name;
				if( this._altText && this.data.attrs.required_when_parent_equals == eoParent )
				{
					if( this.data.attrs.required_when_field_equals == eoData.value )
					{
						this.data.attrs.required = true;
						YAHOO.util.Dom.removeClass(this._container, "rn_Hidden");
						YAHOO.util.Dom.addClass(this._altText, "rn_Hidden");
					}
					else
					{
						this.data.attrs.required = false;
						// also reset the value to null
						this._inputField.value = '';
						var eo = new RightNow.Event.EventObject();
						eo.data = {"name" : this.data.js.name,
							"value" : this._inputField.value,
							"table" : this.data.js.table,
							"required" : (this.data.attrs.required ? true : false),
							"prev" : this.data.js.prev,
							"custom" : true};
						RightNow.Event.fire("evt_toggleFormElement", eo);

						YAHOO.util.Dom.removeClass(this._altText, "rn_Hidden");
						YAHOO.util.Dom.addClass(this._container, "rn_Hidden");
					}
					this.hideReqLabel();
				}
			}
		}
	},

	/**
	 * determines if the field is suitable for submission, if the element were to be required
	 */
	doesFulfilGrpReq : function() {
		if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO) {
			if(this.data.js.is_checkbox == true) {
				if(this._inputField && this._inputField.checked) {
					return true;
				} else {
					return false;
				}
			} else {
				if((this._inputField[0] && this._inputField[1]) && (!this._inputField[0].checked && !this._inputField[1].checked)) {
					return false;
				} else {
					return true;
				}
			}
		} else if(this._inputField.value === "") {
			return false;
		} else {
			return true;
		}
	},

	onVisibilityChange : function(type, args) {
		var controllerId = args[0].data.elementId;

		if(!YAHOO.util.Dom.isAncestor(controllerId, 'rn_' + this.instanceID))
			return;

		if(type === 'evt_hideElement')
			this._hidden = true;
		else
			this._hidden = false;

		if(this.data.js.optional_on_hide) {
			if(type == 'evt_hideElement') {
				this.data.attrs.required = false;
			} else {

				var hidingAncestor = YAHOO.util.Dom.getAncestorBy(document.getElementById("rn_" + this.instanceID), this.getAncestorByCallback);
				var controllerIsAncestorOfHider = YAHOO.util.Dom.isAncestor(controllerId, hidingAncestor);

				//if the element is visible, or hidden by an element that's a parent of the controlling element
				if(!controllerIsAncestorOfHider) {
					this.data.attrs.required = this.savedRequiredState;
				}
			}
		}

                if( this.data.attrs.clear_value_on_hide )
                {
                    // Removing check to see if items are hidden. There are too many conflicting events firing to know if the value should be cleared.
                    // If this function is called and it makes it this far, a controlling input has been changed. Clear the value.
                    // if( this._hidden == true )
                    // {
                        this._clearValue();
                    // }
                }
	},

        _clearValue: function()
        {
            if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO) {
                if(this.data.js.is_checkbox == true) {
                    if(this._inputField.checked === true) {
                        this._inputField.checked = false;
                    }
                } else {
                    if(this._inputField[0].checked)
                        this._inputField[0].checked = false;
                    if(this._inputField[1].checked)
                        this._inputField[1].checked = false;
                }
            } else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_CHECK) {
                ; // this._inputField.value === "1";
            } else {
                //select value
                this._inputField.value = '';
		var reqSpan = YAHOO.util.Dom.getPreviousSiblingBy(this._inputField, function(el){return el.getAttribute('class') == 'rn_Required';});
		if(reqSpan)
		{
			YAHOO.util.Dom.removeClass(reqSpan, 'rn_Hidden');
		}
            }
	    
        },

	/**
	 * Event handler executed when form is being submitted
	 *
	 * @param type String Event name
	 * @param args Object Event arguments
	 */
	_onValidate : function(type, args) {
		this._parentForm = this._parentForm || RightNow.UI.findParentForm("rn_" + this.instanceID);
		var eo = new RightNow.Event.EventObject();
		eo.data = {
			"name" : this.data.js.name,
			"value" : this._getValue(),
			"table" : this.data.js.table,
			"required" : (this.data.attrs.required ? true : false),
			"prev" : this.data.js.prev,
                        "form" : this._parentForm,
                        "namespace" : this.data.js.namespace, // CBO specific
                        "is_cbo" : this.data.js.is_cbo        // CBO specific
		};
		
		//if we're validating a single page on the form, and don't want to submit, see if this element is on the page to validate
		//if it's not just tell the event class that the element is valid
		if(args[0].data.tempValidationArea) {
                        // NEW
			// DT_SELECT && show_menu_as_radio
			var ancCheckField = (this.data.js.type === RightNow.Interface.Constants.EUF_DT_SELECT && this.data.attrs.show_menu_as_radio) ?
				this._inputField[0] : this._inputField;
			if(! YAHOO.util.Dom.isAncestor(args[0].data.tempValidationArea, ancCheckField))
			{
				RightNow.Event.fire("evt_formFieldValidateResponse", eo);
				RightNow.Event.fire("evt_formFieldCountRequest");
				return;
			}
                        // END NEW
		}

		// Do not validate or submit fields that are hidden by conditions like the hideElement widget.
		if(this._hidden) {
			eo.data.value = null;
			RightNow.Event.fire("evt_formFieldValidateResponse", eo);
			RightNow.Event.fire("evt_formFieldCountRequest");
			return;
		}
		
		if(RightNow.UI.Form.form === this._parentForm) {
			this._formErrorLocation = args[0].data.error_location;

			if(this._validateRequirement()) {
				if(this.data.js.profile)
					eo.data.profile = true;
				if(this.data.js.customID) {
					eo.data.custom = true;
					eo.data.customID = this.data.js.customID;
					eo.data.customType = this.data.js.type;
				} else {
					eo.data.custom = false;
				}
				eo.w_id = this.data.info.w_id;
				RightNow.Event.fire("evt_formFieldValidateResponse", eo);
			} else {
				RightNow.UI.Form.formError = true;
			}
		} else {
			RightNow.Event.fire("evt_formFieldValidateResponse", eo);
		}
		RightNow.Event.fire("evt_formFieldCountRequest");
	},

	/**
	 * Returns the String (Radio/Select) or Boolean value (Check) of the element.
	 * @return String/Boolean that is the field value
	 */
	_getValue : function() {
		if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO) {
			if(this.data.js.is_checkbox == true) {
				if(this._inputField.checked === true) {
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
		} else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_CHECK) {
			return this._inputField.value === "1";
		} else {
			//select value
			// NEW
			//return this._inputField.value;
			if(this.data.attrs.show_menu_as_radio)
			{
				// Look for one of the radio's to be checked
        	                var rSelected = null;
                	        for(i = 0; i < this._inputField.length; i++)
                        	{
	                                if(this._inputField[i].checked)
        	                        {
                	                        rSelected = this._inputField[i].value;
                        	                break;
                                	}
	                        }
				return rSelected;
			}
			else
			{
				return this._inputField.value;
			}
			// END NEW
		}
	},

	/**
	 * Validation routine to check if field is required, and if so, ensure it has a value
	 * @return Boolean denoting if required check passed
	 */
	_validateRequirement : function() {
		if(this.data.attrs.required) {
			if(this.data.js.is_checkbox == true) {
				if(!this._inputField.checked) {
					this._displayError(this.data.attrs.label_required);
					return false;
				}
			} else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO) {
				if((this._inputField[0] && this._inputField[1]) && (!this._inputField[0].checked && !this._inputField[1].checked)) {
					this._displayError(this.data.attrs.label_required);
					return false;
				}
			// NEW
			// DT_SELECT && show_menu_as_radio
			} else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_SELECT && this.data.attrs.show_menu_as_radio) {
				// Look for one of the radio's to be checked
				var rSelected = false;
				for(i = 0; i < this._inputField.length; i++)
				{
					if(this._inputField[i].checked)
					{
						rSelected = true;
						break;
					}
				}
				
				if(!rSelected)
				{
					this._displayError(this.data.attrs.label_required);
                                        return false;
				}
			// END NEW
			} else if(this._inputField.value === "") {
				this._displayError(this.data.attrs.label_required);
				return false;
			}
		}
		// NEW
		//YAHOO.util.Dom.removeClass(this._inputField, "rn_ErrorField");
		var inpField = (this.data.js.type === RightNow.Interface.Constants.EUF_DT_SELECT && this.data.attrs.show_menu_as_radio) ?
			document.getElementById('rn_' + this.instanceID + '_menu_as_radios')  : this._inputField;
		YAHOO.util.Dom.removeClass(inpField, "rn_ErrorField");
		// END NEW
		YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
		return true;
	},

	/**
	 * Creates the hint overlay that shows / hides when
	 * the input field is focused / blurred.
	 */
	_initializeHint : function() {
		if(YAHOO.widget.Overlay) {
			if(this.data.attrs.always_show_hint) {
				var overlay = this._createHintElement(true);
			} else {
				var overlay = this._createHintElement(false);
				YAHOO.util.Event.addListener(this._inputField, "focus", function() {
					overlay.show();
				});

				YAHOO.util.Event.addListener(this._inputField, "blur", function() {
					overlay.hide();
				});

			}
		} else {
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
	_createHintElement : function(visibility) {
		var overlay = document.createElement("span");
		overlay.id = "rn_" + this.instanceID + "_Hint";
		YAHOO.util.Dom.addClass(overlay, "rn_HintBox");
		if(visibility)
			YAHOO.util.Dom.addClass(overlay, "rn_AlwaysVisibleHint");
		if(YAHOO.lang.isArray(this._inputField)) {
			//radio buttons
			YAHOO.util.Dom.setStyle(overlay, "margin-left", "2em");
			YAHOO.util.Dom.insertAfter(overlay, this._inputField[this._inputField.length - 1]);
		} else {
			YAHOO.util.Dom.insertAfter(overlay, this._inputField);
		}
		overlay = new YAHOO.widget.Overlay(overlay, {
			visible : visibility
		});
		overlay.setBody(this.data.js.hint);
		overlay.render();

		return overlay;
	},

	/**
	 * Displays error by appending message above submit button
	 *
	 * @param string errorMessage Message to display
	 */
	_displayError : function(errorMessage) {
		var commonErrorDiv, elementId, errorId, label;
		commonErrorDiv = document.getElementById(this._formErrorLocation);
		// NEW
		//errorId = 'rn_ErrorMessage_' + this._inputField.id;
		errorId = 'rn_ErrorMessage_' + ((this.data.js.type === RightNow.Interface.Constants.EUF_DT_SELECT && this.data.attrs.show_menu_as_radio) ? this._inputField[0].id : this._inputField.id);
		// END NEW
		label = '';
		label = this.data.attrs.label_input;
		if(!this.data.js.is_checkbox && this.data.attrs.label_nothing_selected)
			label = this.data.attrs.label_nothing_selected;

		// Add error classes to the field.
		// NEW
		var inpField = (this.data.js.type === RightNow.Interface.Constants.EUF_DT_SELECT && this.data.attrs.show_menu_as_radio) ? 
			document.getElementById('rn_' + this.instanceID + '_menu_as_radios')  : this._inputField;
		//YAHOO.util.Dom.addClass(this._inputField, "rn_ErrorField");
		YAHOO.util.Dom.addClass(inpField, "rn_ErrorField");
		// END NEW
		YAHOO.util.Dom.addClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");

		// Increment error count and handle chat case.
		RightNow.UI.Form.errorCount++;
		if(RightNow.UI.Form.chatSubmit && RightNow.UI.Form.errorCount === 1)
			commonErrorDiv.innerHTML = '';

		// Manipulate the error message, specific to this type of widget.
		if(errorMessage && errorMessage.indexOf('%s') > -1)
			errorMessage = RightNow.Text.sprintf(errorMessage, label);
		
	        if (this.data.attrs.error_msg)
			errorMessage = this.data.attrs.error_msg;

		// Create error message markup.
		errorLink = '<div><b><a id="' + errorId + '" href="javascript:void(0);">' + errorMessage + '</a></b></div>';

		commonErrorDiv.innerHTML += errorLink;

		// Make error messages clickable.
		RightNow.Event.subscribe("evt_formFailValidationResponse", function() {
			YAHOO.util.Event.on(errorId, 'click', this._fireInteractionEvent, 'evt_clickErrorMessage', this);
		}, this);

	},

	/**
	 * --------------------------------------------------------
	 * Business Rules Events and Functions:
	 * --------------------------------------------------------
	 */
	/**
	 * Event handler executed when country dropdown is changed
	 */
	_countryChanged : function() {
		if(this._inputField.options) {
			var evtObj = new RightNow.Event.EventObject();
			evtObj.data = {
				"country_id" : this._inputField.options[this._inputField.selectedIndex].value,
				"w_id" : this.instanceID
			};
			RightNow.Event.fire("evt_formFieldProvinceRequest", evtObj);
		}
	},

	/**
	 * Event handler executed when province/state data is returned from the server
	 *
	 * @param type String Event name
	 * @param args Object Event arguments
	 */
	_onProvinceResponse : function(type, args) {

		var evtObj = args[0], options = this._inputField.options, aNewOption, i;
		if(evtObj.states) {
			options.length = 0;
			//evtObj.states.unshift({val: "--", id: ""});
			evtObj.states.unshift({
				val : "State",
				id : ""
			});
			for( i = 0; i < evtObj.states.length; i++) {
				aNewOption = document.createElement("option");
				aNewOption.text = evtObj.states[i].val;
				aNewOption.value = evtObj.states[i].id;
				if(i == this.data.js.prev)
					aNewOption.selected = "selected";
				options.add(aNewOption);
			}
		}
	},

	/**
	 * When the user clicks the error message, focus on the field.
	 *
	 * @param mixed evt  Event
	 * @param array args Args
	 */
	_onClickErrorMessage : function(evt, args) {
		var field = args[0].data.field;
		var name = args[0].data.name;
		
		// NEW
		// DT_SELECT & show_menu_as_radio
		if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_SELECT && this.data.attrs.show_menu_as_radio)
		{
			field = field[0];
		}
		// END NEW

		if(name !== this.data.js.name)
			return;

		// When the field becomes visible, focus it.
		RightNow.Event.subscribe('evt_changeStep', function() {
			try {field.focus();
			} catch(err) {
			}
		});

		try {field.focus();
		} catch(err) {
		}
	},

    /**
     * set specified form element fields value by element id
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _autorouteUpdate: function(type, args)
    {
        var data = args[0];
        if (!data)
        {
            // make sure default is United States if country
            if (this._fieldName === "cc_issuer_country")
                this._inputField.options[1].selected = true;
            else
                this._inputField.options[0].selected = true;
            this._inputField.disabled = false;
        }
        else
        {
          var value = null;
          switch (this._fieldName)
          {
            case "cc_issuer_state":
                value = data.State;
                break;
            case "cc_issuer_country":
                value = data.Country;
                // not sure why country data is returned as the 2 char abbreviation
                // assume we are only dealing with US companies
                if (value == "US")
                    value = "United States";
                break;

            default: break;
          }
            // need to find selected index based on value
            for (var i = 1; i < this._inputField.options.length; i++)
            {
                if (this._inputField.options[i].innerHTML == value) 
                {
                    this._inputField.options[i].selected = true;
                    break;
                }
                else
                    this._inputField.options[0].selected = true;
            }
            this._inputField.disabled = true;
        }

        var eo = new RightNow.Event.EventObject();
        eo.data = {"name" : this.data.js.name,
                   "value" : value,
                   "table" : this.data.js.table,
                   "required" : (this.data.attrs.required ? true : false),
                   "prev" : this.data.js.prev,
                   "custom" : true};
        RightNow.Event.fire("evt_toggleFormElement", eo);
    },

	/**
	 * Event handler fired when someone changes for whom the incident is submitted.
	 */
	_onFormSubmitContextChange: function( type, args )
	{
		var eventData = args[0];
		switch( eventData.data.filer )
		{
			case 'myself':
				if( this._fieldName == 'onbehalf_myself' )
				{
					// this._inputField.click();
                                        this._inputField.checked = true;
				}
				else if( this._fieldName == 'onbehalf_someone' )
				{
					// this._inputField.click();
                                        this._inputField.checked = false;
				}
				this._fireInteractionEvent( 'evt_toggleFormElement', 'evt_toggleFormElement', 'evt_toggleFormElement' );
			break;
			case 'someone':
				if( this._fieldName == 'onbehalf_myself' )
				{
					// this._inputField.click();
                                        this._inputField.checked = false;
				}
				else if( this._fieldName == 'onbehalf_someone' )
				{
					// this._inputField.click();
                                        this._inputField.checked = true;
				}
				this._fireInteractionEvent( 'evt_toggleFormElement', 'evt_toggleFormElement', 'evt_toggleFormElement' );
			break;
			default:
			break;
		}
	}
};
