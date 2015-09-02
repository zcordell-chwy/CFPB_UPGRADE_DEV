RightNow.Widget.Paginator = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();
    this._currentPage = this.data.js.currentPage;

    RightNow.Event.subscribe("evt_reportResponse", this._onReportChanged, this);
    for(var i = this.data.js.startPage; i <= this.data.js.endPage; i++)
    {
        YAHOO.util.Event.addListener("rn_" + this.instanceID + "_PageLink_" + i, "click", this._onPageChange, i, this);
    }
    YAHOO.util.Event.addListener("rn_" + this.instanceID + "_Forward", "click", this._onForward, null, this);
    YAHOO.util.Event.addListener("rn_" + this.instanceID + "_Back", "click", this._onBack, null, this);
    this._setFilters();
};

RightNow.Widget.Paginator.prototype = {
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

    /**
    * Event handler received when report data has changed
    *
    * @param type String Event type
    * @param args Object Arguments passed with event
    */
    _onReportChanged: function(type, args)
    {
        var newData = args[0];
        newData = newData.data;
        if(args[0].filters.report_id == this.data.attrs.report_id)
        {
            this._currentPage = newData.page;
            var totalPages = newData.total_pages;

            if(totalPages < 2 || newData.truncated)
                YAHOO.util.Dom.addClass("rn_" + this.instanceID, "rn_Hidden");
            else
            {
                //update all of the page links
                var pagesContainer = document.getElementById("rn_" + this.instanceID + "_Pages");
                if(pagesContainer)
                {
                    pagesContainer.innerHTML = "";

                    var startPage, endPage;
                    if(this.data.attrs.maximum_page_links === 0)
                        startPage = endPage = this._currentPage;
                    else if(totalPages > this.data.attrs.maximum_page_links)
                    {
                        var split = Math.round(this.data.attrs.maximum_page_links / 2);
                        if(this._currentPage <= split)
                        {
                            startPage = 1;
                            endPage = this.data.attrs.maximum_page_links;
                        }
                        else
                        {
                            var offsetFromMiddle = this._currentPage - split;
                            var maxOffset = offsetFromMiddle + this.data.attrs.maximum_page_links;
                            if(maxOffset <= newData.total_pages)
                            {
                                startPage = 1 + offsetFromMiddle;
                                endPage = maxOffset;
                            }
                            else
                            {
                                startPage = newData.total_pages - (this.data.attrs.maximum_page_links - 1);
                                endPage = newData.total_pages;
                            }
                        }
                    }
                    else
                    {
                        startPage = 1;
                        endPage = totalPages;
                    }

                    for(var i = startPage, link, titleString; i <= endPage; i++)
                    {
                        if(i === this._currentPage)
                        {
                            link = document.createElement("span");
                            YAHOO.util.Dom.addClass(link, "rn_CurrentPage");
                            link.innerHTML = i;
                        }
                        else
                        {
                            link = document.createElement("a");
                            link.id = "rn_" + this.instanceID + "_PageLink_" + i;
                            link.href = this.data.js.pageUrl + i;
                            link.innerHTML = i;
                            titleString = this.data.attrs.label_page;
                            if(titleString)
                            {
                                titleString = titleString.replace(/%s/, i);
                                titleString = titleString.replace(/%s/, newData.total_pages);
                                link.title = titleString;
                            }
                        }
                        pagesContainer.appendChild(link);
                        YAHOO.util.Event.addListener(link, "click", this._onPageChange, i, this);
                    }

                    YAHOO.util.Dom.removeClass("rn_" + this.instanceID, "rn_Hidden");
                }
            }
            //update the forward button
            var forwardButton = document.getElementById("rn_" + this.instanceID + "_Forward");
            if(forwardButton)
            {
                if(newData.total_pages > newData.page)
                {
                    YAHOO.util.Dom.removeClass(forwardButton, "rn_Hidden");
                    forwardButton.href = this.data.js.pageUrl + (this._currentPage + 1);
                }
                else
                    YAHOO.util.Dom.addClass(forwardButton, "rn_Hidden", "rn_Hidden");
            }
            //update the back button
            var backButton = document.getElementById("rn_" + this.instanceID + "_Back");
            if(backButton)
            {
                if(newData.page > 1)
                {
                    YAHOO.util.Dom.removeClass(backButton, "rn_Hidden");
                    backButton.href = this.data.js.pageUrl + (this._currentPage - 1);
                }
                else
                    YAHOO.util.Dom.addClass(backButton, "rn_Hidden");
            }
        }
        this._currentlyChangingPage = false;
    }
};