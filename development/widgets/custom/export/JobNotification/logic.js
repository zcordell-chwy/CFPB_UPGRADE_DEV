RightNow.Widget.JobNotification = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;

	if( this.data.js.authorized === true && this.data.js.num_finished_jobs > 0 )
	{
		this._notifyUser( this.data.js.num_finished_jobs );
	}
};

RightNow.Widget.JobNotification.prototype = {
	/**
	 * Function showing the number of finished jobs awaiting the user's attention.
	 *
	 * @param	INT	The number of finished jobs.
	 */
	_notifyUser: function( numJobs )
	{
		var messageText = RightNow.Text.sprintf( this.data.js.num_finished_jobs_notify_text, numJobs );
		RightNow.UI.Dialog.messageDialog( messageText );
	}
};
