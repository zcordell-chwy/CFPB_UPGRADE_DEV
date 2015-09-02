/**
 * 6/17/2013 (T. Woodham): Including this to ensure this widget is safe for IE 7.
 */
if( !Array.prototype.indexOf )
{
	Array.prototype.indexOf = function( needle )
	{
		for( var counter = 0; counter < this.length; counter++ )
		{
			if( this[counter] === needle )
			{
				return counter;
			}
		}
		return -1;
	};
}

RightNow.Widget.ReportSelector = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;

	// Only initialize if the user is authorized.
	if( this.data.js.authorized === true )
	{
		this._eo = new RightNow.Event.EventObject();
		this._form = YAHOO.util.Dom.get( 'rn_' + this.instanceID + '_DataExportJobManager' );
		this._caseSubmitButton = YAHOO.util.Dom.get( this.instanceID + '_Incidents' );
		this._totalSearchResults = 0;

		YAHOO.util.Event.addListener( this._caseSubmitButton, 'click', this._submitCaseJob, null, this );

		// if user is not authorized to view export, then js.links will be null
		if(this.data.js.links ) {
			for( var counter = 0; counter < this.data.js.links.length; counter++ )
			{
				var inputId = this.instanceID + "_" + counter;
				YAHOO.util.Event.addListener( inputId, "click", this._onExportClick, this.data.js.links[counter], this );
			}
		}

		RightNow.Event.subscribe( "evt_reportResponse",  this._onReportResponse, this );
		RightNow.Event.subscribe( "evt_exportJobSubmitted", this._exportJobSubmitted, this );
		this._setFilters();
	}
};

RightNow.Widget.ReportSelector.prototype = {
    /**
    * Sets the event object to initial values
    */
    _setFilters: function()
    {
        this._eo.w_id = this.data.info.w_id;
		this._eo.filters.organization = this.data.js.organization;
        this._eo.reports = new Array();

		// Is this user product-limited? If so, go ahead and set that filter.
		if( this.data.js.products.length > 0 )
		{
			var productArray = new Array();
			for( var counter = 0; counter < this.data.js.products.length; counter++ )
			{
				productArray.push( this.data.js.products[counter].join( ',' ) );
			}

			this._eo.filters.product = productArray.join( ';' );
			this.data.js.cachedProductArray = this._eo.filters.product;
		}
    },

	_validSearchFilter: function( filter )
	{
		var validSearchFilter = false;
		var filterCount = this.data.js.validSearchFilters.length;

		for( var counter = 0; counter < filterCount; counter++ )
		{
			if( filter === this.data.js.validSearchFilters[counter] )
			{
				validSearchFilter = true;
				break;
			}
		}

		return validSearchFilter;
	},

	_onReportResponse: function( type, args )
	{
		var newData = args[0];
		this._totalSearchResults = newData.data.total_num;

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

				// update the _eo object filters to include those presented here.
				// Product filters are handled differently to support contact product limitting.
				if( eo.filters.searchName == 'p' )
				{
					this._eo['filters']['product'] = this._getProductValue( eo.filters );
					this._selectExports();
				}
				else if( eo.filters.searchName == 'c' )
				{
					this._eo['filters']['category'] = this._getFilterValue( eo.filters );
				}
				else
				{
					this._eo['filters'][eo.filters.searchName] = this._getFilterValue( eo.filters );
				}
			}
		}
	},

	_getProductValue: function( filter )
	{
		var filterValue = '';
		if( this.data.js.products.length > 0 )
		{
			var product = this._getFilterValue( filter );

			if( product )
			{
				var productArray = product.split( ',' );
				var filterFound = false;

				/*
					We're going to take advantage of this being CP and only allowing one product hierarchy selection per search.
					We'll look through the first tier of products to see if it's in the contact's product limitation. If that's found,
					the following algorithm will be applied:

						- if the user searched only the top tier, and the user is limited by the top tier, allow the product selection.
						- if the user searched only the top tier, but is limited on this branch to lower levels, apply the more limited product selection.
						- if the user searched only the top tier, but is limited by a diferend tier-1 product, invalidate the filter.
						- if the user searched on a tier-2 product, but is limited by the top tier, allow the product selection.
						- if the user searched on a tier-2 product, but is limited by a different tier-2 product, invalidate the filter.
						- if hte user searched on a tier-2 product, but is limited on this branch to lower levels, apply the more limited product selection.
				*/
				for( var prodCounter = 0; prodCounter < this.data.js.products.length; prodCounter++ )
				{
					var userProduct = this.data.js.products[prodCounter];
					if( userProduct[0] == productArray[0] )
					{
						filterFound = true;

						// Found a matching tier-1 product.
						if( userProduct.length == 1 )
						{
							// The user is only limited to the top tier. Return their search as-is.
							filterValue = product;
						}
						else
						{
							// The user has a deeper product filtering.
							if( productArray.length == 1 )
							{
								// The user's search is 1 tier deep. Return this branch of the user's product limiting.
								filterValue = userProduct.join( ',' );
							}
							else if( userProduct[1] == productArray[1] && userProduct.length > productArray.length )
							{
								// There's a tier-2 match, but the user's product filtering is deeper than the product search filter. Return the user's limits.
								filterValue = userProduct.join( ',' );
							}
							else if( userProduct[1] == productArray[1] && userProduct.length <= productArray.length )
							{
								// There's a tier-2 match, but the product search filter is 2 levels or deeper. Return the product search filter.
								filterValue = product;
							}
							else
							{
								// The tier-2 product searched upon doesn't match the user's product filter. Invalidate the filter.
								filterValue = '9999';
							}
						}

						break;
					}
				}

				if( !filterFound )
				{
					filterValue = '9999';
				}
			}
			else
			{
				filterValue = this.data.js.cachedProductArray;
			}
		}
		else
		{
			filterValue = this._getFilterValue( filter );
		}

		return filterValue;
	},

    _getFilterValue: function( filter )
    {
		var filterValue = null;

        if( typeof filter.data == 'string' )
        {
            filterValue = filter.data;
        }
        else if( (typeof filter.data[0] !== 'undefined' && filter.data[0] !== null) )
        {
            filterValue = filter.data[0].join();
        }
        else if( typeof filter.data.val == 'string' )
        {
            filterValue = filter.data.val;
        }
        else if( typeof filter.data.label == 'string' )
        {
            filterValue = filter.data.label;
        }

        return filterValue;
    },

	_selectExports: function()
	{
		for( var counter = 0; counter < this.data.js.links.length; counter++ )
		{
			var inputId = this.instanceID + '_' + counter;
			var input = YAHOO.util.Dom.get( inputId );
			var addInput = false;

			// If there's no product filter applied, select everything.
			if( !this._eo.filters.product )
			{
				input.checked = true;
				addInput = true;
			}
			else
			{
				if( this.data.js.links[counter]['Product'] )
				{
					// All exports, other than the minimum data set, have a tier-1 product setting.
					var selectedProduct = this._eo.filters.product;
					if( selectedProduct.split( ',' )[0] == this.data.js.links[counter]['Product'] )
					{
						input.checked = true;
						addInput = true;
					}
					else
					{
						input.checked = false;
						addInput = false;
					}
				}
				else
				{
					// This export is non-product specific. Is it featured?
					if( this.data.js.links[counter]['Featured'] == '1' )
					{
						input.checked = true;
						addInput = true;
					}
					else
					{
						// So no product with this export and it's not featured. Is the product part of our catch-all?
						if( this.data.js.catchall_product_ids.indexOf( selectedProduct.split( ',' )[0] ) > -1 )
						{
							input.checked = true;
							addInput = true;
						}
						else
						{
							input.checked = false;
							addInput = false;
						}
					}
				}
			}

			if( addInput === true )
				this._addReportToExport( this.data.js.links[counter]['ReportID'], false );
			else
				this._removeReportFromExport( this.data.js.links[counter]['ReportID'], true );
		}
	},

	/**
	 * Event handler catching when submit case button is clicked.
	 */
	_submitCaseJob: function( type, args ) {
		// Only post if there are reports to run and there are results to get.
		var dialogOptions = {
			icon: 'ALARM'
		};

		if( this._totalSearchResults === 0 )
		{
			RightNow.UI.Dialog.messageDialog( 'Your search contains no results. Please update your query so that it includes results prior to creating an export.', dialogOptions );
		}
		else if( this._eo.reports.length === 0 )
		{
			RightNow.UI.Dialog.messageDialog( 'Please select at least one export.', dialogOptions );
		}
		else
		{
			var postData = {
				'f_tok': RightNow.UI.Form.formToken,
				'reports': RightNow.JSON.stringify( this._eo.reports ),
				'filters': RightNow.JSON.stringify( this._eo.filters )
			};

			var requestOptions = {
				successHandler: function( response )
				{
					var eventObject = new RightNow.Event.EventObject();
					eventObject.data = RightNow.JSON.parse( response.responseText );
					RightNow.Event.fire( "evt_exportJobSubmitted", eventObject );
				},
				scope: this,
				failureHandler : function( o )
				{
					// cleanse error: output a more useful message
					if( o.status === 418 && o.argument && o.argument.eventName )
					{
						RightNow.Event.fire( o.argument.eventName, {
							"message" : RightNow.Interface.getMessage( "ERR_SUBMITTING_FORM_DUE_INV_INPUT_LBL" )
						} );
					}
				}
			};

			RightNow.Ajax.makeRequest( "/cc/dataExport/incidents", postData, requestOptions );
		}

		return false;
	},

	/**
	 * Event handler catching when submit case button is clicked.
	 */
	_onExportClick: function( type, args ) {
		this._addReportToExport( args.ReportID, true );
	},

	/**
	 * Helper function to record a report should be run.
	 *
	 * @param	reportId		INT		The report ID that should conditionally be added to the list.
	 * @param	deleteReport	BOOL	Boolean indicating whether a report already on the list should be deleted.
	 */
	_addReportToExport: function( reportId, deleteReport )
	{
		// Is this report already within the array? Remove it if this is the case; otherwise, remove it.
		var reportFound = this._removeReportFromExport( reportId, deleteReport );

		if( !reportFound )
		{
			this._eo.reports.push( reportId );
		}
	},

	/**
	 * Helper function to remove a report from the list to be run.
	 *
	 * @param	INT	The report ID that should conditionally be removed from the list.
	 * @param	deleteReport	BOOL	Boolean indicating whether a report already on the list should be deleted.
	 */
	_removeReportFromExport: function( reportId, deleteReport )
	{
		// Is this report already within the array? Remove it if this is the case; otherwise, remove it.
		var reportFound = false;

		for( var reportCounter = 0; reportCounter < this._eo.reports.length; reportCounter++ )
		{
			if( this._eo.reports[reportCounter] == reportId )
			{
				if( deleteReport )
					this._eo.reports.splice( reportCounter, 1 );

				reportFound = true;
				break;
			}
		}

		return reportFound;
	},

	/**
	 * Event handler for successful data export job submission.
	 */
	_exportJobSubmitted: function( type, args )
	{
		var returnedData = args[0];
		if( returnedData.data.success === true )
		{
			// We need to kick off another ajax call to actually run the created job.
			if( returnedData.data.job_id )
			{
				var postData = {
					'f_tok': RightNow.UI.Form.formToken,
					'jobID': RightNow.JSON.stringify( returnedData.data.job_id )
				};

				var requestOptions = {
					scope: this,
					failureHandler : function( o )
					{
						// cleanse error: output a more useful message
						if( o.status === 418 && o.argument && o.argument.eventName )
						{
							RightNow.Event.fire( o.argument.eventName, {
								"message" : RightNow.Interface.getMessage( "ERR_SUBMITTING_FORM_DUE_INV_INPUT_LBL" )
							} );
						}
					}
				};

				RightNow.Ajax.makeRequest( this.data.js.export_kickoff_url, postData, requestOptions );
			}
			RightNow.UI.Dialog.messageDialog( returnedData.data.message );
		}
		else
		{
			var dialogOptions = {
				'icon': 'WARN'
			};
			RightNow.UI.Dialog.messageDialog( returnedData.data.message, dialogOptions );
		}
	}
};
