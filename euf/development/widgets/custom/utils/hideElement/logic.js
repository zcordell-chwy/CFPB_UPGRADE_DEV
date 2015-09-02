RightNow.Widget.hideElement = function(data, instanceID)
{
    // Data object contains all widget attributes, values, etc.
    this.data = data;
    this.instanceID = instanceID;

    // hiding stuff
    this.animSpeed = .3;
    this.easingType = YAHOO.util.Easing.easeIn;
    this.elementHeight = -1;
    this.controlElement = this.data.js.control_element;
    this.isInPageFlow = true;
    this.numDisplayCalls = 0;
    this.eo = new RightNow.Event.EventObject();
    this.eo.w_id = this.instanceID;
    this.eo.data = {
        'elementId' : this.controlElement
    };
    if (this.controlElement && this.controlElement.length > 1) {
        RightNow.Event.subscribe('evt_hideElement', this.hideElement, this);
        RightNow.Event.subscribe('evt_showElement', this.showElement, this);
        if (this.data.js.form_field_name) {
            RightNow.Event.subscribe('evt_toggleFormElement', this.onToggleFormElement, this);
            RightNow.Event.subscribe('evt_decreaseDisplayCount', this.onChangeDisplayCount, this);
            RightNow.Event.subscribe('evt_increaseDisplayCount', this.onChangeDisplayCount, this);
        }
        YAHOO.util.Event.onDOMReady(function()
        {
            RightNow.Event.fire('evt_hideElement', this.eo);
        }, null, this);

        this.initialize();
    }
};

RightNow.Widget.hideElement.prototype = {
    initialize : function()
    {
        if (this.controlElement && this.data.js.listen_ids) {
            for (var i = this.data.js.listen_ids.length - 1; i >= 0; i--) {
                YAHOO.util.Event.addListener(this.data.js.listen_ids[i], "click", this.toggleElement, this.data.js.listen_ids[i], this);
            };

        }
        elemDisplayStyle = YAHOO.util.Dom.getStyle(this.controlElement, "position");
        if (elemDisplayStyle == "fixed" || elemDisplayStyle == "absolute") {
            this.isInPageFlow = false;
            this.animSpeed = 2 * this.animSpeed;
        }
    },

    onToggleFormElement : function(type, args)
    {
        var formData = args[0].data;
        var fieldName;
        if (formData.custom) {
            fieldName = formData.table + ".c$" + formData.name;
        }
        else {
            fieldName = formData.table + "." + formData.name;
        }

        if (this.data.js.form_field_name.toLowerCase() == fieldName.toLowerCase()) {
            var visible = true;
            // add ability to compare multiple values
            for (var i = 0; i < this.data.js.form_field_value.length; i++) {
                // add ability to compare negation
                if (this.data.attrs['inverse_compare']) {
                    if (this.data.js.form_field_value[i] == formData.value.toString() || this.data.js.form_field_value[i] == formData.value) {
                        visible = false;
                        break;
                    }
                    else if (formData.label && this.data.js.form_field_value[i] == formData.label[0]) {
                        visible = false;
                        break;
                    }
                    else {
                        visible = true;
                    }
                }
                else {
                    if (this.data.js.form_field_value[i] == formData.value.toString() || this.data.js.form_field_value[i] == formData.value) {
                        visible = true;
                        break;
                    }
                    else if (formData.label && this.data.js.form_field_value[i] == formData.label[0]) {
                        visible = true;
                        break;
                    }
                    else {
                        visible = false;
                    }
                }

            }// endfor

            if (visible) {
                if (this.data.attrs['inverse_compare']) {
                    if (this.numDisplayCalls == 0) {
                        RightNow.Event.fire("evt_increaseDisplayCount", this.eo);
                        RightNow.Event.fire("evt_showElement", this.eo)
                    }
                }
                else {
                    // if the current element is a formData.label and is already displayed, do not increase count
                    if (!(formData.label && this.elementHeight > 0))
                        RightNow.Event.fire("evt_increaseDisplayCount", this.eo);
                    RightNow.Event.fire("evt_showElement", this.eo)
                }
            }
            else {
                if (this.numDisplayCalls < 2)
                    RightNow.Event.fire("evt_hideElement", this.eo)
                if (this.numDisplayCalls > 0)
                    RightNow.Event.fire("evt_decreaseDisplayCount", this.eo);
            }
        }
    },

    toggleElement : function(eventData, args)
    {
        var obj = document.getElementById(args);
        if (this.data.attrs.listen_value) {
            if (obj.value == this.data.attrs.listen_value) {
                RightNow.Event.fire("evt_showElement", this.eo);
            }
            else {
                RightNow.Event.fire("evt_hideElement", this.eo);
            }
        }
        else {
            if (YAHOO.util.Dom.hasClass(this.controlElement, "rn_Hidden")) {
                RightNow.Event.fire("evt_showElement", this.eo);
            }
            else {
                RightNow.Event.fire("evt_hideElement", this.eo);
            }
        }
    },

    getElementHeight : function(element)
    {
        if (YAHOO.util.Dom.hasClass(element, "rn_Hidden")) {
            if (this.elementHeight > -1) {
                return this.elementHeight;
            }
            //ugggghhhhh!
            var currentOpacity = YAHOO.util.Dom.getStyle(element, "opacity");
            var currentZindex = YAHOO.util.Dom.getStyle(element, "z-index");
            var currentPos = YAHOO.util.Dom.getStyle(element, "position");
            YAHOO.util.Dom.setStyle(element, "opacity", "0");
            YAHOO.util.Dom.setStyle(element, "z-index", "-10000");
            YAHOO.util.Dom.setStyle(element, "position", "absolute");
            YAHOO.util.Dom.removeClass(element, "rn_Hidden");
            var thisregion = YAHOO.util.Dom.getRegion(element);
            foundHeight = parseInt(YAHOO.util.Dom.getRegion(element).height);
            YAHOO.util.Dom.addClass(element, "rn_Hidden");
            YAHOO.util.Dom.setStyle(element, "opacity", currentOpacity);
            YAHOO.util.Dom.setStyle(element, "z-index", currentZindex);
            YAHOO.util.Dom.setStyle(element, "position", currentPos);

        }
        else {
            foundHeight = parseInt(YAHOO.util.Dom.getRegion(element).height);
        }
        this.elementHeight = foundHeight;
        return foundHeight;
    },

    /**
     * Event handler for when a page element request the controlElement to be displayed or hidden
     * Used to keep track of the number of form elements that required this conrolElement
     */
    onChangeDisplayCount : function(type, args)
    {
        if (args[0].data.elementId != this.controlElement) {
            return;
        }
        if (type == 'evt_increaseDisplayCount') {
            this.numDisplayCalls++;
        }
        else {
            if (this.numDisplayCalls >= 0)
                this.numDisplayCalls--;
        }

    },

    hideElement : function(type, args)
    {
        if (args[0].data.elementId != this.controlElement) {
            return;
        }
        //if multiple elements have displayed the control element, then wait for them to all request hiding
        if (YAHOO.util.Dom.hasClass(this.controlElement, "rn_Hidden")) {
            return;
        }
        //may as well save the height while it's still visible
        this.getElementHeight(this.controlElement);

        /*
         * 2/28/2012 - Removing opacity animation since it's causing display issues in IE.
         *
         //opacity animation
         var targetOpacityAnim = new YAHOO.util.Anim(this.controlElement, {
         opacity : {
         to : 0
         }
         }, this.animSpeed / 2, this.easingType);

         //height animation
         var targetHeightAnim = new YAHOO.util.Anim(this.controlElement, {
         height : {
         to : 0
         }
         }, this.animSpeed / 2, this.easingType);

         //determine if we're working on an element that's in the page flow so that we know how to chain
         if(!this.isInPageFlow) {
         targetOpacityAnim.onComplete.subscribe(function(event, specs, context) {
         YAHOO.util.Dom.addClass(context.controlElement, "rn_Hidden");
         }, this);

         } else {
         targetOpacityAnim.onComplete.subscribe(function(event, specs, context) {
         // context.displayMain();
         targetHeightAnim.animate();
         }, this);

         targetHeightAnim.onComplete.subscribe(function(event, specs, context) {
         YAHOO.util.Dom.addClass(context.controlElement, "rn_Hidden");
         }, this);

         }

         // Go!
         targetOpacityAnim.animate();
         *
         */
        YAHOO.util.Dom.addClass(this.controlElement, "rn_Hidden");
    },

    showElement : function(type, args)
    {
        if (args[0].data.elementId != this.controlElement) {
            return;
        }
        if (!YAHOO.util.Dom.hasClass(this.controlElement, "rn_Hidden")) {
            return;
        }

        /*
         * 2/28/2012 - Removing opacity animation since it's causing display issues in IE.
         *
         //make sure the element is coming from a consistant state
         var targetHeight = this.getElementHeight(this.controlElement)
         if(this.isInPageFlow) {
         YAHOO.util.Dom.setStyle(this.controlElement, 'height', '0');
         }
         YAHOO.util.Dom.setStyle(this.controlElement, 'opacity', '0');

         //opacity animation
         var targetOpacityAnim = new YAHOO.util.Anim(this.controlElement, {
         opacity : {
         to : 1
         }
         }, this.animSpeed / 2, this.easingType);

         //height animation
         var targetHeightAnim = new YAHOO.util.Anim(this.controlElement, {
         height : {
         to : targetHeight
         }
         }, this.animSpeed / 2, this.easingType);

         if(!this.isInPageFlow) {
         YAHOO.util.Dom.removeClass(this.controlElement, "rn_Hidden");
         // targetOpacityAnim.animate();
         } else {
         targetOpacityAnim.onComplete.subscribe(function(event, specs, context) {
         //remove the anim height tag in case the element changes size.
         YAHOO.util.Dom.setStyle(context.controlElement, 'height', '');
         }, this);

         targetHeightAnim.onComplete.subscribe(function(event, specs, context) {
         targetOpacityAnim.animate();
         }, this);

         YAHOO.util.Dom.removeClass(this.controlElement, "rn_Hidden");
         /targetHeightAnim.animate();
         }
         *
         */
        YAHOO.util.Dom.removeClass(this.controlElement, "rn_Hidden");
    }
};
