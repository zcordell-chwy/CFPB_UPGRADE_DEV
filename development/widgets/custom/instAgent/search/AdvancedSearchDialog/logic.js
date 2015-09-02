RightNow.Widget.AdvancedSearchDialog = function(data, instanceID) {
    this.data = data;
    this.instanceID = instanceID;
    YAHOO.util.Event.addListener("rn_" + this.instanceID + "_TriggerLink", "click", this._openDialog, null, this);
};

RightNow.Widget.AdvancedSearchDialog.prototype = {
    /*
    * Build the dialog the first time; show the dialog subsequent times
    */
    _openDialog: function()
    {
        if(!this._dialog)
        {
            var dialogDiv = document.getElementById("rn_" + this.instanceID + "_DialogContent");
            if(dialogDiv)
            {
                //construct buttons
                var buttons = [
                {text:this.data.attrs.label_search_button, handler:{fn:this._performSearch, scope:this}},
                {text:this.data.attrs.label_cancel_button, handler:{fn:this._cancelFilters, scope:this}}];
                this._dialog = RightNow.UI.Dialog.actionDialog(this.data.attrs.label_dialog_title, dialogDiv, {"buttons": buttons, "width": "500px"});
                YAHOO.util.Dom.addClass(this._dialog.id, "rn_AdvancedSearchDialog");
                YAHOO.util.Dom.removeClass(dialogDiv, "rn_Hidden");
                //focus on trigger link when the dialog closes
                this._dialog.hideEvent.subscribe(function(){
                        var trigger = document.getElementById("rn_" + this.instanceID + "_TriggerLink");
                        if(trigger && trigger.focus)
                            trigger.focus();
                }, null, this);
            }
        }
        this._dialogClosed = false;
        this._dialog.show();
            // Handle the close icon ('x') click
        this._dialog.hideEvent.subscribe(this._cancelFilters, null, this);
    },
    
    /*
    * Perform a search by firing the searchRequest event
    * and closes the dialog
    */
    _performSearch: function()
    {
        this._closeDialog();
        var eo = new RightNow.Event.EventObject();
        eo.w_id = this.instanceID;
        eo.filters = {"report_id" : this.data.attrs.report_id, "reportPage" : this.data.attrs.report_page_url};
        RightNow.Event.fire("evt_searchRequest", eo);
    },
    
    /*
    * Resets all search filters
    */
    _cancelFilters: function()
    {
        if(this._dialogClosed) return;
        
        this._closeDialog();
        var eo = new RightNow.Event.EventObject();
        eo.data = {"name" : "all"};
        eo.filters = {"report_id" : this.data.attrs.report_id};
        eo.w_id = this.instanceID;
        RightNow.Event.fire("evt_resetFilterRequest", eo);
    },

    /*
    * Closes the dialog
    */
    _closeDialog: function()
    {
        this._dialogClosed = true;
        if(this._dialog)
            this._dialog.hide();
    }
};
