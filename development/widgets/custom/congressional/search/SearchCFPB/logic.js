RightNow.Widget.SearchCFPB = function(data, instanceID) {
    this.data = data;
    this.instanceID = instanceID;
    YAHOO.util.Event.onDOMReady(this._startSearch);
};

RightNow.Widget.SearchCFPB.prototype = {
    
    /*
     * When the widget has loaded, initiate the search
     * this fixes the issue when paginating with product assigned to the contact
     */
    _startSearch : function() {
        if ((document.URL.indexOf("#s") <= 0) && (document.URL.indexOf("questions") <= 0)) {
            RightNow.Event.fire("evt_startSearch");
        }
    }
};
