RightNow.Widget.FormReviewFieldDisplay4 = function(data, instanceID)
{
    this.data       = data;
    this.instanceID = instanceID;

    this.EUF_DT_CHECK   = RightNow.Interface.Constants.EUF_DT_CHECK;
    this.EUF_DT_FATTACH = RightNow.Interface.Constants.EUF_DT_FATTACH;
    this.EUF_DT_INT     = RightNow.Interface.Constants.EUF_DT_INT;
    this.EUF_DT_MEMO    = RightNow.Interface.Constants.EUF_DT_MEMO;
    this.EUF_DT_RADIO   = RightNow.Interface.Constants.EUF_DT_RADIO;
    this.EUF_DT_SELECT  = RightNow.Interface.Constants.EUF_DT_SELECT;
    this.EUF_DT_HIERMENU= RightNow.Interface.Constants.EUF_DT_HIERMENU;
    this.EUF_DT_VARCHAR = RightNow.Interface.Constants.EUF_DT_VARCHAR;
    this.EUF_DT_DATETIME= RightNow.Interface.Constants.EUF_DT_DATETIME;
    this.EUF_DT_DATE    = RightNow.Interface.Constants.EUF_DT_DATE;

    this.data_value     = document.getElementById(this.instanceID + '_DataValue');
    this.data_value_no  = document.getElementById(this.instanceID + '_DataValue_No');
    this.data_value_yes = document.getElementById(this.instanceID + '_DataValue_Yes');
    this.widget         = document.getElementById('rn_' + this.instanceID);
    
    this.radio_group 	= "";

    //RightNow.Event.subscribe('evt_toggleFormElement', this.onToggleFormElement, this);
};

RightNow.Widget.FormReviewFieldDisplay4.prototype = {
    /*
    * Form element change handler
    *
    * @param event evt  Event
    * @param array args Args
    */
    onToggleFormElement : function(evt, args)
    {
        var form_data = args[0].data;
        var custom    = form_data.custom;
        var name      = form_data.name;
        var table     = form_data.table;
        var namespace = form_data.namespace;
       
        // if data type is hiermenu, then set the value to the label
        if(this.data.js.type == this.EUF_DT_HIERMENU) {
            var value = form_data.label;
        } else {
            var value = form_data.value;
        }
             
        // Combine the table and name.
        if (custom)
            name = 'c$' + name;
        if (namespace)
            name = namespace + '.' + table + '.' + name;
        else if (table)
            name = table + '.' + name;


        if(this.data.js.name !== name){
			//if we're a member of a radio group, and are not selected, update the ui appropriately
			if(this.radio_group == form_data.radio_group){
				this.setValue();
				this.updateVisibility();
			}
            return;
	}

	//save that this form element is a member of a radio group
	//and make it appear like a radio button
	if(form_data.radio_group){
		if(this.radio_group.length < 1 ){
			this.radio_group = form_data.radio_group;
			if(this.data.js.type == this.EUF_DT_CHECK){
				//work around IE7
				//var changeHtml = this.data_value.parentNode.innerHTML
				//changeHtml.replace("checkbox", "radio");
				//this.data_value.parentNode.innerHTML = changeHtml;
				//this.data_value.type = "radio";
			}
		}
		this.radio_group = form_data.radio_group;
	}
	
	if(this.data.attrs.show_menu_as_radios)
	{
                var rgx1 = '_' + form_data.name.toLowerCase();
		var rgx2 = '_' + value;
                var label = YAHOO.util.Dom.getElementBy(function(el){return (el.getAttribute('for').match(rgx1) && el.getAttribute('for').match(rgx2));}, 'label');
		value = (label) ? label.innerHTML : value;
	}
	
        this.setValue(value);
    },

    /*
    * Set the value
    *
    * @param mixed value Value
    */
    setValue: function(value)
    {
        switch(this.data.js.type)
        {
            case this.EUF_DT_CHECK:
                this.data_value.checked = value;
                break;
            case this.EUF_DT_RADIO:
                if(value === '1')
                {
                    this.data_value_no.checked  = false;
                    this.data_value_yes.checked = true;
                }
                else
                {
                    this.data_value_no.checked  = true;
                    this.data_value_yes.checked = false;
                }
                break;
            case this.EUF_DT_FATTACH:
                var html = '<ul>';
                if(value)
                {
                    for(var i = 0; i < value.length; ++i)
                    {
                        var file = value[i];
                        if(file && file.name && file.size)
						{
							//Convert byte size to kilobyte size and round to 2 decimal places
							var fileSize = file.size / 1024;
							fileSize = Math.round(fileSize * 100) / 100;
                            html += '<li>' + file.name + '&nbsp;(' + fileSize + 'KB)</li>';
						}
                    }
                }
                html += '</ul>';
                this.data_value.innerHTML = html;
                break;
            default:
                if (value)
				{
					if( this.data.attrs.COM_type === 'Boolean' )
					{
					    if ( value === null )
                           this.data_value.innerHTML = '';
						else if ( value == '1' || value == 'Yes')
							this.data_value.innerHTML = 'Yes';
						else if (value == '0'|| value == 'No')
							this.data_value.innerHTML = 'No';
					}
					else
					{
                        this.data_value.innerHTML = this.maskValue( value.toString() );
					}
				}
                else
				{
                    this.data_value.innerHTML = ''; // null;
				}
                break;
        }
         this.updateVisibility(value);
    },

	/**
	 * Function to optionally mask the value of a field.
	 *
	 * @param value	STRING	The value to be optionally masked.
	 * @return	STRING	The optionally masked value.
	 */
	maskValue: function( value )
	{
		var maskedValue = value;
		if( this.data.attrs.only_show_final_four === true )
		{
			var substrToMask = value.slice( 0, -4 );
			var maskedSubstr = new Array( substrToMask.length + 1 ).join( '*' );
			maskedValue = value.replace( substrToMask, maskedSubstr );
		}
                if(this.data.attrs.mask_all === true)
                {
                        var maskedSubstr = new Array(value.length + 1).join('*');
                        maskedValue = value.replace(value, maskedSubstr);
                }
                if(this.data.attrs.mask_ssn_all === true)
                {
			if(value.match(/^\d{9}$/) || value.match(/^\d{3}-\d{2}-\d{4}$/))
			{
                        	var maskedSubstr = new Array(value.length + 1).join('*');
	                        maskedValue = value.replace(value, maskedSubstr);
			}
                }

		return maskedValue;
	},

    /*
    * Update the visibility based on the specified value.
    *
    * @param mixed value Value
    */
    updateVisibility: function(value)
    {
        var hidden = false;

        if (this.data.js.hidden == 1) // may have been set in controller
            hidden = true;
        else 
        { 
            switch (this.data.js.type)
            {
                case this.EUF_DT_CHECK:
                    if (!value)
                        hidden = true;
                    break;
                case this.EUF_DT_INT:
                    if (value === '' || isNaN(value))
                        hidden = true;
                    break;
                case this.EUF_DT_MEMO:
                case this.EUF_DT_HIERMENU:
                case this.EUF_DT_SELECT:
                case this.EUF_DT_VARCHAR:
                case this.EUF_DT_DATE:
                case this.EUF_DT_DATETIME:
                    if (value === '')
                        hidden = true;
                    break;
                case this.EUF_DT_FATTACH:
                    hidden = true;
                    for (var i = 0; i < value.length; ++i){
                        if (value[i] !== null){
                            hidden = false;
    					}
    				}
                    break;
                    
    			case this.EUF_DT_RADIO:
    				if (!value)
                        hidden = true;
                    break;
    				
    			default:
    				hidden = true;
    				break; 
            }
        }
        
        if (hidden)
            YAHOO.util.Dom.addClass(this.widget, 'rn_Hidden');
        else
            YAHOO.util.Dom.removeClass(this.widget, 'rn_Hidden');
    },

    /*
    * Mask CC number
    *
    * @param event evt  Event
    * @param array args Args
    */
    _maskCCinfo: function(str)
    {
        var newStr='';
        var last4pos = str.length - 4;
        var i=0;
        for (i=0; i<last4pos; i++)
            newStr+='X';
        newStr += str.substring(last4pos);
        //console.log('mask: '+newStr);
        return newStr;
    }
};
