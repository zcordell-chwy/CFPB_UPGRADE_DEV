RightNow.Widget.SubscribeLink = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._numberOfNotifs = (this.data.js.prodCatList && this.data.js.prodCatList.val) ? this.data.js.prodCatList.val.length : 0;

    this._subscribeLink = document.getElementById("rn_" + this.instanceID + "_Subscribe");
    this._unSubscribeLink = document.getElementById("rn_" + this.instanceID + "_UnSubscribe");
    
    YAHOO.util.Event.addListener("rn_" + this.instanceID + "_SubscribeButton", "click", this._subscribeHandler, null, this);
    YAHOO.util.Event.addListener("rn_" + this.instanceID + "_UnSubscribeButton", "click", this._unSubscribeHandler, null, this);
    this._toggleDisplayLink();

};

RightNow.Widget.SubscribeLink.prototype = {
    
    /*
     * Show/hide the subscrbie/unsubscribe links
     */     
    _toggleDisplayLink: function()
    {
        //console.log(this.data.js.prodCatList.val);
        if (this.data.js.prodCatList.val != null)
        {
            // display unsubscribe, hide subscribe
            this._subscribeLink.className = "rn_Hidden";
            this._unSubscribeLink.className = "";
        }
        else
        {
            // display subscribe, hide unsubscribe
            this._subscribeLink.className = "";
            this._unSubscribeLink.className = "rn_Hidden";
        }
        
    },
    
    /*
     * Subscribe to the prod cat
     */
     _subscribeHandler : function(arg1, arg2) {
        var postData = {
            "chain" : this.data['attrs']['hier_map'],
            "filter_type" : this.data['attrs']['filter_type']
        };
        RightNow.Ajax.makeRequest("/ci/ajaxRequest/prodcatAddNotification", postData, {
            successHandler: function(response)
            {
                var resp = RightNow.JSON.parse(response.responseText);
                this.data.js.prodCatList.val = resp.val[0];
                this._toggleDisplayLink();
            },
            scope: this
        });
    },
    
    /*
     * UnSubscribe to the prod cat
     */
     _unSubscribeHandler : function(arg1, arg2) {
        var notifElement = this.data.js.prodCatList['val'][0].split(":");;
        var postData = {
            "chain" : notifElement[1],
            "filter_type" : notifElement[0],
            "timestamp" : notifElement[2]
        };
        RightNow.Ajax.makeRequest("/ci/ajaxRequest/prodcatDeleteNotification", postData);
        this.data.js.prodCatList.val = null;
        this._toggleDisplayLink();
    }
};

