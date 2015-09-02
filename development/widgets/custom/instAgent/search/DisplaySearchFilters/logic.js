RightNow.Widget.DisplaySearchFilters = function(data, instanceID) {
    this.data = data;
    this.instanceID = instanceID;

    this._originalFilters = {}; //prod/cat filters that are applied when the page loads
    for(var i = 0, eaFilter, j; i < this.data.js.filters.length; i++) {
        eaFilter = this.data.js.filters[i];
        this._originalFilters[eaFilter.name] = [];
        for(j in eaFilter.data) {
            if(eaFilter.data.hasOwnProperty(j)) {
                this._originalFilters[eaFilter.name].push(eaFilter.data[j]);    
            }
        }
        YAHOO.util.Event.addListener("rn_" + this.instanceID + "_Remove_" + i, "click", this._onFilterRemove, i, this);
    }

    this._filters = this.data.js.filters; //current filters
    delete this.data.js.filters;
    this._defaultFilters = this.data.js.defaultFilters; //default values of flat filters (shown when values change from their default values)
    delete this.data.js.defaultFilters;

    RightNow.Event.subscribe("evt_searchFiltersResponse", this._updateFilters, this);
    RightNow.Event.subscribe("evt_reportResponse",  this._onReportResponse, this);
};

RightNow.Widget.DisplaySearchFilters.prototype = {
    /**
    * Updates internal representation of prod/cat filters.
    * @param existingFilter Object Existing filter or null if not previously existing
    * @param updatedFilter Object The updated filter received from event
    * @return Object filter the updated hierarchy filter object
    */
    _updateHierFilter: function(existingFilter, updatedFilter) {
        if(!existingFilter) {
            existingFilter = this._createFilter(updatedFilter, ((updatedFilter.filters.searchName === "p") 
                ? RightNow.Interface.getMessage("PRODUCT_LBL")
                : RightNow.Interface.getMessage("CATEGORY_LBL")));
        }
        var reconstructData = updatedFilter.filters.data.reconstructData;
        if(reconstructData !== existingFilter.data) {
            existingFilter.data = reconstructData;
            existingFilter.touched = true;
        }
        return existingFilter;
    },

    /**
    * Updates internal representation of a normal (flat) filter
    * @param existingFilter Object Existing filter or null if not previously existing
    * @param updatedFilter Object The updated filter received from event
    * @return Object the updated filter object
    */
    _updateFilter: function(existingFilter, updatedFilter) {
        if(!existingFilter)
            existingFilter = this._createFilter(updatedFilter);

        if(existingFilter.data !== updatedFilter.filters.data.label) {
            existingFilter.data = updatedFilter.filters.data.label;
            existingFilter.filterID = (existingFilter.type !== "org") ? updatedFilter.filters.fltr_id : updatedFilter.filters.data.selected;
            existingFilter.touched = true;
        }
        
        this._checkDefaultFilterValues(existingFilter);

        return existingFilter;
    },

    /**
    * Creates an internal representation object for a filter.
    * @param newFilter Object The new filter received from event
    * @param label String Label to use for the filter if none is provided
    * in the newFilter object
    * @return Object a filter object
    *       {filterID: filters.fltr_id (or filters.data.selected for orgs),
    *        name: filters.searchName,
    *        type: filters.rnSearchType,
    *        label: label for the filter,
    *        data: data for the filter,
    *        touched: whether the filter has been updated (if its visual display  should be updated)}
    */
    _createFilter: function(newFilter, label) {
        this._createFilter._createLabel = this._createFilter._createLabel ||
        function(newFilter, label) {
            var finalLabel = label || newFilter.filters.label;
            if(!finalLabel) {
                if(newFilter.filters.rnSearchType === "searchType")
                    return RightNow.Interface.getMessage("SEARCH_TYPE_LBL");
                else if(newFilter.filters.rnSearchType === "org")
                    return RightNow.Interface.getMessage("ORGANIZATION_LBL");
                else return RightNow.Interface.getMessage("CUSTOM_LBL");
            }
            return finalLabel;
        };
        return {
            "filterID" : newFilter.filters.fltr_id || newFilter.filters.data.selected,
            "name" : newFilter.filters.searchName,
            "type" : newFilter.filters.rnSearchType,
            "label" : this._createFilter._createLabel(newFilter, label),
            "data" : [],
            "touched" : true
        };
    },
    
    /**
    * Checks if the newly set filter value is the filter default.
    * @param filterToCheckFor Object The new filter value
    */
    _checkDefaultFilterValues: function(filterToCheckFor) {
        for(var i = 0; i < this._defaultFilters.length; i++) {
            if(this._defaultFilters[i].name === filterToCheckFor.name &&
                this._defaultFilters[i].filterID == filterToCheckFor.filterID) {
                //the filter's being set back to it's default value: don't show it...
                    filterToCheckFor.removeDefault = true;
                    filterToCheckFor.touched = false;
            }
        }
    },
    
    /**
    * Returns the filter id of the named filter, if it exists as a 
    * default filter when the page was loaded.
    * @param name String the filter's searchName value
    * @return Mixed The corresponding Int filter ID or null if not found
    */
    _getDefaultFilterID: function(name) {
        var numberOfDefaultFilters = this._defaultFilters.length,
            defaultFilter;
        if(numberOfDefaultFilters){
            for(var i = 0; i < numberOfDefaultFilters; i++){
                defaultFilter = this._defaultFilters[i];
                if(defaultFilter.name === name) {
                    return defaultFilter.filterID;
                }
            }
        }
        return null;
    },
    
    /**
    * Returns the fltr_id value of any given filter object.
    * @param filter Object A filter
    * @param Mixed The int fltr_id value or null if the fltr_id member
    *       is somewhere it's not supposed to be
    */
    _getFilterID: function(filter) {
        if(typeof filter !== "undefined"){
            //all filters have a fltr_id member as a top-level filters member except orgs, which has it as a member of filters.data.
            return (typeof filter.fltr_id !== "undefined") ? filter.fltr_id : ((filter.data) ? filter.data.fltr_id : null);
        }
        return null;
    },

    /**
    * Listens for all report filters and updates internal representation of filters.
    * @param evt String event name
    * @param eventObj Object Event object
    */
    _updateFilters: function(evt, eventObj) {
        eventObj = eventObj[0];
        var eventObjectFilter = (typeof eventObj === "object" && eventObj !== null) ? eventObj.filters : null;
        if(eventObjectFilter && eventObjectFilter.report_id === this.data.attrs.report_id && this._getFilterID(eventObjectFilter) !== null) {
            var filterUpdated = false;
            for(var i = 0, index; i < this._filters.length; i++) {
                if(this._filters[i] && this._filters[i].name === eventObjectFilter.searchName) {
                    //found a pre-existing filter
                    if(eventObjectFilter.rnSearchType === "menufilter")
                        this._filters[i] = this._updateHierFilter(this._filters[i], eventObj);
                    else
                        this._filters[i] = this._updateFilter(this._filters[i], eventObj);
                    filterUpdated = true;
                    break;
                }
            }
            if(!filterUpdated && eventObjectFilter.searchName) {
                //filter wasn't found: create it
                if(eventObjectFilter.rnSearchType === "menufilter" && eventObjectFilter.data.reconstructData)
                    this._filters[this._filters.length] = this._updateHierFilter(null, eventObj);
                else if(eventObjectFilter.rnSearchType !== "menufilter")
                    this._filters[this._filters.length] = this._updateFilter(null, eventObj);
            }
        }
    },

    /**
    * Displays any new/updated search filters on the reportResponse event.
    * @param evt String event name
    * @args Object Event object
    */
    _onReportResponse: function(evt, args) {
        //update filters:
        //filters will be out-of-date only if called from hist. manager restoring a state
        var reportFilters = args[0].filters.allFilters.filters,
            reportID = args[0].filters.report_id,
            filterIndex, clonedFilterObject, filter, filterID, defaultFilterID;
        for(filterIndex in reportFilters) {
            filter = reportFilters[filterIndex].filters;
            filterID = this._getFilterID(filter);
            if(filter && (filterID !== null || (defaultFilterID = this._getDefaultFilterID(filter.searchName)) !== null)) {
                //filter is either a prod/cat menufilter or a flat org/searchType/custom filter
                clonedFilterObject = RightNow.Lang.cloneObject(reportFilters[filterIndex]); //clone the filter to avoid inadvertantly modifying the event object
                filter = clonedFilterObject.filters;
                if(filter.rnSearchType === "menufilter") {
                    //hierarchy filter: only care about three specific conditions; otherwise the filters displaying are already up-to-date
                    if(filter.data.reconstructData) {
                        //coming in from the cold on a completely restored state: set searchName (since it won't be set)
                        filter.searchName = filterIndex;
                    }
                    else if(!filter.data[0] || !filter.data[0].length) {
                        //returning back to a 'no value' / 'nothing selected' state
                        filter.data.reconstructData = [];
                    }
                    else if(filter.data[0] && filter.data[0].length &&
                        parseInt(filter.data[0][filter.data[0].length - 1], 10) === this._originalFilters[filterIndex][this._originalFilters[filterIndex].length - 1].value) {
                        //returning back to original selection (selection when the page was loaded)
                        filter.data.reconstructData = this._originalFilters[filterIndex];
                    }
                    else {
                        continue;    
                    }
                }
                else {
                    //flat filter
                    filter.fltr_id = filter.fltr_id || filterID || defaultFilterID;
                    filter.data = filter.data || "";
                    filter.searchName = filterIndex;
                    filter.report_id = reportID;
                }
                this._updateFilters(null, [clonedFilterObject]);
            }
        }
        this._displayFilters();
    },

    /**
    * Displays any new/updated search filters (denoted by the filter's "touched" property).
    */
    _displayFilters: function() {
        var outputChanged = false,
            widgetElement = document.getElementById("rn_" + this.instanceID),
            i, j, index, newElement, filterData;
        for(i = 0; i < this._filters.length; i++) {
            if(this._filters[i] && this._filters[i].touched) {
                //the filter is different than what's being displayed : create a new one
                //Construct the HTML:
                newElement = this._buildFilterElement(i, this._filters[i].label);
                filterData = this._filters[i].data;
                //populate it
                if(filterData && this._filters[i].type === "menufilter") {
                    //hierarchy type
                    if(filterData.length) {
                        for(j = 0; j < filterData.length; j++) {
                            newElement.innerHTML += this._buildFilterItemMarkup(filterData[j], null, (j === filterData.length - 1), this._filters[i].name);
                        }
                    }
                    else {
                        newElement.innerHTML = "";
                    }
                }
                else if(filterData) {
                    //flat type
                    newElement.innerHTML += this._buildFilterItemMarkup(this._filters[i], filterData, true);
                }
                else {
                    //flat type filter set back to its default
                    newElement.innerHTML = "";
                }
                //Insert the HTML into the DOM:
                if(widgetElement && newElement) {
                    var outdatedFilter = document.getElementById(newElement.id),
                        addedAFilter;
                    if(outdatedFilter) {
                        //repace outdated filter if there is one
                        if(newElement.innerHTML === "") {
                            outdatedFilter.parentNode.removeChild(outdatedFilter);
                        }
                        else {
                            outdatedFilter.innerHTML = newElement.innerHTML;
                            addedAFilter = true;
                        }
                    }
                    else if(newElement.innerHTML !== "") {
                        widgetElement.appendChild(newElement);
                        addedAFilter = true;
                    }
                    if(addedAFilter) {
                        YAHOO.util.Event.addListener("rn_" + this.instanceID + "_Remove_" + i, "click", this._onFilterRemove, i, this);
                    }
                }
                this._filters[i].touched = false;
                outputChanged = true;
            }
            else if(this._filters[i] && this._filters[i].removeDefault) {
                //filter went back to default value: remove it
                var existingElement = document.getElementById("rn_" + this.instanceID + "_Filter_" + i);
                if(existingElement)
                    existingElement.parentNode.removeChild(existingElement);
                this._filters[i].removeDefault = false;
            }
        }
        //the only element that exists in the widget element is the heading
        if(widgetElement.children.length === 1)
            YAHOO.util.Dom.addClass("rn_" + this.instanceID, "rn_Hidden");
        else if(outputChanged)
            YAHOO.util.Dom.removeClass("rn_" + this.instanceID, "rn_Hidden");
    },

    /**
    * Creates a filter's inner HTML and returns it as a string.
    * @param item Object Internal filter object
    * @param label String Label that may be used instead of the filter's label
    * @param shouldBeSelected Boolean True if the filter should be selected
    * @param filterName String Used to generate the link for the hierarchy filter
    *   (only applies for prod/cat hierarchy filter)
    * @return String representing the filter item
    */
    _buildFilterItemMarkup: function(item, label, shouldBeSelected, filterName) {
        var cssClass = (shouldBeSelected) ? "rn_Selected" : "",
              link = (!shouldBeSelected && item.hierList) ? RightNow.Url.addParameter(this.data.js.searchPage, filterName, item.hierList) : "javascript:void(0);";
        return "<a href='" + link + "' class='" + cssClass + " rn_FilterItem'>" + (label || item.label) + "</a><span class='rn_Separator " + cssClass + "'/>";
    },

    /**
    * Creates and returns a filter element div.
    * @param index Int The index of the filter for use as filter ID
    * @param label String label to use for the filter
    * @return HTMLElement Div The filter element
    */
    _buildFilterElement: function(index, label) {
        var filterElement = document.createElement("div");
        filterElement.id = "rn_" + this.instanceID + "_Filter_" + index;
        YAHOO.util.Dom.addClass(filterElement, "rn_Filter");
        filterElement.innerHTML = "<div class='rn_Label'>" + label + "<a id='rn_" + this.instanceID + "_Remove_" + index + "' href='javascript:void(0);' title='" + this.data.attrs.label_filter_remove + "'> " +
            ((this.data.attrs.remove_icon_path)
            ? "<img alt='" + this.data.attrs.label_filter_remove + "' src='" + this.data.attrs.remove_icon_path + "'/>"
            : this.data.attrs.label_filter_remove) + "</a></div>";
        return filterElement;
    },

    /**
    * Responds when user chooses to remove a filter.
    * @param evt Event Click event
    * @param index Int index of the filter to remove
    */
    _onFilterRemove: function(evt, index) {
        //static variable to keep track of removed filters
        this._onFilterRemove._removedFilters = this._onFilterRemove._removedFilters || 0;

        YAHOO.util.Event.stopEvent(evt);
        var eo = new RightNow.Event.EventObject(),
            filterElement;
        //fire the reset event
        eo.data.name = this._filters[index].name;
        eo.filters.report_id = this.data.attrs.report_id;
        eo.w_id = this.instanceID;
        RightNow.Event.fire("evt_resetFilterRequest", eo);
        //fire off a new search
        eo.filters.reportPage = "";
        RightNow.Event.fire("evt_searchRequest", eo);
        //remove internal representation
        this._filters[index].data = [];
        YAHOO.util.Event.purgeElement("rn_" + this.instanceID + "_Remove_" + index);
        //remove the dom element
        filterElement = document.getElementById("rn_" + this.instanceID + "_Filter_" + index);
        if(filterElement) {
            filterElement.innerHTML = "";
            filterElement.parentNode.removeChild(filterElement);
        }

        this._onFilterRemove._removedFilters++;
        if(this._onFilterRemove._removedFilters === this._filters.length)
            YAHOO.util.Dom.addClass("rn_" + this.instanceID, "rn_Hidden");
    }
};
