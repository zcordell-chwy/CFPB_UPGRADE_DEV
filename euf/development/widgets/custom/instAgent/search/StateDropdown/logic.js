RightNow.Widget.StateDropdown = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();

    this._optionsSelect = document.getElementById("rn_" + this.instanceID + "_Options");
    
    var RightNowEvent = RightNow.Event;
    RightNowEvent.subscribe("evt_customMenuResponse", this._onChangedResponse, this);
    RightNowEvent.subscribe("evt_reportResponse", this._onChangedResponse, this);
    RightNowEvent.subscribe("evt_resetFilterRequest", this._onResetRequest, this);
    RightNowEvent.subscribe("evt_getFiltersRequest", this._onGetFiltersRequest, this);
    YAHOO.util.Event.addListener(this._optionsSelect, "change", this._onSelectChange, null, this);
    this._setFilter();
    this._setSelectedDropdownItem(this.data.js.defaultValue);
};

RightNow.Widget.StateDropdown.prototype =
{
    /**
    * Event handler executed when the drop down is changed
    *
    * @param evt object Event
    */
    _onSelectChange: function(evt)
    {
        this._eo.filters.data.val = this._getSelected();
        RightNow.Event.fire("evt_customMenuRequest", this._eo);
        if (this.data.attrs.search_on_select)
        {
            this._eo.filters.reportPage = this.data.attrs.report_page_url;
            RightNow.Event.fire("evt_searchRequest", this._eo);
        }
    },

    /**
    * internal function to set this.data from the select box into the event object
    */
    _getSelected: function()
    {
        if (this._optionsSelect)
        {
            var index = Math.max(0, this._optionsSelect.selectedIndex);
            if (this._optionsSelect.options[index])
            {
                return this._optionsSelect.options[index].value;
            }
        }
        return null;
    },
    
    /**
    * Sets the selected dropdown item to the one matching the passed-in value.
    * @param valueToSelect Int Value of item to select
    * @return Boolean Whether or not the operation was successful
    */
    _setSelectedDropdownItem: function(valueToSelect)
    {
        if(this._optionsSelect)
        {
            for(var i = 0; i < this._optionsSelect.length; i++)
            {
                if(this._optionsSelect.options[i].value == valueToSelect)
                {
                    this._optionsSelect.selectedIndex = i;
                    return true;
                }
            }
        }
        return false;
    },

    /**
    * sets the initial event object data
    *
    */
    _setFilter: function()
    {
        this._eo.w_id = this.instanceID;
        this._eo.filters = {"searchName":  this.data.js.searchName,
                            "rnSearchType":this.data.js.rnSearchType,
                            "report_id": this.data.attrs.report_id,
                            "data":     {"fltr_id": this.data.js.filters.fltr_id,
                                          "oper_id": this.data.js.filters.oper_id,
                                          "val": this._getSelected()
                                          }
                            };
    },

    /**
    * Event handler executed when the custom menu data is changed
    *
    * @param type string Event type
    * @param args object Arguments passed with event
    */
    _onChangedResponse: function(type, args)
    {
        var data = RightNow.Event.getDataFromFiltersEventResponse(args, this.data.js.searchName, this.data.attrs.report_id),
              newValue = this._eo.filters.data.val;
        
        // don't update anything if event data is not for this report (data is null) or filter
        if (!data || data.fltr_id !== this.data.js.filters.fltr_id)
            return;

        newValue = data.val || this.data.js.defaultValue;
        if (newValue !== this._eo.filters.data.val)
        {
            this._setSelectedDropdownItem(newValue);
            this._eo.filters.data.val = newValue;
        }
    },

    /**
    * Responds to the filterReset event by setting the internal eventObject's data back to default
    * @param type String Event name
    * @param args Object Event object
    */
    _onResetRequest: function(type, args)
    {
        if(RightNow.Event.isSameReportID(args, this.data.attrs.report_id) && (args[0].data.name === this.data.js.searchName || args[0].data.name === "all"))
        {
            this._setSelectedDropdownItem(this.data.js.defaultValue);
            this._eo.filters.data.val = this.data.js.defaultValue;
        }
    },

    /**
    * Event handler executed when search filters are requested - fires the event object
    *
    * @param type string Event type
    * @param args object Arguments passed with event
    */
    _onGetFiltersRequest: function(type, args)
    {
          RightNow.Event.fire("evt_searchFiltersResponse", this._eo);
          RightNow.Event.fire("ps_incidentSearchFilterApplied", this._eo);
    }
};
