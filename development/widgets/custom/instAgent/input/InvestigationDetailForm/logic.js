RightNow.Widget.InvestigationDetailForm = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;

    this.divDelinquent = document.getElementById('div_delinquent');
    this.divExplanation = document.getElementById('div_explanation');
    this.divRelief = document.getElementById('div_relief');
    this.divResponse = document.getElementById('div_response');
    this.divReview = document.getElementById('div_rewiew_history');
    
    switch (this.data.js.bank_statuses) {
        // archive
        case this.data.attrs.co_status['CO_STATUS_CLOSED_W_RELIEF']:
        case this.data.attrs.co_status['CO_STATUS_CLOSED_WO_RELIEF']:
        case this.data.attrs.co_status['CO_STATUS_FULL_RESOLUTION']:
        case this.data.attrs.co_status['CO_STATUS_PARTIAL_RESOLUTION']:
        case this.data.attrs.co_status['CO_STATUS_NO_RESOLUTION']:
        // added new statuses
        case this.data.attrs.co_status['CO_STATUS_CLOSED_W_MONETARY_RELIEF']:
        case this.data.attrs.co_status['CO_STATUS_CLOSED_W_NON_MONETARY_RELIEF']:
        case this.data.attrs.co_status['CO_STATUS_CLOSED_W_EXPLANATION']:
        case this.data.attrs.co_status['CO_STATUS_CLOSED']:
        case this.data.attrs.co_status['CO_STATUS_DELINQUENT_RESPONSE']:
            this._initResponse();
            break;
        case this.data.attrs.co_status['CO_STATUS_INFO_PROVIDED']:
        case this.data.attrs.co_status['CO_STATUS_ALERTED_CFPB']:
        case this.data.attrs.co_status['CO_STATUS_DUPLICATE_CASE']:
        // Moving redirected to anotother portion of this switch statement so the user can enter data.
        // case this.data.attrs.co_status['CO_STATUS_REDIRECTED']:
        case this.data.attrs.co_status['CO_STATUS_SENT_TO_REGULATOR']:
            this._initResponse();
            break;
        // directed requests
        case this.data.attrs.co_status['CO_STATUS_PENDING_INFO']:
            this._initResponse();
            break;
        // under review
        // if company status is "no response", use the cfpb fields that is never displayed to the consumer
        case this.data.attrs.co_status['CO_STATUS_NO_RESPONSE']:
            this._initResponse();
            document.getElementById('step1_cfpb').className = ""; // this will expose the c$cfpb_status menu
            break;
        // active
        case this.data.attrs.co_status['CO_STATUS_IN_PROGRESS']:
        case this.data.attrs.co_status['CO_STATUS_SENT_TO_COMPANY']:
        case this.data.attrs.co_status['CO_STATUS_PAST_DUE']:
        case this.data.attrs.co_status['CO_STATUS_REDIRECTED']:
            this._initResponse();
            // if there is already a response, use company_status_2 custom which hides "in progress" option
            if (this.data.js.is_response) {
                document.getElementById('step1_comp_update').className = ""	;  // this will expose the c$company_status_2 menu
            }
            else {
                document.getElementById('step1_comp').className = ""; // this will expose the c$company_status_1 menu
            }
            break;
        // redirect back to active listing
        default:
            //RightNow.Url.navigate("/app/instAgent/list/incstatus/");
            break;
    }
};

RightNow.Widget.InvestigationDetailForm.prototype = {

   /**
    * Display Investigation response fields if not null
    */
    _initResponse: function()
    {
        if (this.data.js.is_delinquent)
            this.divDelinquent.className = "";
        if (this.data.js.is_explain)
            this.divExplanation.className = "";
        if (this.data.js.is_relief)
            this.divRelief.className = "";
        if (this.data.js.is_response)
            this.divResponse.className = "";
    }
};
