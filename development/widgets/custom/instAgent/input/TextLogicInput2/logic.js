RightNow.Widget.TextLogicInput2 = function(data, instanceID) {
	this.data               = data;
	this.instanceID         = instanceID;
	this._formErrorLocation = null;
	this._validated         = false;
	this.savedRequiredState = this.data.attrs.required;

	this._inputField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name);
	this._countField = document.getElementById("rn_" + this.instanceID + "_" + this.data.js.name + "_count");
	this._reviewField = null;

    this._hidden = false;
	this._allowAutorouteUpdate = true;

	if(!this._inputField)
		return;
		
//had to do some wackiness because of IE8 order of operations (Owen S.G)
	var ltIE9 = false;
	if(navigator.appVersion.indexOf("MSIE 8.0") > 0 || navigator.appVersion.indexOf("MSIE 7.0") > 0)
	{	
		YAHOO.util.Event.on(this._inputField, 'blur', this._fireInteractionEvent, 'evt_toggleFormElement', this);
		ltIE9 = true;
	}
			
	// subscribe to the keydown/keyup events
	if(this.data.attrs.max_chars && this.data.attrs.max_chars > 0) {
		YAHOO.util.Event.on(this._inputField, "keydown", this._countChars, null, this);
		YAHOO.util.Event.on(this._inputField, "keyup",   this._countChars, null, this);
		YAHOO.util.Event.on(this._inputField, "blur",   this._countChars, null, this);
	}

	if(!ltIE9)
	{
		YAHOO.util.Event.on(this._inputField, 'blur', this._fireInteractionEvent, 'evt_toggleFormElement', this);
	}
		RightNow.Event.subscribe('evt_clickErrorMessage', this._onClickErrorMessage, this);
	

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
	if(this._fieldName === "postal_code" || this._fieldName === "ph_office" || this._fieldName === "ph_mobile" || this._fieldName === "ph_fax" || this._fieldName == "ph_asst" || this._fieldName === "ph_home")
		RightNow.Event.subscribe("evt_formFieldProvinceResponse", this._onProvinceChange, this);
	//check for existing username/email
	if(this.data.attrs.validate_on_blur)
		YAHOO.util.Event.addListener(this._inputField, "blur", this._blurValidate, null, this);

        // Someone entered their date of birth - calculate age.
        if( this._fieldName === "age" )
        {
            RightNow.Event.subscribe( "ps_formFieldAgeSetResponse", this._onAgeSet, this );
        }

	RightNow.Event.subscribe('evt_hideElement', this.onVisibilityChange, this);
	RightNow.Event.subscribe('evt_showElement', this.onVisibilityChange, this);
  
    // disable email input when logged in. FTSAI 20120906
    if (this._fieldName === "email") {
        if (this._inputField.value)
            this._inputField.disabled = true;
    }

    if (this.data.attrs.enable_cclookup) {
      switch(this._fieldName) {
        // TODO copy contact address fields to incident ccmail address fields
        /*
        case "street":
            if (this._inputField.value != null || this._inputField.value != "")
            {
                RightNow.Event.fire("evt_copyPrevInfo", this);    
            }
            break;
        case "ccmail_addr1":
            RightNow.Event.subscribe("evt_copyPrevInfo", this._copyPrevInfo, "street", this);
            break;
        */
        case "rccnumber":
            YAHOO.util.Event.addListener(this._inputField, "change", this._validateCCvalue, null, this);
            break;
        case "cc_org_id":
            RightNow.Event.subscribe("evt_ccInfoUpdate", this._ccInfoUpdate, this);
            RightNow.Event.subscribe("evt_ccInfoEnable", this._ccInfoEnable, this);
            RightNow.Event.subscribe("evt_ccInfoTryRevert", this._ccInfoTryRevert, this);
            break;
        case "cc_co_name":
            RightNow.Event.subscribe("evt_ccInfoUpdate", this._ccInfoUpdate, this);
            RightNow.Event.subscribe("evt_ccInfoEnable", this._ccInfoEnable, this);
            RightNow.Event.subscribe("evt_ccInfoTryRevert", this._ccInfoTryRevert, this);
            break;
        case "cc_issuer_phone":
            RightNow.Event.subscribe("evt_ccInfoUpdate", this._ccInfoUpdate, this);
            RightNow.Event.subscribe("evt_ccInfoEnable", this._ccInfoEnable, this);
            RightNow.Event.subscribe("evt_ccInfoTryRevert", this._ccInfoTryRevert, this);
            break;
        case "cc_issuer_website":
            RightNow.Event.subscribe("evt_ccInfoUpdate", this._ccInfoUpdate, this);
            RightNow.Event.subscribe("evt_ccInfoEnable", this._ccInfoEnable, this);
            RightNow.Event.subscribe("evt_ccInfoTryRevert", this._ccInfoTryRevert, this);
            break;
        default: break;
      }
    }
    // Autorouting dynamic update of incident address
    if (this.data.attrs.enable_autoroutelookup) {
      switch(this._fieldName) {
        case "cc_org_id": // org id
        case "cc_co_name": // company name
        case "cc_issuer_addr1": // company street
        case "cc_issuer_city": // company city
        case "cc_issuer_zip": // company zip
            RightNow.Event.subscribe("evt_autorouteUpdate", this._autorouteUpdate, this);
            break;
        default: break;
      }
    }
    
    switch(this._fieldName) {
            case "comp_describe_relief":
                RightNow.Event.subscribe("evt_showCompRelief", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCompRelief", this._unsetRequired, this);
                break;
            case "comp_provide_a_response":
                RightNow.Event.subscribe("evt_showCompInfo", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCompInfo", this._unsetRequired, this);
                break;
            case "comp_explanation_of_closure":
                RightNow.Event.subscribe("evt_showCompExplanation", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCompExplanation", this._unsetRequired, this);
                break;
            case "cfpb_describe_relief":
                RightNow.Event.subscribe("evt_showCFPBRelief", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCFPBRelief", this._unsetRequired, this);
                break;
            case "cfpb_provide_a_response":
                RightNow.Event.subscribe("evt_showCFPBInfo", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCFPBInfo", this._unsetRequired, this);
                break;
            case "cfpb_explanation_of_closure":
                RightNow.Event.subscribe("evt_showCFPBExplanation", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCFPBExplanation", this._unsetRequired, this);
                break;
            // copied logic from TextInput widget since TextLogicInput already can dynamically set/unset required fields
            case "comp_dollar_amount":
                RightNow.Event.subscribe("evt_showCompReliefAmount", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCompReliefAmount", this._unsetRequired, this);
                YAHOO.util.Event.addListener(this._inputField, "blur", this._isMonetaryValue, null, this);
                break;
            case "cfpb_dollar_amount":
                RightNow.Event.subscribe("evt_showCFPBReliefAmount", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideCFPBReliefAmount", this._unsetRequired, this);
                YAHOO.util.Event.addListener(this._inputField, "blur", this._isMonetaryValue, null, this);
                break;
            case "added_to_case":
                RightNow.Event.subscribe("evt_showDuplicate", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideDuplicate", this._unsetRequired, this);                
                break;
            case "agency_name":
                RightNow.Event.subscribe("evt_showRegulator", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideRegulator", this._unsetRequired, this);                                
                break;            
            case "redirect_explanation":
                RightNow.Event.subscribe("evt_showRedirectExplain", this._setRequired, this);
                RightNow.Event.subscribe("evt_hideRedirectExplain", this._unsetRequired, this);                                
                break;            
            default:
                break;
    }

	if(this.data.js.optional_on_hide) {
		//if the widget is not visible initially, act like it was hidden
		var hidingAncestor = YAHOO.util.Dom.getAncestorBy(document.getElementById("rn_" + this.instanceID), this.getAncestorByCallback);
		if(hidingAncestor) {
			this.data.attrs.required = false;
		}
	}

	if(this.data.js.type == RightNow.Interface.Constants.EUF_DT_SELECT) {
		YAHOO.util.Event.addListener(this._inputField, "change", this._countryChanged, null, this);
	} else {
		YAHOO.util.Event.addListener(this._inputField, "keyup", this.removeLabel, null, this);
	}

	YAHOO.util.Event.onDOMReady(this._fireInteractionEvent, 'evt_toggleFormElement', this);
};

RightNow.Widget.TextLogicInput2.prototype = {
	/**
	 * ----------------------------------------------
	 * Form / UI Events and Functions:
	 * ----------------------------------------------
	 */

/**
    * Check for numeric mask for monetary value up to two decimal places.    
    */
    _isMonetaryValue: function(type, args)
    {    
      if (this._inputField.value != '' && !/^[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]{2})?$/.test(this._inputField.value)) {
        alert('Please enter a valid amount\n\t1000\n\t1,000\n\t1,000.00');
        this._inputField.focus();
        this._inputField.value = '';
      }
    },

    /**
    * Set field to be required
    */
    _setRequired: function(type, args)
    {
      //console.log(this._fieldName);                
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

    // unset the requried fields
    _unsetRequired: function(type, args)
    {
        // reset error messages if any
        YAHOO.util.Dom.removeClass(this._inputField, "rn_ErrorField");
        YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
        document.getElementById('rn_ErrorLocation').className = "rn_Hidden";

      //switch(this._fieldName) {
        //case "onbehalf_first":
        //case "onbehalf_last":
          if (this.data.attrs.required == true) {
            //var lblObj = document.getElementById("rn_" + this.instanceID + "_Label").getElementsByTagName("span")[0];
            var lblObj = document.getElementById("rn_" + this.instanceID + "_Label");
            // unset req
            // updated to be dynamic. 201205110 FTSAI
            for (var i=0; i<lblObj.childNodes.length; i++)
            {
                if (lblObj.childNodes[i].id && lblObj.childNodes[i].id.indexOf('DivReq') > 0) {
                    lblObj.removeChild(lblObj.childNodes[i]);
                }
            }
            this.data.attrs.required = false;
          }
        //  break;
        //default: break;
      //}
    },

	/**
	 * adds some class to the input field....
	 *
	 * ...That is, makes the inline label invisible by causing the input field to have an opaque background
	 */
	removeLabel : function(evt) {
		if(this._inputField.value.length > 0) {
			YAHOO.util.Dom.addClass(this._inputField, 'filled');
		} else {
			YAHOO.util.Dom.removeClass(this._inputField, 'filled');
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
		eo.data = {
			'name'  : this.data.js.name,
			'value' : this._getValue(),
			'table' : this.data.js.table,
			'form'  : this._parentForm,
            'field' : this._inputField,
			'type'  : this.data.js.type,
            'w_id'  : this.data.info.w_id
		};
		if(this.data.js.customID) {
			eo.data.custom     = true;
			eo.data.customID   = this.data.js.customID;
			eo.data.customType = this.data.js.type;
		} else {
			eo.data.custom = false;
		}
        RightNow.Event.fire(eventName, eo);
    },

	getAncestorByCallback : function(parent) {
		if(YAHOO.util.Dom.hasClass(parent), "rn_Hidden") {
			return true;
		} else {
			return false;

		}
	},

	onVisibilityChange : function(type, args) {
		var controllerId = args[0].data.elementId;

        if (!YAHOO.util.Dom.isAncestor(controllerId, 'rn_' + this.instanceID))
            return;

        if (type === 'evt_hideElement')
            this._hidden = true;
        else
            this._hidden = false;

		if(this.data.js.optional_on_hide) {
			if(type == 'evt_hideElement') {
				this.data.attrs.required = false;
			} else {
				var hidingAncestor = YAHOO.util.Dom.getAncestorBy(document.getElementById("rn_" + this.instanceID), this.getAncestorByCallback);
				var controllerIsAncestorOfHider = YAHOO.util.Dom.isAncestor(controllerId, hidingAncestor)

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
                        }
                    // }
                }    
	},

	/**
	 * Event handler executed when form is being submitted
	 *
	 * @param type String Event name
	 * @param args Object Event arguments
	 */
	_onValidate : function(type, args) {
		this._validated = true;

                // console.log( "Name: " + this.data.js.name + ", Value: " + this._getValue() );

		this._parentForm = this._parentForm || RightNow.UI.findParentForm("rn_" + this.instanceID);
		var eo = new RightNow.Event.EventObject();
		eo.data = {
			"name"     : this.data.js.name,
			"value"    : this._getValue(),
			"table"    : this.data.js.table,
			"required" : (this.data.attrs.required ? true : false),
			"prev"     : this.data.js.prev,
			"form"     : this._parentForm
		};

		//if we're validating a single page on the form - and don't want to submit - then see if this element is on the page to validate
		if(args[0].data.tempValidationArea) {
			if(! YAHOO.util.Dom.isAncestor(args[0].data.tempValidationArea, this._inputField)) {
				RightNow.Event.fire("evt_formFieldValidateResponse", eo);
				RightNow.Event.fire("evt_formFieldCountRequest");
				return;
			}
		}

        // Do not validate or submit fields that are hidden by conditions like the hideElement widget.
        if (this._hidden)
        {
            eo.data.value = null;
            RightNow.Event.fire("evt_formFieldValidateResponse", eo);
		    RightNow.Event.fire("evt_formFieldCountRequest");
            return;
        }

		if(RightNow.UI.Form.form === this._parentForm) {
			this._formErrorLocation = args[0].data.error_location;
			this._trimField();

			if(this._compareInputToMask(true) && this._checkRequired() && this._checkData() && this._checkValue() && this._checkEmail() && this._checkUrl()) {
				YAHOO.util.Dom.removeClass(this._inputField, "rn_ErrorField");
				YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
				if(this.data.js.profile)
					eo.data.profile = true;
				if(this.data.js.customID) {
					eo.data.custom = true;
					eo.data.customID = this.data.js.customID;
					eo.data.customType = this.data.js.type;
				} else {
					eo.data.custom = false;
				}
				if(this.data.js.channelID) {
					eo.data.channelID = this.data.js.channelID;
				}
				eo.w_id = this.data.info.w_id;
				RightNow.Event.fire("evt_formFieldValidateResponse", eo);
			} else {
				RightNow.UI.Form.formError = true;
			}
		} else {
			RightNow.Event.fire("evt_formFieldValidateResponse", eo);
		}
		this._validated = false;
		RightNow.Event.fire("evt_formFieldCountRequest");
	},

	/**
	 * Validates that the input field has a value (if required) and that the value is
	 * of the correct format.
	 */
	_blurValidate : function() {
		this._formErrorLocation = null;
		if(this._onAccountExistsResponse._dialogShowing)
			return;

		this._trimField();
		if(this._checkRequired() && this._checkData() && this._checkValue() && this._checkEmail()) {
			if(this._fieldName === "login" || this._fieldName === "email" || this._fieldName === "email_alt1" || this._fieldName === "email_alt2") {
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
	_checkValue : function() {
		if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_INT) {
			//make sure it's a valid int
			if(this._inputField.value !== "" && (isNaN(Number(this._inputField.value)) || parseInt(this._inputField.value) !== parseFloat(this._inputField.value))) {
				this._displayError(RightNow.Interface.getMessage('VALUE_MUST_BE_AN_INTEGER_MSG'));
				return false;
			}
			//make sure it's value is in bounds
			if(this.data.js.maxVal || this.data.js.minVal) {
				var value = parseInt(this._inputField.value);
				if(this.data.js.maxVal && value > parseInt(this.data.js.maxVal)) {
					this._displayError(RightNow.Interface.getMessage('VALUE_IS_TOO_LARGE_MAX_VALUE_MSG') + this.data.js.maxVal + ")");
					return false;
				}
				if(this.data.js.minVal && value < parseInt(this.data.js.minVal)) {
					this._displayError(RightNow.Interface.getMessage('VALUE_IS_TOO_SMALL_MIN_VALUE_MSG') + this.data.js.minVal + ")");
					return false;
				}
			}
		} else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_PASSWORD && this.data.js.name !== "password" && this.data.js.name !== "organization_password" && this.data.js.passwordLength) {
			var length = RightNow.Text.Encoding.utf8Length(this._inputField.value), minLength = this.data.js.passwordLength;
			if(length < minLength) {
				this._displayError(RightNow.Text.sprintf(RightNow.Interface.getMessage("PCT_D_CHARACTERS_MSG"), minLength));
				return false;
			}
		}
		if(this.data.js.fieldSize) {
			//make sure it's within the max field size
			var length = RightNow.Text.Encoding.utf8Length(this._inputField.value), maxLength = this.data.js.field_size;

			if(maxLength < length) {
				var extra, errorString, roughMBCS = parseInt(length / (this._inputField.value.length)), numtokExp = new RegExp("%d");

				if(length % (this._inputField.value.length) !== 0)
					roughMBCS++;
				extra = parseInt(( length - maxLength) / roughMBCS);
				errorString = RightNow.Text.sprintf(RightNow.Interface.getMessage("EXCEEDS_SZ_LIMIT_PCT_D_CHARS_PCT_D_LBL"), numtokExp, parseInt(maxLength / roughMBCS));

				if(( length - maxLength) % (roughMBCS) !== 0)
					extra++;
				errorString = errorString.replace(numtokExp, extra);
				this._displayError(errorString);
				return false;
			}
		}

                // Is there a regex mask to be applied?.
                if( this._inputField.value != "" && this.data.attrs.regex_mask != "" )
                {
                    var pattern = new RegExp( this.data.attrs.regex_mask );
                    if( this._inputField.value.search( pattern ) == -1 )
                    {
                        this._displayError( "value is not in the correct format." );
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
	_checkData : function(silent) {
		var spacesRe = /\s/;
		if(this._inputField.value !== "") {
			if(this._fieldName === "login") {
				var quotesRe = /["']/;

				//check if username contains spaces
				if(spacesRe.test(this._inputField.value)) {
					if(!silent)
						this._displayError(RightNow.Interface.getMessage('CONTAIN_SPACES_PLEASE_TRY_MSG'));
					return false;
				}
				//check if username contains double quotes
				if(quotesRe.test(this._inputField.value)) {
					if(!silent)
						this._displayError(RightNow.Interface.getMessage('CONTAIN_QUOTE_CHARS_PLEASE_TRY_MSG'));
					return false;
				}
			} else if(this._fieldName === "ph_office" || this._fieldName === "ph_fax" || this._fieldName === "ph_home" || this._fieldName === "ph_asst" || this._fieldName === "ph_mobile" || this._fieldName === "postal_code") {
				var validInput = new RegExp("^[-A-Za-z0-9,# +.()]+$");
				if(!validInput.test(this._inputField.value)) {
					if(!silent) {
						if(this._fieldName === "postal_code")
							this._displayError(RightNow.Interface.getMessage("PCT_S_IS_AN_INVALID_POSTAL_CODE_MSG"));
						else
							this._displayError(RightNow.Interface.getMessage("PCT_S_IS_AN_INVALID_PHONE_NUMBER_MSG"));
					}
					return false;
				}
			}
			
			else if( this._fieldName === 'date_birth' )
			{
				// Check for a valid date string of MM/DD/YYYY.
				if( !this._validateDateString( this._inputField.value ) )
				{
					if( !silent )
						this._displayError( " must be in MM/DD/YYYY format." );

					return false;
				}

				// Make sure the date is not too far back.
				if( this.data.attrs.max_age > 0 && this._determineAge( this._inputField.value ) > this.data.attrs.max_age )
				{
					if( !silent )
						this._displayError( " must be sooner than " + this.data.attrs.max_age + " years ago." );
				}

				RightNow.Event.fire( "ps_formFieldAgeSetResponse", this._inputField.value );
			}
			//Check for space characters on channel fields
			else if(this.data.js.channelID && spacesRe.test(this._inputField.value)) {
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
	_checkEmail : function(silent) {
		if(!(this._fieldName === 'email' || this._fieldName === 'email_alt1' || this._fieldName === 'email_alt2' || this._fieldName === 'alternateemail' || this.data.js.email) || this._inputField.value === "")
			return true;
		if(this._fieldName === 'alternateemail') {
			var status = true;
			var emailArray = this._inputField.value.split(";");
			for(var i = 0; i < emailArray.length; i++) {
				emailArray[i] = YAHOO.lang.trim(emailArray[i]);
				status = (this._validateEmail(emailArray[i], silent) && status) ? true : false;
			}
			return status;
		} else {
			return this._validateEmail(this._inputField.value, silent);
		}
	},

	/**
	 * subroutine to validate against the regex
	 * @param value String the email address
	 * @param silent Boolean Optional parameter: set to true if the caller wishes to perform
	 */
	_validateEmail : function(value, silent) {
		if(!RightNow.Text.isValidEmailAddress(value)) {
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
	_checkUrl : function(silent) {
		if((this.data.js.customID) && (this.data.js.url) && !(this._inputField.value === "")) {
			if(!RightNow.Text.isValidUrl(this._inputField.value)) {
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
	_checkRequired : function() {
		if(this.data.attrs.required) {
			//label infield logic
			//if(this._inputField.value === "" || this._inputField.value == this.data.attrs.label_input)
			if(this._inputField.value === "") {
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
	_getValue : function() {
		if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_INT) {
			if(this._inputField.value !== "")
				return parseInt(this._inputField.value);
		}
		if(this.data.js.mask)
			return this._stripMaskFromFieldValue();
		//label infield logic
		//return (this._inputField.value == this.data.attrs.label_input) ? "" : this._inputField.value;
		
                // Scrub any Word special characters before returning.
                return this._scrubSpecialCharacters( this._inputField.value );

	},

        /**
         * Scrub any special characters that might have  been pasted into the field.
         * @param value String The value of text field being scrubbed.
         * @return String String with word special characters replaced.
         */
        _scrubSpecialCharacters: function( value )
        {
            var specialChars = [
                String.fromCharCode(8220), //.
                String.fromCharCode(8221), //.
                String.fromCharCode(8216), //.
                String.fromCharCode(8217), //.
                String.fromCharCode(8211), //.
                String.fromCharCode(8212), //.
                String.fromCharCode(189), //½
                String.fromCharCode(188), //¼
                String.fromCharCode(190), //¾
                String.fromCharCode(169), //©
                String.fromCharCode(174), //®
                String.fromCharCode(8230) //.
            ];

            var specialCharReplacements = [
                '"',
                '"',
                "'",
                "'",
                "-",
                "--",
                "1/2",
                "1/4",
                "3/4",
                "(C)",
                "(R)",
                "..."
            ];

            // Ensure the special character and special character replacement arrays are the same lenght 
            // before proceeding.
            if( specialChars.length == specialCharReplacements.length )
            {
                for( var counter = 0; counter < specialChars.length; counter++ )
                {
                    var regex = new RegExp( specialChars[counter], "gi" );
                    value = value.replace( regex, specialCharReplacements[counter] );
                }
            }
            else
            {
                throw new Error( "The length of the special character and replacement arrays do not match." );
            }

            return value;
      },

	/**
	 * Trims the value of the input field (removes leading / trailing whitespace).
	 */
	_trimField : function() {
		if(this._inputField.value !== "" && this.data.js.type !== RightNow.Interface.Constants.EUF_DT_PASSWORD)
			this._inputField.value = YAHOO.lang.trim(this._inputField.value);
		return true;
	},

	/**
	 * Shows hint on the input field's focus
	 * and hides the hint on the field's blur.
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
			YAHOO.util.Dom.insertAfter(hint, this._inputField);
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
		YAHOO.util.Dom.insertAfter(overlay, this._inputField);
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
        var commonErrorDiv, errorId, errorLink, label;
		commonErrorDiv = document.getElementById(this._formErrorLocation);
        errorId        = 'rn_ErrorMessage_' + this._inputField.id;
        label          = this.data.attrs.label_input;
        if (this.data.attrs.error_validation_label)
            label = this.data.attrs.error_validation_label;

        // Add error classes to the field.
		YAHOO.util.Dom.addClass(this._inputField, "rn_ErrorField");
		YAHOO.util.Dom.addClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");

        // Increment error count and handle chat case.
        RightNow.UI.Form.errorCount++;
        if(RightNow.UI.Form.chatSubmit && RightNow.UI.Form.errorCount === 1)
            commonErrorDiv.innerHTML = '';

        // Manipulate the error message, specific to this type of widget.
        if (errorMessage)
        {
            if (errorMessage.indexOf('%s') > -1)
                errorMessage = RightNow.Text.sprintf(errorMessage, label);
            else
                errorMessage = label + errorMessage;
        }
        if (this.data.attrs.error_msg)
            errorMessage = this.data.attrs.error_msg;

        // Create error message markup.
        errorLink = '<div><b><a id="' + errorId + '" href="javascript:void(0);" oncontextmenu="return false;">' + errorMessage + '</a></b></div>';

        // Add error message markup to the common error location.
        commonErrorDiv.innerHTML += errorLink;

        // Make error messages clickable.
        RightNow.Event.subscribe("evt_formFailValidationResponse",
            function() {
                YAHOO.util.Event.on(errorId, 'click', this._fireInteractionEvent, 'evt_clickErrorMessage', this);
            }, this
        );
	},

        /**
         * Event handler for when age should be calculated.
         */
        _onAgeSet : function( type, args )
        {
			var dob = args[0];

			if( this._validateDateString( dob ) )
			{	
				var age = this._determineAge( dob );

				if( age > 0 )
					this._inputField.value = age;
			}
        },

		/**
		 * Helper function to calculate the age.
		 *
		 * @param	dob	STRING	Previously validated string containing a date in MM/DD/YYY format.
		 * @return	INT	The age of an individual, after subtracting from today's date.
		 */
		_determineAge : function( dob )
		{
			var dobToken = dob.split( '/' );
			var year = parseInt( dobToken[2] );
			var month = parseInt( dobToken[0] );
			var day = parseInt( dobToken[1] );

			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth() + 1; //January is 0!
			var yyyy = today.getFullYear();

			var age = yyyy - year;
			// Has their birthday occured this year? If not, subtract one from their age.
			if( mm < month )
				age = age - 1;
			else if( mm == month && dd < day )
				age = age - 1;

			return age;
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
	_checkExistingAccount : function() {
		if(this._inputField.value === "" || this._inputField.value === this.data.js.prev || (this._fieldName.indexOf("email") > -1 && this._inputField.value.toLowerCase() === this.data.js.prev))
			return false;
		//static copy so we don't do a bunch of requests onblur if the value hasn't changed
		if(!this._checkExistingAccount._seenValue)
			this._checkExistingAccount._seenValue = this._inputField.value;
		else if(this._checkExistingAccount._seenValue === this._inputField.value)
			return false;
		else
			this._checkExistingAccount._seenValue = this._inputField.value;

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
	_onAccountExistsResponse : function(type, args) {
		RightNow.Event.unsubscribe("evt_formFieldAccountExistsResponse", this._onAccountExistsResponse);
		var results = args[0];
		if(results !== false && this._validated === false) {
			//add error indicators
			YAHOO.util.Dom.addClass("rn_" + this.instanceID + "_Label", "rn_ErrorLabel");
			YAHOO.util.Dom.addClass(this._inputField, "rn_ErrorField");
			//create action dialog with link to acct assistance page
			var handleOK = function() {
				warnDialog.hide();
				this._onAccountExistsResponse._dialogShowing = false;
				this._inputField.focus();
			};

			var buttons = [{
				text : RightNow.Interface.getMessage("OK_LBL"),
				handler : {
					fn : handleOK,
					scope : this
				},
				isDefault : true
			}];
			var dialogBody = document.createElement("div");
			dialogBody.innerHTML = results.message;
			var warnDialog = RightNow.UI.Dialog.actionDialog(RightNow.Interface.getMessage("WARNING_LBL"), dialogBody, {
				"buttons" : buttons,
				"width" : "250px"
			});
			this._onAccountExistsResponse._dialogShowing = true;
			warnDialog.show();
		} else {
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
	_onProvinceChange : function(type, args) {
		var eventObj = args[0], resetMask = false;

		if(!eventObj.states.length)
			this.data.js.mask = "";

		if((this._fieldName === "postal_code") && ("postal_mask" in eventObj)) {
			resetMask = true;
			this.data.js.mask = eventObj.postal_mask;
		} else if("phone_mask" in eventObj) {
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
	_initializeMask : function() {
		YAHOO.util.Event.addListener(this._inputField, "keyup", this._compareInputToMask, null, this);
		YAHOO.util.Event.addListener(this._inputField, "blur", this._hideMaskMessage, null, this);
		YAHOO.util.Event.addListener(this._inputField, "focus", this._compareInputToMask, null, this);
		this.data.js.mask = this._createMaskArray(this.data.js.mask);
		//Set up mask overlay
		var overlay = document.createElement("div");
        // set zindex high so no bleed through. FTSAI 20120905
        overlay.style.zIndex = 200;
		YAHOO.util.Dom.addClass(overlay, "rn_MaskOverlay");
		if(YAHOO.widget.Overlay) {
			this._maskNode = YAHOO.util.Dom.insertAfter(overlay, this._inputField);
			this._maskNode = new YAHOO.widget.Overlay(this._maskNode, {
				visible : false
			});
			this._maskNode.cfg.setProperty("context", [this._inputField, "tl", "bl", ["windowScroll"]]);
			this._maskNode.setBody("");
			this._maskNode.render();
		} else {
			YAHOO.util.Dom.addClass(overlay, "rn_Hidden");
			this._maskNode = overlay;
			YAHOO.util.Dom.insertAfter(this._maskNode, this._inputField);
		}

		if(this.data.attrs.always_show_mask) {
			//Write mask onto the page
			var maskMessageOnPage = this._getSimpleMaskString(), widgetContainer = document.getElementById("rn_" + this.instanceID);
			if(maskMessageOnPage && widgetContainer) {
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
	_createMaskArray : function(mask) {
		if(!mask)
			return;
		var maskArray = [];
		for(var i = 0, j = 0, size = mask.length / 2; i < size; i++) {
			maskArray[i] = mask.substring(j, j + 2);
			j += 2;
		}
		return maskArray;
	},

	/**
	 * Removes the mask from the field value.
	 * @return String The value without the mask
	 */
	_stripMaskFromFieldValue : function() {
		if(!this.data.js.mask || this._inputField.value === "")
			return this._inputField.value;

		var result = "";
		for(var i = 0; i < this._inputField.value.length; i++) {
			if(i < this.data.js.mask.length && this.data.js.mask[i].charAt(0) !== 'F')
				result += this._inputField.value.charAt(i);
		}
		return result;
	},

	/**
	 * Builds up simple mask string example based off of mask characters
	 */
	_getSimpleMaskString : function() {
		if(!this.data.js.mask)
			return "";
		var maskString = "";
		for(var i = 0; i < this.data.js.mask.length; i++) {
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
	_compareInputToMask : function(submitting) {
		if(!this.data.js.mask)
			return true;
		var error = [];
		if(this._inputField.value.length > 0) {
			for(var i = 0, tempRegExVal; i < this._inputField.value.length; i++) {
				if(i < this.data.js.mask.length) {
					tempRegExVal = "";
					switch(this.data.js.mask[i].charAt(0)) {
						case 'F':
							if(this._inputField.value.charAt(i) !== this.data.js.mask[i].charAt(1))
								error.push([i, this.data.js.mask[i]]);
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
						error.push([i, this.data.js.mask[i]]);
				} else {
					error.push([i, "LEN"]);
				}
			}
			//input matched mask but length didn't match up
			if((!error.length) && (this._inputField.value.length < this.data.js.mask.length) && (!this.data.attrs.always_show_mask || submitting === true)) {
				for(var i = this._inputField.value.length; i < this.data.js.mask.length; i++)error.push([i, "MISS"]);
			}
			if(error.length > 0) {
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
	_showMaskMessage : function(error) {
		if(error === null) {
			this._hideMaskMessage();
		} else {
			if(!this._showMaskMessage._maskMessages) {
				//set a static variable containing error messages so it's lazily defined once across widget instances
				this._showMaskMessage._maskMessages = {
					"F" : RightNow.Interface.getMessage('WAITING_FOR_CHARACTER_LBL'),
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
					"MISS" : RightNow.Interface.getMessage('THE_INPUT_IS_TOO_SHORT_MSG')
				};
			}
			var message = "", sampleMaskString = this._getSimpleMaskString().split("");
			for(var i = 0, type; i < error.length; i++) {
				type = error[i][1];
				//F means format char should have followed
				if(type.charAt(0) === "F") {
					message += "<b>" + RightNow.Interface.getMessage('CHARACTER_LBL') + " " + (error[i][0] + 1) + "</b> " + RightNow.Interface.getMessage('WAITING_FOR_CHARACTER_LBL') + type.charAt(1) + " ' <br/>";
					sampleMaskString[(error[i][0])] = "<span style='color:#F00;'>" + sampleMaskString[(error[i][0])] + "</span>";
				} else {
					if(type !== "MISS") {
						message += "<b>" + RightNow.Interface.getMessage('CHARACTER_LBL') + " " + (error[i][0] + 1) + "</b> " + this._showMaskMessage._maskMessages[type] + "<br/>";
						if(type !== "LEN") {
							sampleMaskString[(error[i][0])] = "<span style='color:#F00;'>" + sampleMaskString[(error[i][0])] + "</span>";
						} else {
							break;
						}
					}
				}
			}
			sampleMaskString = sampleMaskString.join("");
			this._setMaskMessage(RightNow.Interface.getMessage('EXPECTED_INPUT_LBL') + ": " + sampleMaskString + "<br/>" + message);
			this._showMask();
		}
	},

	/**
	 * Sets mask message.
	 * @param message String message to set
	 */
	_setMaskMessage : function(message) {
		((this._maskNode.body) ? this._maskNode.body : this._maskNode).innerHTML = message;
	},

	/**
	 * Shows mask message.
	 */
	_showMask : function() {
		if(this._maskNode.show)
			this._maskNode.show();
		else
			YAHOO.util.Dom.removeClass(this._maskNode, "rn_Hidden");
	},

	/**
	 * Hides mask message.
	 */
	_hideMaskMessage : function() {
		if(this._maskNode.cfg && this._maskNode.cfg.getProperty("visible") !== false)
			this._maskNode.hide();
		else if(!this._maskNode.cfg)
			YAHOO.util.Dom.addClass(this._maskNode, "rn_Hidden");
	},

	/**
	 * Count characters and ensure the max
	 */
	_countChars : function() {
				var exceededLength = false;
				var dontUnflag = false;
				
				
				
                var length = RightNow.Text.Encoding.utf8Length(this._inputField.value), 
                    maxLength = this.data.attrs.max_chars, 
                    currentLength = this._inputField.value.length;
				if(this._countField.style.color=='red' && length == maxLength)
				{
					dontUnflag = true;
				}
                if( length > maxLength )
                {
					exceededLength = true;
                    var currentValue = this._inputField.value;
                    while( length > maxLength )
                    {
                        currentValue = currentValue.substring( 0, currentValue.length - 1 );
                        length = RightNow.Text.Encoding.utf8Length( currentValue );
                        currentLength = currentValue.length;
                    }

                    this._inputField.value = currentValue;
					
                }

                
                /*
                if(this._inputField.value.length > this.data.attrs.max_chars)
			this._inputField.value = this._inputField.value.substring(0, this.data.attrs.max_chars);

		this._countField.innerHTML = (this.data.attrs.max_chars - this._inputField.value.length) + " " + this.data.attrs.max_chars_label;
                */
				
					if(exceededLength)
					{
						this._countField.innerHTML = ( maxLength - length ) + " " + this.data.attrs.max_chars_label + " - your text has exceeded the max length and has been truncated";
						this._countField.style.color='red';
					}
					else if(!dontUnflag)
					{
						this._countField.innerHTML = ( maxLength - length ) + " " + this.data.attrs.max_chars_label;
						this._countField.style.color='black';
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
        var name  = args[0].data.name;

        if (name !== this.data.js.name)
            return;

        // When the field becomes visible, focus it.
        RightNow.Event.subscribe('evt_changeStep', function() {try{field.focus();}catch(err){}});
        try{field.focus();}catch(err){}
    },

	/**
	 * Validate date string
	 *
	 */
	_validateDateString : function( value )
	{
		if( value == '' )
		{
			// console.log( "empty data string value." );
			return false;
		}

		var datePattern = new RegExp( "^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$" );
		if( value.search( datePattern ) == -1 )
		{
			// console.log( "Date string failed regex comparison. '" + value + "'" );
			return false;
		}

		// Now that we know the string is formatted like a date, is it?
		var token = value.split( '/' );
		var month = parseInt( token[0] );
		var day = parseInt( token[1] );
		var year = parseInt( token[2] );
		
		if( month > 12 )
		{
			// console.log( "Date failed month out of bounds test. Month: " + month + ", Day: " + day + ", Year: " + year );
			return false;
		}
		else if( month == 2 ) 
		{
			if( day == 29 ) 
			{
				if( year % 4 != 0 || year % 100 == 0 && year % 400 != 0 )
				{
					// console.log( "Date failed February test. Month: " + month + ", Day: " + day + ", Year: " + year );
					return false;
				}
			}
			else if( day > 28 )
			{
				// console.log( "Date failed February test. Month: " + month + ", Day: " + day + ", Year: " + year );
				return false;
			}
		}
		else if( month == 4 || month == 6 || month  == 9 || month == 11 ) 
		{
			if( day > 30 )
			{
				// console.log( "Date failed 30 day month test. Month: " + month + ", Day: " + day + ", Year: " + year );
				return false;
			}
		}
		else 
		{
			if( day > 31 ) 
			{
				// console.log( "Date failed 31 day month test. Month: " + month + ", Day: " + day + ", Year: " + year );
				return false;
			}
		}

		return true;
	},

    /**
     * Validate CC Card values
     *
     */
    _validateCCvalue : function(type, args) {
        // lookup bank info
        this._ccLookup(type, this._inputField.value.substring(0,8));
    },
    _ccLookup: function(type, args)
    {
        document.getElementById("vcc_bank_info").className = "";

        //console.log(args);
        var postData = {
            "f_tok": RightNow.UI.Form.formToken,
            "cc_info": args
        };
        var requestOptions = {
            successHandler: function(response)
            {
                var eventObject = new RightNow.Event.EventObject();
                eventObject.data = RightNow.JSON.parse(response.responseText);
                RightNow.Event.fire("evt_ccInfoUpdate", eventObject);
            },
            scope: this,
            /** #inner */
            failureHandler : function(o) {
                // cleanse error: output a more useful message
                if (o.status === 418 && o.argument && o.argument.eventName)
                    RightNow.Event.fire(o.argument.eventName, {
                        "message" : RightNow.Interface.getMessage("ERR_SUBMITTING_FORM_DUE_INV_INPUT_LBL")
                    });
            }
        };
        RightNow.Ajax.makeRequest("/cc/ajaxCustom/ccLookup", postData, requestOptions);
    },
    _ccInfoTryRevert: function(type, args)
    {
        this._inputField.value = document.getElementById('review_' + this._fieldName + '_copy').innerHTML;
        this._inputField.className = "rn_Text filled";
        //this._setObjVal(type, 'review_' + this._fieldName);
        var eo = new RightNow.Event.EventObject();
        eo.data = {"name" : this.data.js.name,
                   "value" : this._inputField.value,
                   "table" : this.data.js.table,
                   "required" : (this.data.attrs.required ? true : false),
                   "prev" : this.data.js.prev,
                   "custom" : true};
        RightNow.Event.fire("evt_toggleFormElement", eo);

        this._inputField.disabled = true;
    },
    _ccInfoEnable: function(type, args)
    {
        // not mybank is checked so we need to null out the fields update fields in review section
		// 2013.05.15 T. Woodham: ...unless it's the org ID in a successful BIN number lookup.
		if( this._fieldName != 'cc_org_id' )
		{
			this._inputField.value = "";
			this._inputField.className = "rn_Text";
			//this._setObjVal(type, 'review_' + this._fieldName);
		}
        var eo = new RightNow.Event.EventObject();
        eo.data = {"name" : this.data.js.name,
                   "value" : this._inputField.value,
                   "table" : this.data.js.table,
                   "required" : (this.data.attrs.required ? true : false),
                   "prev" : this.data.js.prev,
                   "custom" : true};
        RightNow.Event.fire("evt_toggleFormElement", eo);
        
        this._inputField.disabled = false;
        //this._onLabelHandlerBlur();
        // hide fields in review section
        //document.getElementById('review_' + this._fieldName + '_span').className = "rn_Hidden";
    },
    _ccInfoUpdate: function(type, args)
    {
        var data = args[0].data[0];
        if (data)
        {
            // return false;

            var value = null;
            switch (this._fieldName)
            {
                case "cc_org_id":
                    value = data.ID;
                    break;
                case "cc_co_name":
                    value = data.Name;
                    break;
                case "cc_issuer_phone":
                    value = data.c$cc_issuer_phone;
                    break;
                case "cc_issuer_website":
                    value = data.c$cc_issuer_website;
                    break;
                default: break;
            }

            if (value != null) {
                this._inputField.value = value;
                this._inputField.className = "rn_Text filled";

				// 2013.05.15 T. Woodham: If BIN number lookup succeeds, it wins against auto-routing.
				if( this._fieldName == 'cc_org_id' )
				{
					this._allowAutorouteUpdate = false;
				}
            }
			else if ( value == null && this._fieldName == 'cc_org_id' )
			{
				this._allowAutorouteUpdate = true;
			}

            var eo = new RightNow.Event.EventObject();
            eo.data = {"name" : this.data.js.name,
                       "value" : value,
                       "table" : this.data.js.table,
                       "required" : (this.data.attrs.required ? true : false),
                       "prev" : this.data.js.prev,
                       "custom" : true};
            RightNow.Event.fire("evt_toggleFormElement", eo);
            //this._setObjVal(type, 'review_' + this._fieldName);
            // save copy in case we uncheck is not my card
            this._setObjVal(type, 'review_' + this._fieldName + '_copy');
            this._inputField.disabled = true;
            document.getElementById("vcc_is_not_mybank").className = "";
            RightNow.Event.fire("evt_ccInfoUpdated");
        }
        else
        {
			this._allowAutorouteUpdate = true;
            document.getElementById("vcc_is_not_mybank").className = "rn_Hidden";
        }
    },    
    /**
     * set specified form element fields value by element id
     * copied from _ccInfoUpdate, but modified for autorouting scenarios
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _autorouteUpdate: function(type, args)
    {
        var data = args[0];
        if (!data)
        {
            this._inputField.value = '';
            this._inputField.className = "rn_Text";
            this._inputField.disabled = false;
        }
        else 
        {
          var value = null;
          switch (this._fieldName)
          {
            case "cc_org_id":
				if( typeof data.ID !== 'undefined' && this._allowAutorouteUpdate === true )
	                value = data.ID;
                break;
            case "cc_co_name":
				if( typeof data.Name !== 'undefined' )
					value = data.Name;
                break;
            case "cc_issuer_addr1":
				if( typeof data.Street !== 'undefined' )
	                value = data.Street;
                break;
            case "cc_issuer_city":
				if( typeof data.City !== 'undefined' )
	                value = data.City;
                break;
            case "cc_issuer_zip":
				if( typeof data.PostalCode !== 'undefined' )
					value = data.PostalCode;
                break;

            default: break;
          }
        
            this._inputField.disabled = false;

			if( value !== null )
			{
				this._inputField.value = value;
				; // this._inputField.disabled = true;
			}
			else
			{
				; // this._inputField.disabled = false;
			}

			if( this._inputField.value )
				this._inputField.className = "rn_Text filled";
			else
				this._inputField.className = "rn_Text";
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
     * set specified form element fields value by element id
     *
     * @param type String Event name
     * @param args Object Event arguments
     */
    _setObjVal: function(type, args)
    {
        var obj = document.getElementById(args);
        if (obj) {
            if (this._inputField.value != null && this._inputField.value != '')
            {
                obj.innerHTML = this._inputField.value;
            } else {
                obj.innerHTML = "";
            }
        }
    }
};
