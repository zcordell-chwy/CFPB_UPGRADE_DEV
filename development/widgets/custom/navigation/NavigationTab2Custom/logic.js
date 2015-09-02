RightNow.Widget.NavigationTab2Custom = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;

    if(this.data.attrs.subpages)
    {
        this._toggleElement = document.getElementById("rn_" + this.instanceID + "_DropdownButton");
        if(!this._toggleElement) return;
        
        YAHOO.util.Event.addListener(this._toggleElement, 'click', this._toggleDropdown, null, this);
        this._tabElement = document.getElementById("rn_" + this.instanceID);
        this._dropdownElement = document.getElementById("rn_" + this.instanceID + "_SubNavigation");
        this._linkElements = YAHOO.util.Dom.getChildren(this._dropdownElement);
        YAHOO.util.Event.addListener(this._linkElements[this._linkElements.length-1], 'focus', this._toggleDropdown, null, this);
        YAHOO.util.Event.addListener(this._linkElements[0], 'focus', this._toggleDropdown, null, this);
        //close the dropdown when shift-tabbing off the tab
        YAHOO.util.Event.addListener("rn_" + this.instanceID + "_Link", "keydown", function(evt){
                if(evt.keyCode && evt.shiftKey && evt.keyCode === YAHOO.util.KeyListener.KEY.TAB)
                    this._closeDropdown({type:"click"}, null);
        }, null, this);
    }
    if(this.data.attrs.searches_done > 0 && this.data.js.searches < this.data.attrs.searches_done)
        RightNow.Event.subscribe("evt_searchInProgressRequest", this._onSearchCountChanged, this);
};

RightNow.Widget.NavigationTab2Custom.prototype = {
    /**
    * Stops the default event and toggles between
    * displaying and hiding the dropdown list of links.
    * @param evt Event Click or Focus event.
    * @param args Object Event arguments
    */
    _toggleDropdown: function(evt, args)
    {
        YAHOO.util.Event.stopEvent(evt);
        if(this._dropdownOpen && YAHOO.util.Event.getTarget(evt) === this._toggleElement)
            this._closeDropdown({type:"click"}, null);
        else
            this._openDropdown();
        return false;
    },

    /**
    * Displays the dropdown list of sublinks and subscribes
    * to appropriate events that dictate the proper closing of
    * the dropdown.
    */
    _openDropdown: function()
    {
        if(!this._dropdownOpen)
        {
            this._tabRegion = YAHOO.util.Dom.getRegion(this._tabElement);
            //console.log(this._tabRegion);
            YAHOO.util.Dom.setStyle(this._dropdownElement, "top", (this._tabRegion.bottom - 92) + "px");
            var left = YAHOO.util.Dom.hasClass(this._tabElement, this.data.attrs.css_class) ? 4 : 0;
            YAHOO.util.Dom.setStyle(this._dropdownElement, "left", (this._tabRegion.left - left - 20) + "px");
            YAHOO.util.Dom.removeClass(this._dropdownElement, "rn_ScreenReaderOnly");

            YAHOO.util.Event.addListener([this._tabElement, this._dropdownElement], 'mouseout', this._closeDropdown, null, this);
            YAHOO.util.Event.addListener([this._dropdownElement, document], 'click', this._closeDropdown, null, this);
            //close dropdown on tabbing off of its last link
            var lastDropdownElement = this._linkElements[this._linkElements.length - 1];
            YAHOO.util.Event.addListener(lastDropdownElement, "keydown", function(evt) {
                if(evt.keyCode && !evt.shiftKey && evt.keyCode === YAHOO.util.KeyListener.KEY.TAB)
                    this._closeDropdown(evt);
            }, null, this);
            this._dropdownOpen = true;
        }
    },

    /**
    * Hides the dropdown list of sublinks and optionally
    * purges the element that triggered the event.
    * @param evt Event Click, Blur, or Mouseout event
    * @param args Object Event arguments
    */
    _closeDropdown: function(evt, args)
    {
        if(this._dropdownOpen)
        {
            if(evt.type !== "keydown" && evt.type !== "click")
            {
                //check to see if we're hovering or clicking over an element that's okay
                var coordinates = YAHOO.util.Event.getXY(evt);
                coordinates = new YAHOO.util.Point(coordinates[0], coordinates[1]);

                this._dropdownRegion = this._dropdownRegion || YAHOO.util.Dom.getRegion(this._dropdownElement);
                if((this._tabRegion && this._tabRegion.contains(coordinates)) || (this._dropdownRegion && this._dropdownRegion.contains(coordinates)))
                    return;
            }
            YAHOO.util.Event.purgeElement(document, false);
            YAHOO.util.Dom.setStyle(this._dropdownElement, "top", "auto");
            YAHOO.util.Dom.setStyle(this._dropdownElement, "left", "-10000px");
            YAHOO.util.Dom.addClass(this._dropdownElement, "rn_ScreenReaderOnly");
            this._dropdownOpen = false;
        }
    },
    
    /**
     * Updates the number of searches performed to determine if we need to show the tab
     */
    _onSearchCountChanged: function()
    {
        this.data.js.searches++;
        if(this.data.js.searches >= this.data.attrs.searches_done)
        {
            RightNow.Event.unsubscribe("evt_searchInProgressRequest", this._onSearchCountChanged);
            var tabElement = document.getElementById('rn_' + this.instanceID);
            if(tabElement)
                YAHOO.util.Dom.removeClass(tabElement, 'rn_Hidden');
        }
    }
    
};
