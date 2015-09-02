RightNow.Widget.JobDetail = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();
	this._reprocessButton = YAHOO.util.Dom.get( 'rn_' + this.instanceID + '_Reprocess' );

	YAHOO.util.Event.addListener( this._reprocessButton, 'click', this._reprocessJob, null, this );
};

RightNow.Widget.JobDetail.prototype = {
	/**
	 * Event handler to make the system attempt to reprocess a job.
	 */
	_reprocessJob: function( type, args ) {
		var postData = {
			'f_tok': RightNow.UI.Form.formToken,
			'jobID': this.data.js.job.ID,
			'reprocess': true
		};
	
		var requestOptions = {
			successHandler: function( response )
			{
				; // RightNow.UI.Dialog.messageDialog( "Your reprocessed data export job is now complete." );
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

		RightNow.UI.Dialog.messageDialog( "Your data export job is now being reprocessed." );
		RightNow.Ajax.makeRequest( this.data.js.export_kickoff_url, postData, requestOptions );
	}
};
