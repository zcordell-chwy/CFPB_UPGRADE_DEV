RightNow.Widget.SelectionLogicInput = function(data, instanceID){

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
    //province changing
    if(fieldName === "country_id") {
        YAHOO.util.Event.addListener(this._inputField,"change", this._countryChanged, null, this);
        if (this._inputField.value != "")
            this._countryChanged();
    }
    else if(fieldName === "prov_id")
        RightNow.Event.subscribe("evt_formFieldProvinceResponse", this._onProvinceResponse, this);
                
    this.step1_comp = document.getElementById('step1_comp');
    this.step1_comp_update = document.getElementById('step1_comp_update');
    this.step1_cfpb = document.getElementById('step1_cfpb');
    
    this.step2 = document.getElementById('step2');
    this.step2_desc = document.getElementById('step2_desc');
    // duplicate
    this.step2_duplicate = document.getElementById('step2_duplicate');
    // regulator
    this.step2_regulator = document.getElementById('step2_regulator');
	this.step2_redirect_explain = document.getElementById('step2_redirect_explain');
    // company 
    this.step2_comp = document.getElementById('step2_comp');
    this.step2_comp_relief = document.getElementById('step2_comp_relief');
    this.step2_comp_relief_amount = document.getElementById('step2_comp_relief_amount');
    this.step2_comp_redirect = document.getElementById( 'step2_comp_redirect' );
    this.step2_comp_info = document.getElementById('step2_comp_info');
    this.step2_comp_explain = document.getElementById('step2_comp_explain');
	this.step2_comp_dispute_filed = document.getElementById('step2_comp_dispute_filed');
    // cfpb  
    this.step2_cfpb = document.getElementById('step2_cfpb');
    this.step2_cfpb_relief = document.getElementById('step2_cfpb_relief');
    this.step2_cfpb_relief_amount = document.getElementById('step2_cfpb_relief_amount');
    this.step2_cfpb_info = document.getElementById('step2_cfpb_info');
    this.step2_cfpb_explain = document.getElementById('step2_cfpb_explain');

    //var span_comp_provide_a_response = document.getElementById('span_comp_provide_a_response');
    //var span_cfpb_provide_a_response = document.getElementById('span_cfpb_provide_a_response');
    var div_response = document.getElementById('div_response');

    var evtObj = new RightNow.Event.EventObject();
        evtObj.data = {
            "inputField" : this._inputField,
            "w_id" : this.instanceID
        };

    switch(fieldName) {
        case "bank_statuses":
            RightNow.Event.subscribe("evt_setBankStatus", function(type, args) {
                var newStatus = args[0].data['inputField'].options[args[0].data['inputField'].selectedIndex].text;
                for (var i = 0; i < this._inputField.options.length; i++) {
                    var bankStatus = this._inputField.options[i].text;
                    if (bankStatus.indexOf(newStatus) == 0) {
                        this._inputField.selectedIndex = i;
                        break;
                    }
                }
            }, this);

            break;
        case "company_status_1":
            YAHOO.util.Event.addListener(this._inputField, "change", function() { 
                this._showDescription(this._inputField.options[this._inputField.selectedIndex].text);
                // fire event to set bank_status
                RightNow.Event.fire("evt_setBankStatus", evtObj);
                //console.log(this._inputField.options[this._inputField.selectedIndex].text);                
                switch (this._inputField.options[this._inputField.selectedIndex].text) {
                    case this.data.attrs.co_status['CO_STATUS_DUPLICATE_CASE']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showDuplicate");
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_duplicate.className = "";
						
                        break;
                    case this.data.attrs.co_status['CO_STATUS_SENT_TO_REGULATOR']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showRegulator");
                        RightNow.Event.fire("evt_showRedirectExplain");
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_regulator.className = "";
						this.step2_redirect_explain.className = "";
                       
                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;
                    
                    case this.data.attrs.co_status['CO_STATUS_REDIRECTED']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire( "evt_showOrgComplaintHandler" );
                        RightNow.Event.fire( "evt_showRedirectExplain" );
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_redirect.className = "";
                        this.step2_redirect_explain.className = "";
						
                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;
                                       
                    //case 'Closed with relief':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompRelief");
                        RightNow.Event.fire("evt_showCompReliefAmount");
                        RightNow.Event.fire("evt_showCompInfo");
                        RightNow.Event.fire("evt_showCompExplanation");
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_relief.className = "";
                        this.step2_comp_relief_amount.className = "";
                        this.step2_comp_info.className = "";
                        this.step2_comp_explain.className = "";
                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';
                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompRelief");
                        RightNow.Event.fire("evt_showCompInfo");
                        RightNow.Event.fire("evt_showCompExplanation");
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_relief.className = "";
                        this.step2_comp_info.className = "";
                        this.step2_comp_explain.className = "";
                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';
                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    //case 'Closed without relief':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_EXPLANATION']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompInfo");
                        RightNow.Event.fire("evt_showCompExplanation");
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_info.className = "";
                        this.step2_comp_explain.className = "";

                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    // case 'Closed':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompInfo");
                        RightNow.Event.fire("evt_showCompExplanation");

                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_info.className = "";
                        this.step2_comp_explain.className = "";
if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';
                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;
                    case this.data.attrs.co_status['CO_STATUS_IN_PROGRESS']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompInfo");
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_info.className = "";
                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    case this.data.attrs.co_status['CO_STATUS_INCORRECT_COMPANY']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompExplanation");

                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_explain.className = "";
					
                        break;
                    case 'Misdirected':
                    case this.data.attrs.co_status['CO_STATUS_ALERTED_CFPB']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompExplanation");
                        
                        // set visibility
                        this.step1_comp.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_explain.className = "";

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    default:
                        // set required fields
                        this._resetRequired();
                        
                        // set visibility
                        this.step1_comp.className = "";
                        break;
                }
            }, null, this);
            break;
        case "company_status_2":
            YAHOO.util.Event.addListener(this._inputField, "change", function() { 
                this._showDescription(this._inputField.options[this._inputField.selectedIndex].text);
                // fire event to set bank_status
                RightNow.Event.fire("evt_setBankStatus", evtObj);
                //console.log(this._inputField.options[this._inputField.selectedIndex].text);
                switch (this._inputField.options[this._inputField.selectedIndex].text) {                    
                    case this.data.attrs.co_status['CO_STATUS_DUPLICATE_CASE']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showDuplicate");
                        
                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_duplicate.className = "";
                        break;
                    case this.data.attrs.co_status['CO_STATUS_SENT_TO_REGULATOR']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showRegulator");
                        RightNow.Event.fire( "evt_showRedirectExplain" );
                        
                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_regulator.className = "";
						this.step2_redirect_explain.className = "";

                        // toggle attachment view
                        this._toggleAttachment('show');                        
                        break;
                    case this.data.attrs.co_status['CO_STATUS_REDIRECTED']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire( "evt_showOrgComplaintHandler" );
                        RightNow.Event.fire( "evt_showRedirectExplain" );
                        
                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_redirect.className = "";
						this.step2_redirect_explain.className = "";

                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;
                    //case 'Closed with relief':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompRelief");
                        RightNow.Event.fire("evt_showCompReliefAmount");
                        RightNow.Event.fire("evt_showCompExplanation");

                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_relief.className = "";
                        this.step2_comp_relief_amount.className = "";
                        this.step2_comp_explain.className = "";
                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompRelief");
                        RightNow.Event.fire("evt_showCompExplanation");

                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_relief.className = "";
                        this.step2_comp_explain.className = "";
                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';

                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;
                    //case 'Closed without relief':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_EXPLANATION']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompExplanation");

                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_explain.className = "";

                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';

                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;
                    // case 'Closed':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompExplanation");

                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_explain.className = "";
                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    case this.data.attrs.co_status['CO_STATUS_INCORRECT_COMPANY']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompExplanation");

                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_explain.className = "";
                        break;
                    case 'Misdirected':
                    case this.data.attrs.co_status['CO_STATUS_ALERTED_CFPB']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCompExplanation");
                        
                        // set visibility
                        this.step1_comp_update.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_explain.className = "";

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    default:
                        // set required fields
                        this._resetRequired();
                        
                        // set visibility
                        this.step1_comp_update.className = "";
                        break;
                }
            }, null, this);
            break;
        case "cfpb_status":
            YAHOO.util.Event.addListener(this._inputField, "change", function() { 
                this._showDescription(this._inputField.options[this._inputField.selectedIndex].text);
                // fire event to set bank_status
                RightNow.Event.fire("evt_setBankStatus", evtObj);
                //console.log(this._inputField.options[this._inputField.selectedIndex].text);
                switch (this._inputField.options[this._inputField.selectedIndex].text) {
                    case this.data.attrs.co_status['CO_STATUS_DUPLICATE_CASE']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showDuplicate");
                        
                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_duplicate.className = "";
                        break;
                    case this.data.attrs.co_status['CO_STATUS_SENT_TO_REGULATOR']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showRegulator");
                        RightNow.Event.fire( "evt_showRedirectExplain" );
                        
                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_regulator.className = "";
						this.step2_redirect_explain.className = "";

                        // toggle attachment view
                        this._toggleAttachment('show');                                                
                        break;
                    case this.data.attrs.co_status['CO_STATUS_REDIRECTED']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire( "evt_showOrgComplaintHandler" );
                        RightNow.Event.fire( "evt_showRedirectExplain" );

                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_comp.className = "";
                        this.step2_comp_redirect.className = "";
                        this.step2_comp_explain.className = "";
						this.step2_redirect_explain.className = "";

                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;
                    //case 'Closed with relief':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']:
                        // set required fields
                        this._resetRequired();
                        
                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_cfpb.className = "";
                        this.step2_cfpb_relief.className = "";
                        this.step2_cfpb_relief_amount.className = "";
                        // we need to look at initial response from comp
                        //if (span_comp_provide_a_response.className != 'rn_Hidden') {
                        if (div_response.className != 'rn_Hidden') {
                            RightNow.Event.fire("evt_showCFPBRelief");
                            RightNow.Event.fire("evt_showCFPBReliefAmount");
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "rn_Hidden";
                        } else {
                            RightNow.Event.fire("evt_showCFPBRelief");
                            RightNow.Event.fire("evt_showCFPBReliefAmount");
                            RightNow.Event.fire("evt_showCFPBInfo");
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "";
                        }
                        this.step2_cfpb_explain.className = "";

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF']:
                        // set required fields
                        this._resetRequired();
                        
                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_cfpb.className = "";
                        this.step2_cfpb_relief.className = "";
                        // we need to look at initial response from comp
                        //if (span_comp_provide_a_response.className != 'rn_Hidden') {
                        if (div_response.className != 'rn_Hidden') {
                            RightNow.Event.fire("evt_showCFPBRelief");
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "rn_Hidden";
                        } else {
                            RightNow.Event.fire("evt_showCFPBRelief");
                            RightNow.Event.fire("evt_showCFPBInfo");
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "";
                        }
                        this.step2_cfpb_explain.className = "";

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    //case 'Closed without relief':
                    case this.data.attrs.co_status['CO_STATUS_CLOSED_W_EXPLANATION']:
                        // set required fields
                        this._resetRequired();

                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_cfpb.className = "";
                        //if (span_cfpb_provide_a_response.className != 'rn_Hidden') {
                        if (div_response.className != 'rn_Hidden') {
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "rn_Hidden";
                        } else {
                            RightNow.Event.fire("evt_showCFPBInfo");
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "";
                        }
                        this.step2_cfpb_explain.className = "";

                        if( this.step2_comp_dispute_filed )
                            this.step2_comp_dispute_filed.className = '';

                        // toggle attachment view
                        this._toggleAttachment('show');
                        break;

                    case this.data.attrs.co_status['CO_STATUS_CLOSED']:
                        // set required fields
                        this._resetRequired();
                        
                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_cfpb.className = "";
                        //if (span_cfpb_provide_a_response.className != 'rn_Hidden') {
                        if (div_response.className != 'rn_Hidden') {
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "rn_Hidden";
                        } else { 
                            RightNow.Event.fire("evt_showCFPBInfo");
                            RightNow.Event.fire("evt_showCFPBExplanation");
                            this.step2_cfpb_info.className = "";
                        }
                        this.step2_cfpb_explain.className = "";

                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    case this.data.attrs.co_status['CO_STATUS_INCORRECT_COMPANY']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCFPBExplanation");
                        
                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_cfpb.className = "";
                        this.step2_cfpb_explain.className = "";
                        break;
                    case 'Misdirected':
                    case this.data.attrs.co_status['CO_STATUS_ALERTED_CFPB']:
                        // set required fields
                        this._resetRequired();
                        RightNow.Event.fire("evt_showCFPBExplanation");
                        
                        // set visibility
                        this.step1_cfpb.className = "";
                        this.step2.className = "";
                        this.step2_cfpb.className = "";
                        this.step2_cfpb_explain.className = "";
                        
                        // toggle attachment view 
                        this._toggleAttachment('show');
                        break;
                    default:
                        // set required fields
                        this._resetRequired();

                        // set visibility
                        this.step1_cfpb.className = "";
                        break;
                }
            }, null, this);
            break;
            
         default:
            break;
                
    }
};

RightNow.Widget.SelectionLogicInput.prototype = {
/**
 * ----------------------------------------------
 * Form / UI Events and Functions:
 * ----------------------------------------------
 */

    /**
     * Toggle attachment view between incident and investigation
     *
     */
    _toggleAttachment: function(type) {
        switch (type) {
            case 'incident':
                RightNow.Event.fire("evt_setAttachIncident");
                break;
            case 'investigation':
                RightNow.Event.fire("evt_setAttachInvestigation");
                break;
            case 'hide':
                RightNow.Event.fire("evt_hideAttach");
                break;
            case 'show':
                RightNow.Event.fire("evt_showAttach");
                break;
        }
    },

    /**
     * Show descriptions
     *
     */
    _showDescription: function(str) {
        // show descriptions
        switch (str) { 
            /*
            case 'Closed with relief':
                this.step2_desc.innerHTML = this.data.attrs.closed_with_relief_desc;
                break;
            case 'Closed without relief':
                this.step2_desc.innerHTML = this.data.attrs.closed_without_relief_desc;
                break;
            */
            case this.data.attrs.co_status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_CLOSED_W_MONETARY_RELIEF'];
                break;
            case this.data.attrs.co_status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_CLOSED_W_NON_MONETARY_RELIEF'];
                break;
            case this.data.attrs.co_status['CO_STATUS_CLOSED_W_EXPLANATION']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_CLOSED_W_EXPLANATION'];
                break;
            case this.data.attrs.co_status['CO_STATUS_CLOSED']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_CLOSED'];
                break;
            case this.data.attrs.co_status['CO_STATUS_IN_PROGRESS']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_IN_PROGRESS'];
                break;
            case this.data.attrs.co_status['CO_STATUS_INCORRECT_COMPANY']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_INCORRECT_COMPANY'];
                break;
            case this.data.attrs.co_status['CO_STATUS_MISDIRECTED']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_MISDIRECTED'];
                break;
            case this.data.attrs.co_status['CO_STATUS_ALERTED_CFPB']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_ALERTED_CFPB'];
                break;
            case this.data.attrs.co_status['CO_STATUS_DUPLICATE_CASE']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_DUPLICATE_CASE'];
                break;
            case this.data.attrs.co_status['CO_STATUS_REDIRECTED']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_REDIRECTED'];
                break;
            case this.data.attrs.co_status['CO_STATUS_SENT_TO_REGULATOR']:
                this.step2_desc.innerHTML = this.data.attrs.co_status_desc['CO_DESC_SENT_TO_REGULATOR'];
                break;
        }
    }, 

    /**
     * Rest all fields to not required
     *
     */
    _resetRequired: function(type, args)
    {
        // these events will make the field not required
        RightNow.Event.fire("evt_hideCompRelief");
        RightNow.Event.fire("evt_hideCompReliefAmount");
        RightNow.Event.fire("evt_hideCompInfo");
        RightNow.Event.fire("evt_hideCompExplanation");
        RightNow.Event.fire("evt_hideCFPBRelief");
        RightNow.Event.fire("evt_hideCFPBReliefAmount");
        RightNow.Event.fire("evt_hideCFPBInfo");
        RightNow.Event.fire("evt_hideCFPBExplanation");
        RightNow.Event.fire("evt_hideDuplicate");
        RightNow.Event.fire("evt_hideRegulator");
        RightNow.Event.fire("evt_hideOrgComplaintHandler");
		RightNow.Event.fire("evt_hideRedirectExplain");

        // hide all relative elements
        this.step1_comp.className = "rn_Hidden";
        this.step1_comp_update.className = "rn_Hidden";
        
        this.step2.className = "rn_Hidden";
        this.step2_duplicate.className = "rn_Hidden";
        this.step2_regulator.className = "rn_Hidden";
        this.step2_redirect_explain.className = "rn_Hidden";
        
        this.step2_comp.className = "rn_Hidden";
        this.step2_comp_relief.className = "rn_Hidden";
        this.step2_comp_relief_amount.className = "rn_Hidden";
        this.step2_comp_info.className = "rn_Hidden";
        this.step2_comp_explain.className = "rn_Hidden";
        this.step2_comp_redirect.className = "rn_Hidden";
    
        this.step2_cfpb.className = "rn_Hidden";
        this.step2_cfpb_relief.className = "rn_Hidden";
        this.step2_cfpb_relief_amount.className = "rn_Hidden";
        this.step2_cfpb_info.className = "rn_Hidden";
        this.step2_cfpb_explain.className = "rn_Hidden";

		if( this.step2_comp_dispute_filed )
			this.step2_comp_dispute_filed.className = 'rn_Hidden';

        // toggle attachment view
        this._toggleAttachment('hide');
    },

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
		var returnValue = null;

		// Only return a value if the field is visible.
		if( this._inputField.offsetHeight === 0 )
		{
			returnValue = null;
		}
		else
		{
			if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_RADIO)
			{
				if (this.data.attrs.is_checkbox == true) {
					if (this._inputField.checked === true) {
						returnValue = true;
					} else {
						returnValue = false;
					}
				} else {
					if(this._inputField[0].checked)
						returnValue = this._inputField[0].value;
					if(this._inputField[1].checked)
						returnValue = this._inputField[1].value;
				}
			}
			else if(this.data.js.type === RightNow.Interface.Constants.EUF_DT_CHECK)
			{
				returnValue = this._inputField.value === "1";
			}
			else
			{
				//select value
				returnValue = this._inputField.value;
			}
		}

		// If we should only return a value with a positive value and the value isn't true, clear it out.
		if( this.data.attrs.submit_positive_value_only === true && returnValue != true )
		{
			returnValue = null;
		}

		return returnValue;
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
            var labelStr = (this.data.attrs.label_nothing_selected) ? this.data.attrs.label_nothing_selected : this.data.attrs.label_input;
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
            if (value == '') // try innerText if label is empty (work-around for IE issue)
            {
                value = this._inputField.options[this._inputField.selectedIndex].innerText;
            }
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
    _onProvinceResponse: function(type, args)
    {

        var evtObj = args[0],
            options = this._inputField.options,
            aNewOption, i;
        if(evtObj.states)
        {
            options.length = 0;
            //evtObj.states.unshift({val: "--", id: ""});
            evtObj.states.unshift({
                val: "State", 
                id: ""
            });
            for(i = 0; i < evtObj.states.length; i++)
            {
                aNewOption = document.createElement("option");
                aNewOption.text = evtObj.states[i].val;
                aNewOption.value = evtObj.states[i].id;
                if (i == this.data.js.prev)
                    aNewOption.selected = "selected";
                options.add(aNewOption);
            }
        }
    }
};
