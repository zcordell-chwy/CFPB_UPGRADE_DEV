RightNow.Widget.Grid2Custom = function(data, instanceID){
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();
    this._sortEo = new RightNow.Event.EventObject();
    this._contentName = "rn_" + this.instanceID + "_Content";
    this._gridName = "rn_" + this.instanceID + "_Grid";
    this._loadingName = "rn_" + this.instanceID + "_Loading";

    RightNow.Event.subscribe("evt_reportResponse" , this._onReportChanged, this);
    RightNow.Event.subscribe("evt_searchInProgressResponse", this._searchInProgress, this);
    RightNow.Event.subscribe("evt_getFiltersRequest", this._onGetFiltersRequest, this);
    RightNow.Event.subscribe("evt_sortTypeResponse", this._onSortTypeResponse, this);
    this._setFilter();
    RightNow.Event.fire("evt_setInitialFiltersRequest", this._eo);
    
    // hack for xhtml compliance
    var dummyElement = document.getElementById("rn_" + this.instanceID + "_Tbody");
    if(dummyElement)
        dummyElement.parentNode.removeChild(dummyElement);
        
    if(this.data.attrs.headers)
        this._generateYUITable(this._gridName, this._contentName, this.data.js.headers);
    
    if (RightNow.Event.isHistoryManagerFragment())
        this._setLoading(true);
    
    //use custom getReportData ajax function
    RightNow.Event.subscribe("on_before_ajax_request", this._beforeAjaxRequest, this);
};

RightNow.Widget.Grid2Custom.prototype = {
    /**
     * Event handler to replace ajaxRequest/getReportData with ajaxCustom/getReportData
     * so we can use custom report_model2 which disables the contact security filter
     * @param type string Event name
     * @param args object Event arguments
     */
    _beforeAjaxRequest: function(type, args)
    {
        if(args[0].url == '/ci/ajaxRequest/getReportData')
            args[0].url = "/cc/ajaxCustom/getReportData";
    },

    /**
     * Initilization function to set up search filters for report
     */
    _setFilter: function()
    {
        this._sortEo.w_id = this.instanceID;
        this._sortEo.filters = {"report_id": this.data.attrs.report_id,
                                "searchName": this.data.js.searchName,
                                "report_page": ""
                               };
        this._setSortData();

        this._eo.w_id = this.instanceID;
        this._eo.filters = {"report_id": this.data.attrs.report_id,
                            "token": this.data.js.token,
                            "allFilters": this.data.js.filters,
                            "format": this.data.js.format
                            };
        this._eo.filters.format.parmList = this.data.attrs.add_params_to_url;
    },

    /**
    * initializes sort event object data
    */
    _setSortData: function()
    {
        this._sortEo.filters.data =  {"col_id": this.data.js.colId,
                                      "sort_direction": this.data.js.sortDirection
                                     };
    },

    /**
     * Event handler executed to show progress icon during searches
     *
     * @param type string Event type
     * @param args object Arguments passed with event
     */
    _searchInProgress: function(type, args)
    {
        if(args[0].filters.report_id == this.data.attrs.report_id)
        {
            document.body.setAttribute("aria-busy", "true");
            this._setLoading(true);
        }
    },

    /**
    * changes the loading icon and hides/unhide the data
    * @param loading bool
    */
    _setLoading: function(loading)
    {
        if(loading)
        {
            var element = document.getElementById(this._contentName);
            if(element)
            {
                //keep height to prevent collapsing behavior
                YAHOO.util.Dom.setStyle(element, "height", element.offsetHeight + "px");
                //IE rendering: so bad it can't handle eye-candy
                if(YAHOO.env.ua.ie)
                    YAHOO.util.Dom.addClass(element, "rn_Hidden");
                else
                    (new YAHOO.util.Anim(element, { opacity: {to: 0 } }, 0.4, YAHOO.util.Easing.easeIn)).animate();
                YAHOO.util.Dom.addClass(this._loadingName, "rn_Loading");
            }
        }
        else
        {
            YAHOO.util.Dom.removeClass(this._loadingName, "rn_Loading");
            if(YAHOO.env.ua.ie)
                YAHOO.util.Dom.removeClass(this._contentName, "rn_Hidden");
            else
                (new YAHOO.util.Anim(this._contentName, { opacity: {to: 1 } }, 0.4, YAHOO.util.Easing.easeIn)).animate();
        }
    },

    /**
     * Event handler executed to display new results
     *
     * @param type string Event type
     * @param args object Arguments passed with event
     */
    _onReportChanged: function(type, args)
    {
        var sortData = RightNow.Event.getDataFromFiltersEventResponse(args, this.data.js.searchName, this.data.attrs.report_id);
        if (sortData)
            this._sortEo.filters.data = sortData;
        else
            this._setSortData();

        this._setLoading(false);
        var newdata = args[0].data;
        var alertDiv = document.getElementById("rn_" + this.instanceID + "_Alert");
        if(newdata.report_id == this.data.attrs.report_id)
        {
            var currentPageSize = newdata.per_page,
                cols = newdata.headers.length,
                report = document.getElementById(this._contentName),
                str = "<table id='" + this._gridName + "' summary='" + this.data.attrs.label_summary + "' class='yui-dt-table'>" +
                    "<caption>" + this.data.attrs.label_caption + "</caption>",
                i, j;

            //Add the new results to the widgets's DOM
            if(this.data.attrs.headers)
            {
                str += "<thead class='GridHead'><tr>" + 
                ((newdata.row_num)
                    ? "<th scope='col' class='GridHeader'>" + this.data.attrs.label_row_number + "</th>"
                    : "");
                for(i = 0; i < cols; i++)
                    str += "<th scope='col' class='GridHeader' width='" + newdata.headers[i].width + "%'>" + newdata.headers[i].heading + "</th>";
                //adding hidden header isUnread for data exception
                str += "<th scope='col' class='' style='display:none'>isUnread</th></tr></thead>";
            }
            if(newdata.total_num > 0)
            {
                str += "<tbody class='yui-dt-body>'";
                for (i = 0; i < currentPageSize; i++)
                {
                    str += "<tr class='" + ((i % 2 === 0) ? 'yui-dt-even' : 'yui-dt-odd') + "'>" + 
                    ((newdata.row_num)
                        ? "<td>" + (newdata.start_num + i) + "</td>"
                        : "");
                    for(j = 0; j < cols; j++)
                        str += "<td>" + ((newdata.data[i][j] !== "") ? newdata.data[i][j] : '&nbsp;')  + "</td>";
                    str += "</tr>";
                }
                str += "</tbody>";
                if(this.data.attrs.hide_when_no_results)
                    YAHOO.util.Dom.removeClass('rn_' + this.instanceID, 'rn_Hidden');
            }
            else if(this.data.attrs.hide_when_no_results)
            {
                    YAHOO.util.Dom.addClass('rn_' + this.instanceID, 'rn_Hidden');
            }
            str += "</table>";
            report.innerHTML = str;
            if(this.data.attrs.headers)
                this._generateYUITable(this._gridName, this._contentName, newdata.headers);
            //now allow expand/contract
            YAHOO.util.Dom.setStyle(report, "height", "auto");
            RightNow.Url.transformLinks(document.getElementById(this._contentName));
            document.body.setAttribute("aria-busy", "false");

            if(newdata.total_num > 0)
            {
                if(alertDiv)
                    alertDiv.innerHTML = this.data.attrs.label_screen_reader_search_success_alert;
                //focus on the first result
                var anchors = this._grid.getFirstTdEl().getElementsByTagName("A");
                
                if(anchors && anchors[0])
                    anchors[0].focus();
            }
            else
            {
                //don't focus anywhere, stay where you are so you can perhaps try a new search
                if(alertDiv)
                    alertDiv.innerHTML = this.data.attrs.label_screen_reader_search_no_results_alert;
            }
        }
    },

    /**
     * Generates a YUI datatable out of an existing table
     *
     * @param source object The HTML table source to build off of
     * @param dest object The destination element in the DOM
     * @param headers object Headers for the datatable
     */
     _generateYUITable: function(source, dest, headers)
    {
        var dataSource = new YAHOO.util.DataSource(YAHOO.util.Dom.get(source));
        dataSource.responseType = YAHOO.util.DataSource.TYPE_HTMLTABLE;
        var gridColumns = [];
        var fieldInfo = [];
        
        if(this.data.js.row_num)
        {
            gridColumns.push({key:this.data.attrs.label_row_number, sortable:false, formatter:"number"});
            fieldInfo.push({key:this.data.attrs.label_row_number, parser:"number"});
        }
        for(var i = 0, length = headers.length, dataType; i < length; i++)
        {
            dataType = headers[i].data_type;
            if(dataType === 3)
            {
                //number
                fieldInfo.push({key: headers[i].heading, parser: "number"});
                gridColumns.push({key: headers[i].heading, colId: headers[i].col_id, sortable: true});
            }
            else if(dataType === 4 || dataType === 7)
            {
                //date / datetime
                fieldInfo.push({key: headers[i].heading});
                gridColumns.push({key: headers[i].heading, colId: headers[i].col_id, sortable: true, formatter: "date"});
            }
            else
            {
                //varchar
                fieldInfo.push({key: headers[i].heading});
                gridColumns.push({key: headers[i].heading, colId: headers[i].col_id, sortable:true});
            }
        }

        // setup hidden isUnread field for data exception
        //fieldInfo.push({key: "isunread"});
        
        dataSource.responseSchema = {fields: fieldInfo};

        //setup custom formatter for data exceptions
        var rowExceptionFormatter = function(elTr, oRecord)
        {
            // added exception for past due cases only if the company status is pending info from company
            if ((oRecord.getData('Respond by') != null) && (oRecord.getData('Company status') == 'Pending info from company'))
            {
                var responseDue = new Date(oRecord.getData('Respond by'));
                var today = new Date();
                if (responseDue.setHours(0,0,0,0) < today.setHours(0,0,0,0))
                    YAHOO.util.Dom.addClass(elTr, 'ps_pastDue');
            }

            if (oRecord.getData('isUnread') == 'Yes')
                YAHOO.util.Dom.addClass(elTr, 'ps_unreadMsg');
            return true;
        };

        var configs = {MSG_EMPTY: RightNow.Interface.getMessage("NO_RECORDS_FOUND_MSG"),
                       MSG_SORTASC: RightNow.Interface.getMessage("CLICK_TO_SORT_ASCENDING_CMD"),
                       MSG_SORTDESC: RightNow.Interface.getMessage("CLICK_TO_SORT_DESCENDING_CMD"),
                       //caption: this.data.attrs.label_caption,
                       summary: this.data.attrs.label_summary,
                       formatRow: rowExceptionFormatter
                      };
        if(this._sortEo.filters.data.sort_direction != null && this._sortEo.filters.data.col_id != null)
        {
            for (var i=0; i < gridColumns.length; i++)
            {
                if (gridColumns[i].colId == this._sortEo.filters.data.col_id)
                {
                    var sortKey = gridColumns[i].key;
                    if (sortKey)
                    {
                        var sortDirection = (this._sortEo.filters.data.sort_direction == 1) ? "asc" : "desc";
                        configs.sortedBy = {key: sortKey, dir: sortDirection};
                        break;
                    }
                }
            }
        }

        this._grid = new YAHOO.widget.DataTable(dest, gridColumns, dataSource, configs);

        // remove isUnread column from display
        this._grid.removeColumn(this._grid.getColumn('isUnread'));

        if(!this.data.attrs.headers)
            YAHOO.util.Dom.addClass(this._contentName, "rn_NoHeader");

        var columns = this._grid.getColumnSet();
        if(columns)
        {
            for(var i = 0, length = columns.keys.length, element; i < length; i++)
            {
                element = columns.keys[i].getThEl();
                element.setAttribute("scope", "col");
                if(columns.keys[i].getSanitizedKey() === "")
                {
                    //YUI failed to generate a proper ID using the header's label; but we need to ensure uniqueness
                    element.id = element.id + i;
                }
            }
        }

        this._grid.sortColumn = RightNow.Event.createDelegate(this, this._sortColumn);
    },

    /**
     * Event handler executed when column is clicked to be sorted
     * @param column object The column that was clicked in the Grid
     */
    _sortColumn: function(column)
    {
        //Default to ascending
        var sortDirection = 1;
        if(this._grid.get("sortedBy"))
        {
            if(column.key === this._grid.get("sortedBy").key)
                sortDirection = (this._grid.get("sortedBy").dir === YAHOO.widget.DataTable.CLASS_ASC) ? 2 : 1;
        }
        this._sortEo.filters.data.sort_direction = sortDirection;
        this._sortEo.filters.data.col_id = column.colId;
        RightNow.Event.fire("evt_sortTypeRequest", this._sortEo);
        RightNow.Event.fire("evt_searchRequest", this._sortEo);
    },

    /**
    * Event handler executed when search filters are requested - fires the event object
    *
    * @param type string Event type
    * @param args object Arguments passed with event
    */
    _onGetFiltersRequest: function(type, args)
    {
         RightNow.Event.fire("evt_searchFiltersResponse", this._sortEo);
    },

    /**
    * Event handler executed when the sort type is changed
    *
    * @param type string Event type
    * @param args object Arguments passed with event
    */
    _onSortTypeResponse: function(type, args)
    {
        var evt = args[0];
        if (evt.filters.report_id == this.data.attrs.report_id)
        {
            this._sortEo.filters.data.col_id = evt.filters.data.col_id;
            this._sortEo.filters.data.sort_direction = evt.filters.data.sort_direction;
        }
    }
};
