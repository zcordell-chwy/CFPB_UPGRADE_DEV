RightNow.Widget.SearchButton2 = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._requestInProgress = false;
    this._searchButton = document.getElementById("rn_" + this.instanceID + "_SubmitButton");
    
    this._enableClickListener();
    RightNow.Event.subscribe("evt_reportResponse",  this._onSearchResponse, this);
    RightNow.Event.subscribe("evt_startSearch",  this._startSearch, this);
};

RightNow.Widget.SearchButton2.prototype = {
    /**
    * Event handler executed when the button is clicked
    * @param evt object Event
    */
    _startSearch: function(evt)
    {
         if(this._requestInProgress) return false;
         if(!this.data.attrs.popup_window && (!this.data.attrs.report_page_url && (this.data.attrs.target === '_self')))
            this._disableClickListener();

        if(YAHOO.env.ua.ie !== 0)
        {
            //since the form is submitted by script, deliberately tell IE to do auto completion of the form data
            if(!this._parentForm)
                this._parentForm = YAHOO.util.Dom.getAncestorByTagName("rn_" + this.instanceID, "FORM");
            if(this._parentForm && window.external && "AutoCompleteSaveForm" in window.external)
            {
                window.external.AutoCompleteSaveForm(this._parentForm);
            }
        }
        var eo = new RightNow.Event.EventObject();
        eo.w_id = this.instanceID;
        eo.filters = {report_id: this.data.attrs.report_id, 
            reportPage: this.data.attrs.report_page_url,
            target: this.data.attrs.target,
            popupWindow: this.data.attrs.popup_window,
            width: this.data.attrs.popup_window_width_percent,
            height: this.data.attrs.popup_window_height_percent
        };

        RightNow.Event.fire("evt_searchRequest", eo);
    },

    /**
    * Event handler executed when search submission returns from server
    * @param type string Event name
    * @param args object Event arguments
    */
    _onSearchResponse: function(type, args)
    {
        if(args[0].filters.report_id == this.data.attrs.report_id)
           this._enableClickListener();
    },
    
    /**
     * Enable the form submit control by enabling button and adding an onClick listener.
     */
    _enableClickListener: function()
    {
        this._searchButton.disabled = this._requestInProgress = false;
        YAHOO.util.Event.addListener(this._searchButton, "click", this._startSearch, null, this);
    },

    /**
     * Disable the form submit control by disabling button and removing the onClick listener.
     */
    _disableClickListener: function()
    {
        this._searchButton.disabled = this._requestInProgress = true;
        YAHOO.util.Event.removeListener(this._searchButton, "click", this._startSearch);
    }
};
