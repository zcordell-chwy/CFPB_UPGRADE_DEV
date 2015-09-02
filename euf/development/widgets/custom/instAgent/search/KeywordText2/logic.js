RightNow.Widget.KeywordText2 = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();
    this._textElement = document.getElementById("rn_" + this.instanceID + "_Text");
    //Decode back to what is stored in the DOM nodes value property so it can be used for comparison
    if(this.data.js.initialValue)
        this.data.js.initialValue = this.data.js.initialValue.replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&#039;/g, "'").replace(/&quot;/g, '"');
    if(this._textElement)
    {
        this._searchedOn = this._textElement.value;
        if(this._textElement.value !== this.data.js.initialValue)
            this._textElement.value = this.data.js.initialValue;
        this._setFilter();
        YAHOO.util.Event.addListener(this._textElement, "change", this._onChange, null, this);
        RightNow.Event.subscribe("evt_keywordChangedResponse", this._onChangedResponse, this);
        RightNow.Event.subscribe("evt_reportResponse", this._onChangedResponse, this);
        RightNow.Event.subscribe("evt_getFiltersRequest", this._onGetFiltersRequest, this);
        RightNow.Event.subscribe("evt_resetFilterRequest", this._onResetRequest, this);
        if(this.data.attrs.initial_focus)
            this._textElement.focus();  
    }
};

RightNow.Widget.KeywordText2.prototype = {
    /**
    * Event handler executed when text has changed
    *
    * @param evt object Event
    */
    _onChange: function(evt)
    {
        this._eo.data = this._textElement.value;
        this._eo.filters.data = this._textElement.value;
        RightNow.Event.fire("evt_keywordChangedRequest", this._eo);
    },

    /**
    * Event handler executed to fire the event object for search filters
    *
    * @param type string Event type
    * @param args object Arguments passed with event
    */
    _onGetFiltersRequest: function(type, args)
    {
        this._eo.filters.data = YAHOO.lang.trim(this._textElement.value);
        this._searchedOn = this._eo.filters.data;
        RightNow.Event.fire("evt_searchFiltersResponse", this._eo);
        RightNow.Event.fire("ps_incidentSearchFilterApplied", this._eo);
    },

    /**
    * internal function to set the initial values of the event object
    *
    */
    _setFilter: function()
    {
        this._eo.w_id = this.instanceID;
        this._eo.filters = {"searchName": this.data.js.searchName,
                            "data": this.data.js.initialValue,
                            "rnSearchType": this.data.js.rnSearchType,
                            "report_id": this.data.attrs.report_id
                            };
    },

    /**
    * Event handler executed when the keyword data is changed
    *
    * @param type string Event type
    * @param args object Arguments passed with event
    */
    _onChangedResponse: function(type, args)
    {
        if (RightNow.Event.isSameReportID(args, this.data.attrs.report_id))
        {
            var data = RightNow.Event.getDataFromFiltersEventResponse(args, this.data.js.searchName, this.data.attrs.report_id),
                  newValue = (data === null) ? this.data.js.initialValue : data;
            if(this._textElement.value !== newValue)
                this._textElement.value = newValue;
        }
    },

    /**
    * Responds to the filterReset event by setting the keyword data back to the last searched-on value
    * @param type String Event name
    * @param args Object Event object
    */
    _onResetRequest: function(type, args)
    {
        if(RightNow.Event.isSameReportID(args, this.data.attrs.report_id) && (args[0].data.name === this.data.js.searchName || args[0].data.name === "all"))
        {
            this._textElement.value = this._searchedOn;
        }
    }
};
