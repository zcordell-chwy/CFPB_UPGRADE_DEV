RightNow.Widget.LogoutLink2 = function(data, instanceID){
    this.data = data;
    this.instanceID = instanceID;
    if(document.getElementById("rn_" + this.instanceID))
    {
        YAHOO.util.Event.addListener("rn_" + this.instanceID + "_LogoutLink", "click", this._onLogoutClick, null, this);
        RightNow.Event.subscribe("evt_logoutResponse", this._onLogoutCompleted, this);
    }
};

RightNow.Widget.LogoutLink2.prototype = {
    /**
     * Event handler for when logout has occured
     * @param {String} type Event name
     * @param {Object} arg Event arguments
     */
    _onLogoutCompleted: function(type, arg)
    {
        var result = arg[0],
            Url = RightNow.Url,
            eventData = arg[1];
        if(result.success === 1 && !RightNow.UI.Form.logoutInProgress && eventData.w_id === this.instanceID)
        {
            RightNow.UI.Form.logoutInProgress = true;
            //If redirect is specified in the controller, use it, otherwise default
            //to response from server for compatability
            if(this.data.js && this.data.js.redirectLocation)
            {
                Url.navigate(this.data.js.redirectLocation, true);
            }
            else
            {
                if(result.socialLogout)
                    Url.navigate(result.socialLogout, true);
                else if(this.data.attrs.redirect_url === '')
                    Url.navigate(result.url, true);
                else
                    Url.navigate(this.data.attrs.redirect_url + result.session, true);
            }
        }
    },

    /**
     * Event handler for when logout is clicked.
     */
    _onLogoutClick: function()
    {
        //console.log('yo');
        var eo = new RightNow.Event.EventObject();
        eo.w_id = this.instanceID;
        eo.data.currentUrl = window.location.pathname;
        eo.data.redirectUrl = this.data.attrs.redirect_url;
        //console.log(eo.data);
        //RightNow.Event.fire("evt_logoutRequest", eo);
    }
};
