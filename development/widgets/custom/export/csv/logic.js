RightNow.Widget.csv = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();
    this._currentPage = this.data.js.currentPage;
    this._searchFilters = new Array();
    this._form = new Array();

    if (this.data.js.links) { // if user is not authorized to view export, then js.links will be null
        for( var counter = 0; counter < this.data.js.links.length; counter++ )
        {
            var formId = "rn_" + this.instanceID + "_" + this.data.js.links[counter].form_label + '_Form';
            this._form.push( YAHOO.util.Dom.get( formId ) );
            YAHOO.util.Event.addListener( "rn_" + this.instanceID + "_" + this.data.js.links[counter].form_label, "change", this._onChange, this.data.js.links[counter], this );
        }
    }
    
    // RightNow.Event.subscribe( "ps_incidentSearchFilterApplied", this._searchFiltersApplied, this );
	//SMadsen: This fires every time a report response comes back. Changing so that it is only fired if the user has resolved to authorized
    if(this.data.js.authorized == true){
        RightNow.Event.subscribe("evt_reportResponse",  this._onReportResponse, this);
    }
    YAHOO.util.Event.addListener( "rn_" + this.instanceID + "_Select", "change", this._onChange, null, this );
    this._setFilters();

	// Go ahead and get all the search filters - we need them in case the user went into a case details page and used the back button.
	var eo = new RightNow.Event.EventObject();
	eo.filters.report_id = this.data.attrs.search_report_id;
	// RightNow.Event.fire( 'evt_getFiltersRequest' );
};

RightNow.Widget.csv.prototype = {
    /**
    * Sets the event object to initial values
    */
    _setFilters: function()
    {
        this._eo.w_id = this.data.info.w_id;
        this._eo.data.per_page = this.data.attrs.per_page;
        this._eo.data.page = this._currentPage;
        this._eo.filters.report_id = this.data.attrs.report_id;
    },

    _onChange: function( evt, args )
    {
        var form = YAHOO.util.Dom.get( "rn_" + this.instanceID + "_" + args.form_label + "_Form" );
        var select = YAHOO.util.Dom.get( "rn_" + this.instanceID + "_" + args.form_label );
        /*
        if( select[select.selectedIndex].value > 0 )
            form.submit();
        */
    },

    /**
    * Event Handler fired when a page link is selected
    *
    * @param evt Object Event object
    * @param pageNumber Int Number of the page link clicked on
    */
    _onPageChange: function(evt, pageNumber)
    {
        YAHOO.util.Event.preventDefault(evt);
        
        if(this._currentlyChangingPage || !pageNumber || pageNumber === this._currentPage)
            return;
        
        this._currentlyChangingPage = true;
        RightNow.Event.fire("evt_searchInProgressRequest", this._eo);

        pageNumber = (pageNumber < 1) ? 1 : pageNumber;
        this._eo.data.page = this._currentPage = pageNumber;

        RightNow.Event.fire("evt_pageRequest", this._eo);
    },

    /**
    * Event Handler fired when the next button is clicked
    *
    * @param evt Object Event object
    */
    _onForward: function(evt)
    {
        YAHOO.util.Event.preventDefault(evt);
        if(this._currentlyChangingPage) return;
        
        this._currentlyChangingPage = true;
        RightNow.Event.fire("evt_searchInProgressRequest", this._eo);
        this._currentPage++;
        this._eo.data.page = this._currentPage;
        RightNow.Event.fire("evt_pageRequest", this._eo);
    },


    /**
    * Event Handler fired when the back button is clicked
    *
    * @param evt Object Event object
    */
    _onBack: function(evt)
    {
        YAHOO.util.Event.preventDefault(evt);
        if(this._currentlyChangingPage) return;
        
        this._currentlyChangingPage = true;
        RightNow.Event.fire("evt_searchInProgressRequest", this._eo);
        this._currentPage--;
        this._eo.data.page = this._currentPage;
        RightNow.Event.fire("evt_pageRequest", this._eo);
    },

	_validSearchFilter: function( filter )
	{
        //SMadsen: This function should only fire when the customer is authorized for csv export.
        if (this.data.js._validSearchFilters){
    		var filterCount = this.data.js.validSearchFilters.length;
	    	for( var counter = 0; counter < filterCount; counter++ )
		    {
			    if( filter === this.data.js.validSearchFilters[counter] )
				    return true;
    		}
        }
		return false;
	},

	_onReportResponse: function( type, args )
	{
		var newData = args[0];
		for( filter in newData.filters.allFilters.filters )
		{
			if( this._validSearchFilter( filter ) )
			{
				// filter.searchName
				// filter.rnSearchType
				// w_id
				// filter.data
				var eo = newData.filters.allFilters.filters[filter];
				eo.filters.searchName = filter;
				eo.w_id = this.instanceID;

				this._searchFiltersApplied( type, new Array( eo ) );
			}
		}

		// Do we need to update the number of pages in the dropdown?
		if( this.data.attrs.update_export_pages && newData.data.total_num )
		{
			this._updatePageDropdown( newData.data.total_num );
		}
	},

    /**
    * Event handler received when search filters applied.
    *
    * @param type String Event type
    * @param args Object Arguments passed with event
    */
    _searchFiltersApplied: function(type, args)
    {
        var newData = args[0];
        this._searchFilters[newData.w_id] = newData.filters;

        // Store the data in hidden form elements.
        for( key in this._searchFilters )
        {
            var filter = this._searchFilters[key];
            // console.log( filter );

            for( var counter = 0; counter < this._form.length; counter++ )
            {
                var form = this._form[counter];
                // To get the correct ID of the select element, we need to subtract '_Form' off the form's ID.
                var selectIdLength = YAHOO.util.Dom.getAttribute( form, 'id' ).length - 5;
                var selectElementId = YAHOO.util.Dom.getAttribute( form, 'id' ).substr( 0, selectIdLength );
                var selectElement = YAHOO.util.Dom.get( selectElementId );
                var elementId = selectElementId + '_' + filter.rnSearchType + '_' + filter.searchName;

                if( YAHOO.util.Dom.get( elementId ) )
                {
                    // Element already exists. Updated its value.
                    var element = YAHOO.util.Dom.get( elementId );
                    element = this._setElementValue( element, filter );
                }
                else
                {
                    // We haven't seen this filter before. Create it.
                    var element = document.createElement( 'input' );
                    element.type = 'hidden';
                    element.id = elementId;
                    element.name = filter.searchName;
                    element = this._setElementValue( element, filter );
                    YAHOO.util.Dom.insertAfter( element, selectElement ); 
                }
            }
        }

        // We *MAY* need to remove any filters that are no longer applied.
    },

	/**
	 * Helper function to calculate the number of pages that should be in the dropdown.
	 * 
	 * @param	numResults	INT	The number of results returned by the user's search.
	 */
	_updatePageDropdown: function( numResults )
	{
		// console.log( 'Num results: ' + numResults );
		// Determine how many pages we should have.
		var numPages = parseInt( numResults / this.data.attrs.per_page );
		if( numResults % this.data.attrs.per_page !== 0 )
			numPages++;

		var totalOptions = numPages + 1;
		// console.log( "Alex, I'd like to wager " + numPages + " pages in the daily double." );

		for( var counter = 0; counter < this.data.js.links.length; counter++ )
		{
			var dropdown = YAHOO.util.Dom.get( "rn_" + this.instanceID + "_" + this.data.js.links[counter].form_label );
			if( dropdown.options.length != totalOptions )
			{
				dropdown.options.length = 1;
				for( var optionCounter = 1; optionCounter < totalOptions; optionCounter++ )
				{
					dropdown.options[optionCounter] = new Option( optionCounter, optionCounter, false, false );
				}
				// console.log( 'Updated number of options: ' + dropdown.options.length );
			}
		}
	},

    _setElementValue: function( element, filter )
    {
        if( typeof filter.data == 'string' )
        {
            element.value = filter.data;
        }
        else if( (typeof filter.data[0] !== 'undefined' && filter.data[0] !== null) )
        {
            element.value = filter.data[0].join();
        }
        else if( typeof filter.data.val == 'string' )
        {
            element.value = filter.data.val;
        }
        else if( typeof filter.data.label == 'string' )
        {
            element.value = filter.data.label;
        }

        return element;
    }
};
