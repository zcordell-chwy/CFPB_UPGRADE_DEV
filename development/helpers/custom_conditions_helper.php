<?php

/*
* Returns the status ID for the current incident
*/
function IncidentStatusId()
{
	$incident = getBusinessObjectInstance('incidents');
	return $incident->status_id->value;
}
