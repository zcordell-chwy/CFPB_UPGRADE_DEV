RightNow.Widget.SampleWidget = function(data, instanceID)
{
    //Data object contains all widget attributes, values, etc.
    this.data = data;
    this.instanceID = instanceID;

    //Perform any initial javascript logic here

};
RightNow.Widget.SampleWidget.prototype = {
    //Define any widget functions here
    
    
    _sampleFunction1: function(parameter)
    {

    },  // Note the comma here

    _sampleFunction2: function(parameter)
    {

    }   // no comma on last function in prototype
};
