RightNow.Widget.SearchTypeList2 = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();

    this._selectBox = document.getElementById("rn_" + this.instanceID + "_Options");

    var RightNowEvent = RightNow.Event;
    RightNowEvent.subscribe("evt_searchTypeResponse", this._onChangedResponse, this);
    RightNowEvent.subscribe("evt_getFiltersRequest", this._onGetFiltersRequest, this);
    RightNowEvent.subscribe("evt_reportResponse", this._onChangedResponse, this);
    RightNowEvent.subscribe("evt_resetFilterRequest", this._onResetRequest, this);
    YAHOO.util.Event.addListener(this._selectBox, "change", this._onSelectChange, null, this);
    this._setFilter();
    this._setSelectedDropdownItem(this.data.js.defaultFilter);
};

RightNow.Widget.SearchTypeList2.prototype = {
    /**
    * Event handler executed when the select box is changed
    *
    * @param evt object Event
    */
    _onSelectChange: function(evt)
    {
        this._setSelected();
        RightNow.Event.fire("evt_searchTypeRequest", this._eo);
        if (this.data.attrs.search_on_select)
        {
            this._eo.filters.reportPage = this.data.attrs.report_page_url;
            RightNow.Event.fire("evt_searchRequest", this._eo);
        }
    },
    
    /**
    * Sets the selected dropdown item to the one matching the passed-in value.
    * @param valueToSelect Int Value of item to select
    * @return Boolean Whether or not the operation was successful
    */
    _setSelectedDropdownItem: function(valueToSelect)
    {
        if(this._selectBox)
        {
            for(var i = 0, length = this._selectBox.length; i < length; i++)
            {
                if(this._selectBox.options[i].value == valueToSelect)
                {
                    this._selectBox.selectedIndex = i;
                    return true;
                }
            }
        }
        return false;
    },

    /**
    * internal function to set the event object values from the select box values
    */
    _setSelected: function()
    {
        if (this._selectBox)
        {
            var index = this._selectBox.selectedIndex;
            index = (index > 0) ? index : 0;
            if (this._selectBox.options[index])
            {
                var selectedOption = this._selectBox.options[index],
                    value = selectedOption.value,
                    label = selectedOption.text,
                    node;
                for(node in this.data.js.filters)
                {
                    if (this.data.js.filters[node].fltr_id == value)
                    {
                        return this._setSelectedFilter(this.data.js.filters[node], label);
                    }
                }
            }
        }
    },

    /**
    * internal function to set the event object values to the selected values
    */
    _setSelectedFilter: function(selected, label)
    {
        this._eo.filters.fltr_id = selected.fltr_id;
        this._eo.filters.data = {"val": selected.fltr_id};
        
        if(label)
            this._eo.filters.data.label = label;
        this._eo.filters.oper_id = selected.oper_id;
    },

    /**
    * internal function to set the initial event object values
    */
    _setFilter: function()
    {
        this._eo.w_id = this.instanceID;
        this._eo.filters = {"rnSearchType": this.data.js.rnSearchType,
                            "searchName": this.data.js.searchName,
                            "report_id": this.data.attrs.report_id
                           };

        for (var node in this.data.js.filters)
        {
            if (this.data.js.filters[node].fltr_id === this.data.js.defaultFilter)
            {
                this._setSelectedFilter(this.data.js.filters[node]);
                break;
            }
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
            this._setSelectedDropdownItem(this.data.js.defaultFilter);
            this._setFilter();
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
    },

     /**
    * Event handler executed whent the keyword data is changed
    *
    * @param type string Event type
    * @param args object Arguments passed with event
    */
    _onChangedResponse: function(type, args)
    {
        if (RightNow.Event.isSameReportID(args, this.data.attrs.report_id))
        {
            var data = RightNow.Event.getDataFromFiltersEventResponse(args, this.data.js.searchName, this.data.attrs.report_id);
            this._setSelectedDropdownItem(((data && data.val) ? data.val : this.data.js.defaultFilter));
            this._setSelected();
        }
    }
};
