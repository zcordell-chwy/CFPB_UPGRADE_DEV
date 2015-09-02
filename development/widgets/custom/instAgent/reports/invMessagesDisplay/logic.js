RightNow.Widget.invMessagesDisplay = function(data, instanceID) {
	this.data = data;
	this.instanceID = instanceID;
	this._eo = new RightNow.Event.EventObject();

	RightNow.Event.subscribe("evt_sortTypeResponse", this._onSortTypeResponse, this);

};
RightNow.Widget.invMessagesDisplay.prototype = {
	/**
	 * Event handler executed when the sort type is changed
	 *
	 * @param type string Event type
	 * @param args object Arguments passed with event
	 */
	_onSortTypeResponse : function(type, args) {
		var evt = args[0];
		if(evt.filters.report_id == this.data.attrs.report_id) {
			this._sortEo.filters.data.col_id = evt.filters.data.col_id;
			this._sortEo.filters.data.sort_direction = evt.filters.data.sort_direction;
		}
	}
};
