RightNow.Widget.ProductCategorySearchRoleFilter = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();
    this._currentIndex = 0;
    this._noValueNodeIndex = 0;
    this._displayField = document.getElementById("rn_" + this.instanceID + "_" + this.data.attrs.filter_type + "_Button");
    this._displayFieldVisibleText = document.getElementById("rn_" + this.instanceID + "_ButtonVisibleText");
    this._accessibleView = document.getElementById("rn_" + this.instanceID + "_Links");
    this._outerTreeContainer = "rn_" + this.instanceID + ((this.data.attrs.show_confirm_button_in_dialog) ? "_TreeContainer" : "_Tree");
    
    if(!this._displayField) return;

    RightNow.Event.subscribe("evt_getFiltersRequest", this._getFiltersRequest, this);
    RightNow.Event.subscribe("evt_menuFilterGetResponse", this._getSubLevelResponse, this);
    RightNow.Event.subscribe("evt_accessibleTreeViewGetResponse", this._getAccessibleTreeViewResponse, this);
    RightNow.Event.subscribe("evt_reportResponse", this._onReportResponse, this);
    RightNow.Event.subscribe("evt_resetFilterRequest", this._onResetRequest, this);

    //toggle panel on/off when button is clicked
    YAHOO.util.Event.addListener(this._displayField, "click", this._toggleProductCategoryPicker, null, this);
    YAHOO.util.Event.addListener("rn_" + this.instanceID + "_LinksTrigger", "click", this._toggleAccessibleView, null, this);

    this._initializeFilter();

    //build menu panel
    this._panel = new YAHOO.widget.Panel(this._outerTreeContainer, {close:false, width:"300px", visible:false, constraintoviewport:true});
    this._panel.setHeader("");
    this._panel.render();
    YAHOO.util.Dom.setStyle("rn_" + this.instanceID + "_Tree", "overflow-y", "auto");

    if(this.data.js.defaultData)
        this._buildTree();
};

RightNow.Widget.ProductCategorySearchRoleFilter.prototype = {

    /**
    * Removes the specified menu items from the attributes 
    */    
    _removeMenuItems : function(hierData)
    {                                      
        var newArray = Array();
        var newArrayWrap = Array();
        var newIndx = 0;
        for (var i=0; i<hierData.length; i++)
        {
            var menuId = hierData[i].value;
            if(!this._inArray(menuId, this.data.attrs.remove_menu_items.split(',')))
                newArray[newIndx++] = hierData[i]; 
        }
        newArrayWrap[0] = newArray;
        return newArrayWrap;
    },
    _inArray : function(needle, haystack)
    {
        for(var i=0; i<haystack.length; i++)
        {
            if(haystack[i] == needle)
                return true;
        }
        return false;
    },

    /**
    * Constructs the YUI Treeview widget for the first time with initial data returned
    * from the server. Pre-selects and expands data that is expected to be populated.
    */
    _buildTree : function()
    {
        var treeDiv = document.getElementById("rn_" + this.instanceID + "_Tree");
        if(treeDiv && YAHOO.widget.TreeView)
        {
            this._tree = new YAHOO.widget.TreeView(treeDiv);
            this._tree.setDynamicLoad(RightNow.Event.createDelegate(this, this._getSubLevelRequest));
            var root = this._tree.getRoot(),
                defaultValues = false,
                hierData = this.data.js.hierData || this.data.js.hierDataNone;
            
            // we need to remove specific hierData objects based on remove_menu_items attribute
            hierData = this._removeMenuItems(hierData[0]);
            
            // role based product filter, display only products contacts are allowed to view based on contacts.c$product_role
            // setup a new hierData
            /*
            var newHierData;
            for (var key in hierData) 
            {
                if(hierData.hasOwnProperty(key))
                {                
                    var obj = hierData[key];
                    for (var hierKey in obj)
                    {
                        if(obj.hasOwnProperty(hierKey))
                        {
                            if(obj[hierKey].value == 4)
                            {
                                newHierData = {};
                                //console.log(obj[hierKey].value);
                            }
                        }
                    }
                }
            }
            */
            // only delete this.data.js.hierData if this.data.js.hierDataNone exists
            // otherwise, the customer has updated logic.js and not updated the controller  
            if(this.data.js.hierData && this.data.js.hierDataNone)
                delete this.data.js.hierData;

            //if there is no confirm button tab should close the panel 
            //but when there is tab should be ignored and by default take you to the confirm button
            if(!this.data.attrs.show_confirm_button_in_dialog) {
                YAHOO.util.Event.addListener(this._tree.getEl(), "keydown", function(ev){
                    if(YAHOO.util.Event.getCharCode(ev) === YAHOO.util.KeyListener.KEY.TAB)
                    {
                        var currentNode = this._tree.currentFocus;
                        if(currentNode.href) {
                            if(currentNode.target) {
                                window.open(currentNode.href, node.target);
                            }
                            else {
                                window.location(currentNode.href);
                            }
                        }
                        else {
                            currentNode.toggle();
                        }
                        this._tree.fireEvent('enterKeyPressed', currentNode);
                        YAHOO.util.Event.preventDefault(ev);
                    }
                }, null, this);
            }
            for(var i = 0, node, length = hierData.length; i < length; i++)
            {
                for(var j = 0, nodeData; j < hierData[i].length; j++)
                {
                    //if this node has a parent, it needs to be retrieved so that this node
                    //can properly attach itself to it
                    nodeData = hierData[i][j];
                    if(i !== 0 && nodeData.parentID)
                        root = this._tree.getNodeByProperty("hierValue", nodeData.parentID);

                    node = new YAHOO.widget.MenuNode(nodeData.label, root);
                    node.hierValue = nodeData.value;
                    node.href = 'javascript:void(0);';
                    if(nodeData.selected)
                    {
                        //if it should be pre-selected by default
                        defaultValues = true;
                        this._currentIndex = node.index;
                    }
                    if(!nodeData.hasChildren)
                    {
                        //if it doesn't have children then turn off the +/- icon
                        //and notify that the node is already loaded
                        node.dynamicLoadComplete = true;
                        node.iconMode = 1;
                    }
                }
                root.loadComplete();
            }
            var noValueNode = this._tree.getRoot().children[0];
            noValueNode.isLeaf = true;
            this._noValueNodeIndex = noValueNode.index;

            this._tree.subscribe("enterKeyPressed", this._enterPressed, null, this);
            if(this.data.attrs.show_confirm_button_in_dialog)
            {
                YAHOO.util.Event.addListener("rn_" + this.instanceID + "_" + this.data.attrs.filter_type + '_ConfirmButton', "click", function(){
                    this._selectNode({node: this._tree.currentFocus});
                }, null, this);
                YAHOO.util.Event.addListener("rn_" + this.instanceID + "_" + this.data.attrs.filter_type + '_CancelButton', "click", this._toggleProductCategoryPicker, null, this);
                YAHOO.util.Event.addListener("rn_" + this.instanceID + "_" + this.data.attrs.filter_type + '_CancelButton', "keydown", function(ev){
                    if(YAHOO.util.Event.getCharCode(ev) === YAHOO.util.KeyListener.KEY.TAB && !ev.shiftKey)
                        this._displaySelectedNodesAndClose(true);
                }, null, this);
            }
            else
            {
                this._tree.subscribe('clickEvent', this._selectNode, null, this);
            } 
            //scroll container to 20px above expanded node
            this._tree.subscribe('expandComplete', function(node) {
                document.getElementById('rn_' + this.instanceID + "_Tree").scrollTop = node.getEl().offsetTop - 20;
            }, null, this);
            this._tree.collapseAll();
            YAHOO.util.Dom.setStyle("rn_" + this.instanceID + "_Tree", "display", "block");
            if(this.data.attrs.show_confirm_button_in_dialog)
                YAHOO.util.Dom.setStyle("rn_" + this.instanceID + "_TreeContainer", "display", "block");
            if(defaultValues)
                this._displaySelectedNodesAndClose(false);
        }
    },

    /**
    * Creates and displays a dialog consisting of an accessible list of items.
    */
    _displayAccessibleDialog: function()
    {
        //build tree for the first time
        if(!this._tree)
            this._buildTree();
        // If the dialog doesn't exist, create it.  (Happens on first click).
        if(!(this._dialog))
        {
            // Set up buttons with handler functions.
            var handleDismiss = function()
            {
                this.hide();
            };

            this._buttons = [ {text: RightNow.Interface.getMessage("CANCEL_CMD"), handler: handleDismiss, isDefault: false} ];
            // Create the dialog.
            YAHOO.util.Dom.removeClass(this._accessibleView, "rn_Hidden");
            this._dialog = RightNow.UI.Dialog.actionDialog(this.data.attrs.label_nothing_selected, this._accessibleView, {"buttons": this._buttons, "width": "400px"});
        }
        else
        {
            var currentlySelectedSpan = document.getElementById("rn_" + this.instanceID + "_IntroCurrentSelection");
            var introLink = document.getElementById("rn_" + this.instanceID + "_Intro");
            if(currentlySelectedSpan && introLink)
            {
                var currentNode = this._tree.getNodeByIndex(this._currentIndex);
                if(!currentNode)
                {
                    currentNode = {};
                    currentNode.hierValue = 0;
                }
                var localInstanceID = this.instanceID;
                introLink.onclick = function(){document.getElementById("rn_" + localInstanceID + "_AccessibleLink_" + currentNode.hierValue).focus();};
                var selectedNodes = this._getSelectedNodesMessage();
                selectedNodes = selectedNodes[0] ? selectedNodes.join(", ") : RightNow.Interface.getMessage("NO_VAL_LBL");
                currentlySelectedSpan.innerHTML = RightNow.Text.sprintf(RightNow.Interface.getMessage("SELECTION_PCT_S_ACTIVATE_LINK_JUMP_MSG"), selectedNodes);
            }
        }

        YAHOO.lang.later(1000, this._dialog, 'show');
        return false;
    },

    /**
    * Toggles accessible view.
    */
    _toggleAccessibleView: function(e)
    {
        if(this._dataType === "categories" && this.data.js.linkingOn)
            this._eo.data.linkingProduct = RightNow.UI.Form.currentProduct;

        if(this._flatTreeViewData)
            this._displayAccessibleDialog();
        else
            RightNow.Event.fire("evt_accessibleTreeViewRequest", this._eo);
    },

    /**
    * Listens to response from the server and constructs an HTML tree according to
    * the flat data structure given.
    * @param e String Event name
    * @param args Object Event arguments
    */
    _getAccessibleTreeViewResponse: function(e, args)
    {
        if(args[0].data.hm_type != this._eo.data.hm_type)
            return;
        var evtObj = args[0];
        if(evtObj.data.data_type == this._dataType)
        {
            this._flatTreeViewData = evtObj.data.accessibleLinks;
            //add the No Value node
            var noValue = {0: RightNow.Interface.getMessage("NO_VAL_LBL"),
                           1: 0,
                           hier_list: 0,
                           level: 0};
            if(!YAHOO.lang.isArray(this._flatTreeViewData))
            {
                //convert object to array because objects don't support unshift drop off the nonNumeric values
                var tempArray = [];
                for(var i in this._flatTreeViewData)
                    if(!isNaN(parseInt(i)))
                        tempArray[i] = this._flatTreeViewData[i];    
                
                this._flatTreeViewData = tempArray;
            }
            this._flatTreeViewData.unshift(noValue);
            var htmlList = "<p><a href='javascript:void(0)' id='rn_" + this.instanceID + "_Intro'" + 
            "onclick='document.getElementById(\"rn_" + this.instanceID + "_AccessibleLink_" + noValue[1] +
            "\").focus();'>" + RightNow.Text.sprintf(RightNow.Interface.getMessage("PCT_S_LINKS_DEPTH_ANNOUNCED_MSG"), this.data.attrs.label_input) + 
             
            " <span id='rn_" + this.instanceID + "_IntroCurrentSelection'>" + RightNow.Text.sprintf(RightNow.Interface.getMessage("SELECTION_PCT_S_ACTIVATE_LINK_JUMP_MSG"), noValue[0]) + "</span></a></p>";
            //loop through each hier_item to figure out nesting structure
            var previousLevel = -1;
            for(var i in this._flatTreeViewData)
            {
                if(this._flatTreeViewData.hasOwnProperty(i))
                {
                    var item = this._flatTreeViewData[i];
                    //print down html
                    if(item.level > previousLevel)
                        htmlList += "<ol>";

                    //print up html
                    while(item.level < previousLevel)
                    {
                        htmlList += "</li></ol>";
                        previousLevel--;
                    }
                    //print across html
                    if(item.level === previousLevel)
                        htmlList += "</li>";
                    //print current node
                    htmlList += "<li>" + '<a href="javascript:void(0)" id="rn_' +  this.instanceID + '_AccessibleLink_' + item[1] + '" class="rn_AccessibleHierLink" hierList="' + item['hier_list'] + '">' + item[0] + '</a>';
                    previousLevel = item.level;
                }
            }
            //close list
            for(var i = previousLevel; i >= 0; --i)
                htmlList += "</li></ol>";
            
            htmlList += "<div id='rn_" + this.instanceID + "_AccessibleErrorLocation'></div>";
            this._accessibleView.innerHTML = htmlList;
            //set up click handlers
            var allNodes = YAHOO.util.Dom.getElementsByClassName("rn_AccessibleHierLink", "a", this._accessibleView);
            YAHOO.util.Event.addListener(allNodes, "click", this._accessibleLinkClick, null, this);
            this._displayAccessibleDialog();
        }
    },
    
    /**
    * Executed when a tree item is selected from the accessible view.
    * @param e Event DOM click event
    */
    _accessibleLinkClick: function(e)
    {
        //basically transfer this click to the visible control
        //find the node in this._tree. If it's not there, expand it's parents until it is there.
        //call click on that node.
        var element = YAHOO.util.Event.getTarget(e);
        var hierArray = element.getAttribute("hierList").split(",");
        this._expandAndCreateNodes(hierArray);
        return false;
    },

    /**
    * Shows / hides Panel containing TreeView widget
    * Shows when user clicks button and the Panel is hidden.
    * Hides when user selects a node or the Panel loses focus.
    * @param event Event The click event
    */
    _toggleProductCategoryPicker: function(event)
    {
        //build tree for the first time
        if(!this._tree)
            this._buildTree();
        //show panel
        if(this._panel.cfg.getProperty("visible") === false)
        {
            //Set the panel to line up with the button (by default it's left-aligned in the dom)
            if(!this._toggleProductCategoryPicker._buttonPos || this._toggleProductCategoryPicker._buttonPos !== this._panel.cfg.getProperty("x"))
            {
                this._toggleProductCategoryPicker._buttonPos = YAHOO.util.Dom.getX(this._displayField);
                this._panel.cfg.setProperty("x", this._toggleProductCategoryPicker._buttonPos);
            }
            this._panel.syncPosition();
            this._panel.show();
            //focus on either the previously selected node or the first node
            var currentNode = this._tree.getNodeByIndex(this._currentIndex);
            if(currentNode && currentNode.focus)
            {
                currentNode.focus();
            }
            else if(this._tree.getRoot().children[0] && this._tree.getRoot().children[0].focus)
            {
                this._tree.getRoot().children[0].focus();
            }

            //create event listener (once)
            this._toggleProductCategoryPicker._closeListener = this._toggleProductCategoryPicker._closeListener ||
            function(event)
            {
                if(this._panel.cfg.getProperty("visible"))
                {
                    var coordinates = YAHOO.util.Event.getXY(event);
                    //return if target was the toggle button (either clicking or enter key)
                    if((event.type === "click" && YAHOO.util.Event.getTarget(event).id === this._displayField.id) || coordinates[0] === 0 && coordinates[1] === 0)
                        return;

                    coordinates = new YAHOO.util.Point(coordinates[0], coordinates[1]);
                    var panelRegion = YAHOO.util.Dom.getRegion(this._outerTreeContainer),
                        buttonRegion = YAHOO.util.Dom.getRegion(this._displayField);
                    if(panelRegion && buttonRegion && (!panelRegion.contains(coordinates) && !buttonRegion.contains(coordinates)))
                    {
                        //if click was anywhere outside of button or panel region, hide the panel
                        if(this.data.attrs.show_confirm_button_in_dialog && this._tree.currentFocus)
                            this._currentIndex = this._tree.currentFocus.index;
                        this._displaySelectedNodesAndClose();
                        YAHOO.util.Event.removeListener(document, this._toggleProductCategoryPicker._closeListener);
                    }
                }
            };
            YAHOO.util.Event.addListener(document, "click", this._toggleProductCategoryPicker._closeListener, null, this);
        }
        //hide panel
        else
        {
            this._displaySelectedNodesAndClose();
            YAHOO.util.Event.removeListener(document, this._toggleProductCategoryPicker._closeListener);
        }
    },

    /**
     * Navigates up from the selected node, generating an array
     * consisting of the labels of ea. hierarchy level in order.
     * @return array Array of labels
     */
    _getSelectedNodesMessage: function()
    {
        //work back up the tree from the selected node
        this._currentIndex = this._currentIndex || 1;
        var hierValues = [],
              currentNode = this._tree.getNodeByIndex(this._currentIndex);
        while(currentNode && !currentNode.isRoot())
        {
            hierValues.push(currentNode.label);
            currentNode = currentNode.parent;
        }
        return hierValues.reverse();
    }, 

    /**
    * Displays the hierarchy of the currently selected node up to it's root node,
    * hides the panel, and focuses on the selection button (if directed).
    * @param focus Boolean Whether or not the button should be focused
    */
    _displaySelectedNodesAndClose: function(focus)
    {
        RightNow.Event.fire("evt_productCategoryFilterSelected", this._eo);
        this._panel.hide();
        //also close the dialog if it exists
        if(this._dialog && this._dialog.cfg.getProperty("visible"))
            this._dialog.hide();
        if(this._currentIndex <= this._noValueNodeIndex)
        {
            this._displayFieldVisibleText.innerHTML = this.data.attrs.label_nothing_selected;
            var description = document.getElementById("rn_" + this.instanceID + "_TreeDescription");
            if(description)
               description.innerHTML = this.data.attrs.label_nothing_selected;
        }
        else
        {
            var hierValues = this._getSelectedNodesMessage().join("<br/>"),
                field = this._displayFieldVisibleText;
            if(YAHOO.env.ua.webkit) {
                //webkit doesn't allow setting the innerHTML of the button during keypress event,
                //so set it one millisecond later...
                setTimeout(function(){field.innerHTML = hierValues;}, 1);
            }
            else {
                field.innerHTML = hierValues;
            }
            
            var description = document.getElementById("rn_" + this.instanceID + "_TreeDescription");
            if(description)
               description.innerHTML = this.data.attrs.label_screen_reader_selected + hierValues;
        }
        //don't focus if the accessible dialog is in use or was in use during this page load.
        //the acccessible view and the treeview shouldn't really be mixed
        if(focus && !this._dialog)
            try{this._displayField.focus();} catch(e){}
    },

    /**
    * Handler for when enter was pressed while focused on a node
    * Emulates mouse click
    * @param {Event} keyEvent The node's enterPressed event.
    */
    _enterPressed: function(keyEvent)
    {
        this._selectNode({node:keyEvent});
    },

    /**
    * Selected a node by clicking on its label
    * (as opposed to expanding it via the expand image).
    * @param clickEvent Event The node's click event.
    */
    _selectNode: function(clickEvent)
    {
        this._currentIndex = clickEvent.node.index;
        this._selected = true;
        //static variable
        this._selectNode._selectedWidget = this.data.info.w_id;
        if((clickEvent.node.expanded || this._noValueNodeIndex === clickEvent.node.index) && !this.data.js.linkingOn)
        {
            this._eo.data.level = clickEvent.node.depth + 1;
            //setup filter data for report's filter request
            if(this._eo.data.level !== this._eo.filters.data[0].length)
            {
                //filter's been reset or user skipped a level: make sure to always pass correct values
                this._eo.filters.data[0] = [];
                var currentNode = clickEvent.node;
                while(currentNode && !currentNode.isRoot())
                {
                    this._eo.filters.data[0][currentNode.depth] = currentNode.hierValue;
                    currentNode = currentNode.parent;
                }
            }
            else
            {
                this._eo.filters.data[0][this._eo.data.level - 1] = clickEvent.node.hierValue || this._eo.data.value;
                for(var i = this._eo.data.level; i < this._eo.filters.data[0].length; i++)
                    delete this._eo.filters.data[0][i];
            }
        }
        else
        {
            this._getSubLevelRequest(clickEvent.node);
            this._tree.collapseAll();
        }
        
        this._displaySelectedNodesAndClose(true);
        if(clickEvent.event)
            YAHOO.util.Event.preventDefault(clickEvent.event);

        if(this.data.attrs.search_on_select)
        {
            this._eo.filters.reportPage = this.data.attrs.report_page_url;
            RightNow.Event.fire("evt_searchRequest", this._eo);
        }
        return false;
    },

    /**
     * Event handler when a node is expanded.
     * Requests the next sub-level of items from the server.
     * @param expandingNode Event The node that's expanding
     */
    _getSubLevelRequest: function(expandingNode)
    {
        //only allow one node at-a-time to be expanded
        if(this._nodeBeingExpanded || (expandingNode.expanded && !this.data.js.linkingOn)) return;
        
        this._nodeBeingExpanded = true;
        this._eo.data.level = expandingNode.depth + 1;
        this._eo.data.label = expandingNode.label;
        this._eo.data.value = expandingNode.hierValue;
        
        //When the show_confirm_button_in_dialog attribute is set, we don't want to explicity change the users selection when they drill down
        //into an element. If we did that, the user wouldn't be able to use the cancel button correctly. We just want to set a
        //temporary value which we can use in the response event. If this attribute isn't set, keep the behavior the same as before.
        if(this.data.attrs.show_confirm_button_in_dialog)
            this._requestedIndex = expandingNode.index;
        else
            this._currentIndex = expandingNode.index;
        
        //static variable for different widget instances but the same data type
        this._getSubLevelRequest._origRequest = this._getSubLevelRequest._origRequest || [];
        this._getSubLevelRequest._origRequest[this._dataType] = expandingNode.hierValue;

        if(this._dataType === "products")
        {
            //Set namespace global for hier menu list linking display
            RightNow.UI.Form.currentProduct = this._eo.data.value;
        }
        if(this._eo.data.value < 1 && this._eo.data.linking_on)
        {
            //prod linking
            this._eo.data.reset = true;
            if(this._eo.data.value === 0 && this._dataType === "products")
            {
                //product was set back to all: fire event for categories to re-show all
                this._eo.data.reset = false;
                var eo = new RightNow.Event.EventObject();
                eo.data = {"name" : "c", "reset" : true};
                eo.filters.report_id = this.data.attrs.report_id;
                RightNow.Event.fire("evt_resetFilterRequest", eo);
                this._nodeBeingExpanded = false;
                return;
            }
            else
            {
                this._eo.data.value = 0;
            }
        }
        else
        {
            this._eo.data.reset = false;
        }

        if(this.data.js.link_map)
        {
            //pass link map (prod linking) to EventBus for first time
            this._eo.data.link_map = this.data.js.link_map;
            this.data.js.link_map = null;
        }
        //setup filter data for report's filter request
        if(this._eo.data.level !== this._eo.filters.data[0].length)
        {
            //filter's been reset or user skipped a level: make sure to always pass correct values
            this._eo.filters.data[0] = [];
            var currentNode = expandingNode;
            while(currentNode && !currentNode.isRoot())
            {
                this._eo.filters.data[0][currentNode.depth] = currentNode.hierValue;
                currentNode = currentNode.parent;
            }
        }
        else
        {
            this._eo.filters.data[0][this._eo.data.level - 1] = this._eo.data.value;
            for(var i = this._eo.data.level; i < this._eo.filters.data[0].length; i++)
                delete this._eo.filters.data[0][i];
        }
        RightNow.Event.fire("evt_menuFilterRequest", this._eo);
        // Remove link_map from this._eo so this widget does not misinform the Event Bus
        // or other widgets about the link_map on subsequent requests.
        if(this._eo.data.link_map)
            delete this._eo.data.link_map;
        this._nodeBeingExpanded = false;
    },


    /**
     * Event handler when report has been updated
     * @param type String Event name
     * @param args Object Event arguments
     */
    _onReportResponse: function(type, args)
    {
        if(RightNow.Event.isSameReportID(args, this.data.attrs.report_id)) {
            var data = RightNow.Event.getDataFromFiltersEventResponse(args, this.data.js.searchName, this.data.attrs.report_id);
            if(data[0] && data[0].length) {
                if(!this._tree)
                    this._buildTree();
                //remove empties
                if(typeof data[0] === "string")
                    data[0] = data[0].split(",");
                var finalData = RightNow.Lang.arrayFilter(data[0]);
                this._expandAndCreateNodes(finalData);
                this._eo.filters.data[0] = finalData;
                this._lastSearchValue = finalData.slice(0);
                if(this._eo.filters.data.reconstructData) {
                    this._eo.filters.data.level = this._eo.filters.data.reconstructData.level;
                    this._eo.filters.data.label = this._eo.filters.data.reconstructData.label;
                }
            }
            else {
                //always set back to empty array since search eventbus may have inadvertantly set it to null...
                this._eo.filters.data[0] = [];
                if(this._tree) {
                    //going from some selection back to no selection
                    this._currentIndex = this._noValueNodeIndex;
                    this._displaySelectedNodesAndClose();
                }
            }
        }
    },
    
    /**
    * Used to set the tree to a specific state; programatically expands nodes 
    * in order to build up the hierarchy tree to the specified array of IDs.
    * @param hierArray Array IDs denoting the specified prod/cat chain
    */
    _expandAndCreateNodes: function(hierArray)
    {
        var i = hierArray.length - 1,
            currentNode = null;
        //walk up the chain looking for the first available node
        while(!currentNode && i >= 0) {
            currentNode = this._tree.getNodeByProperty("hierValue", parseInt(hierArray[i]));
            i--;
        }
        //now currentNode should be something:
        //already selected? return
        if(this._currentIndex === currentNode.index)
        {
            //close the basic view if it is in use
            if(this._dialog && this._dialog.cfg.getProperty("visible"))
                this._dialog.hide();
            return;
        }
        //if we already have the one selected, then we can go ahead and select it.
        i++;
        if(this._noValueNodeIndex === currentNode.index || currentNode.hierValue == hierArray[hierArray.length - 1]) {
            this._selectNode({node: currentNode});
        }
        else {
            var onExpandComplete = function(expandingNode) {
                if(expandingNode.nextToExpand) {
                    var nextNode = this._tree.getNodeByProperty("hierValue", parseInt(expandingNode.nextToExpand));
                    if(nextNode) {
                        nextNode.nextToExpand = hierArray[++i];
                        nextNode.expand();
                    }
                }
                else if(i === hierArray.length) {
                    //we don't want to subscribe to this more than once
                    this._tree.unsubscribe("expandComplete", onExpandComplete, null);
                    expandingNode.expanded = false;
                    this._selectNode({node: expandingNode});
                }
                return true;
            };
            //walk back down to their selection from here expanding as you go
            this._tree.subscribe("expandComplete", onExpandComplete, null, this);
            currentNode.nextToExpand = hierArray[++i];
            currentNode.expand();
        }
    },

    /**
     * Event handler when returning from ajax data request
     * @param type String Event name
     * @param args Object Event arguments
     */
    _getSubLevelResponse: function(type, args)
    {
        var evtObj = args[0];

        //Check if this widget is supposed to update
        if((evtObj.data.data_type !== this._dataType) || (evtObj.filters.report_id !== this.data.attrs.report_id))
            return;

        // delete link_map if we have not already so that we don't send stale data
        if(this.data.js.link_map)
            delete this.data.js.link_map;

        var hierLevel = evtObj.data.level,
              hierData = evtObj.data.hier_data,
              redisplaySelectedNode = false,
              currentRoot = null;

        if(!this._tree)
            this._buildTree();
        
        if(!evtObj.data.reset_linked_category && this._getSubLevelRequest._origRequest && this._getSubLevelRequest._origRequest[this._dataType])
        {
            //get the node by its hierValue
            currentRoot = this._tree.getNodeByProperty("hierValue", this._getSubLevelRequest._origRequest[this._dataType]);
            if(currentRoot.index !== (this.data.attrs.show_confirm_button_in_dialog ? this._requestedIndex : this._currentIndex))
            {
                this._currentIndex = currentRoot.index;
                redisplaySelectedNode = true;
            }
        }
        //prod linking : data's being completely reset
        else if(evtObj.data.reset_linked_category)
        {
            //clear out the existing tree and add 'no value' node
            currentRoot = this._tree.getRoot();
            currentRoot.dynamicLoadComplete = false;
            this._tree.removeChildren(currentRoot);
            this._flatTreeViewData = null;
            var tempNode = new YAHOO.widget.MenuNode(RightNow.Interface.getMessage("NO_VAL_LBL"), currentRoot, false);
            tempNode.hierValue = 0;
            tempNode.href='javascript:void(0);';
            tempNode.isLeaf = true;
            this._noValueNodeIndex = this._currentIndex = this._requestedIndex = tempNode.index;
            //since the data's being reset, reset the button's label
            this._displayFieldVisibleText.innerHTML = this.data.attrs.label_nothing_selected;
            var description = document.getElementById("rn_" + this.instanceID + "_TreeDescription");
            if(description)
                description.innerHTML = this.data.attrs.label_nothing_selected;
        }

        //add the new nodes to the currently selected node
        if(hierLevel < 7 && currentRoot && !currentRoot.dynamicLoadComplete)
        {
            for(var i = 0, tempNode, hasChildrenIndex; i < hierData.length; i++)
            {
                hasChildrenIndex = hierData[i].length - 1;
                tempNode = new YAHOO.widget.MenuNode(hierData[i][1], currentRoot, false);
                tempNode.hierValue = hierData[i][0];
                tempNode.href = 'javascript:void(0);';
                if(!hierData[i][hasChildrenIndex] || hierLevel === 6)
                {
                    //if it doesn't have children then turn off the +/- icon
                    //and notify that the node is already loaded
                    tempNode.dynamicLoadComplete = true;
                    tempNode.iconMode = 1;
                }
            }
            currentRoot.loadComplete();
        }
        //leaf node was expanded : display and close
        if(hierData.length === 0 && !this._selected)
        {
            this._displaySelectedNodesAndClose();
        }
        //node was selected : its already selected and closed
        else if(this._selected)
        {
            this._selected = false;
        }
        else if(redisplaySelectedNode && this._selectNode._selectedWidget)
        {
            this._selectNode._selectedWidget = null;
            this._displaySelectedNodesAndClose();
        }
    },

    /**
    * Returns event object for search event if the report matches.
    * @param type String Event name
    * @param args Object Event object
    */
    _getFiltersRequest: function(type, args)
    {
        if(this._tree)
        {
            this._eo.filters.data.reconstructData = [];
            if(this._currentIndex !== this._noValueNodeIndex)
            {
                var currentNode = this._tree.getNodeByIndex(this._currentIndex || this._noValueNodeIndex),
                      filterType,
                      hierValues,
                      level;
                this._eo.data.level = currentNode.depth + 1;
                this._eo.data.label = currentNode.label;
                this._eo.data.value = currentNode.hierValue;
                
                // Keep a static store to preserve filter values with multiple widget instances.
                filterType = this._eo.filters.searchName || "";
                if(!this._getFiltersRequest.filterValues)
                    this._getFiltersRequest.filterValues = [];

                // Store any filter that is set if we don't have one already
                if(this._eo.filters.data[0].length)
                {
                    if(this._getFiltersRequest.filterValues[filterType])
                    {
                        var hasFilter = false,
                            eoFilters = this._eo.filters.data[0].toString(),
                            filters = this._getFiltersRequest.filterValues[filterType];

                        for(var i = 0; i < filters.length; i++)
                        {
                            if(filters[i].toString() === eoFilters)
                            {
                                hasFilter = true;
                                break;
                            }
                        }

                        if(!hasFilter)
                            this._getFiltersRequest.filterValues[filterType].push(this._eo.filters.data[0]);
                    }
                    else
                    {
                        this._getFiltersRequest.filterValues[filterType] = [this._eo.filters.data[0]];
                    }
                }

                // Get the last filter added
                if(this._getFiltersRequest.filterValues[filterType])
                    this._eo.filters.data[0] = this._getFiltersRequest.filterValues[filterType][this._getFiltersRequest.filterValues[filterType].length - 1];

                while(currentNode && !currentNode.isRoot())
                {
                    level = currentNode.depth + 1;
                    hierValues = this._eo.filters.data[0].slice(0, level).join(",");
                    this._eo.filters.data.reconstructData.push({"level" : level, "label" : currentNode.label, "hierList" : hierValues});
                    currentNode = currentNode.parent;
                }
                this._eo.filters.data.reconstructData.reverse();
            }
            else
            {
                this._eo.filters.data[0] = [];
                this._getFiltersRequest.filterValues = [];
                this._eo.data.value = 0;
            }
        }
        this._lastSearchValue = this._eo.filters.data[0].slice(0);
        RightNow.Event.fire("evt_searchFiltersResponse", this._eo);
        RightNow.Event.fire("ps_incidentSearchFilterApplied", this._eo);
    },

    /**
    * Responds to the filterReset event by setting the internal eventObject's data to blank.
    * @param type String Event name
    * @param args Object Event object
    */
    _onResetRequest: function(type, args)
    {
        if(this._tree && RightNow.Event.isSameReportID(args, this.data.attrs.report_id) && (args[0].data.name === this.data.js.searchName || args[0].data.name === "all"))
        {
            if(args[0].data.name === "all" && this._lastSearchValue)
            {
                //setting back to last search-on value
                this._eo.filters.data[0] = this._lastSearchValue;
                this._currentIndex = this._tree.getNodeByProperty("hierValue", this._lastSearchValue[this._lastSearchValue.length - 1]).index;
            }
            else
            {
                if(args[0].data.reset && this.data.js.linkingOn && this._dataType === "categories")
                {
                    // delete link_map if we have not already so that we don't send stale data
                    if(this.data.js.link_map)
                        delete this.data.js.link_map;
                    this._buildTree();
                }
                //setting to no value
                this._eo.filters.data[0] = [];
                this._currentIndex = this._noValueNodeIndex;
            }
            this._displaySelectedNodesAndClose();
        }
    },

    /**
     * Sets filters for searching on report
     */
    _initializeFilter: function()
    {
        this._eo.w_id = this.instanceID;
        this._eo.data.data_type = this._dataType = this.data.attrs.filter_type;
        this._eo.data.linking_on = this.data.js.linkingOn;
        this._eo.data.cache = [];
        this._eo.data.hm_type = this.data.js.hm_type;
        this._eo.data.linkingProduct = 0;
        this._eo.filters = {rnSearchType : "menufilter",
                            searchName : this.data.js.searchName,
                            report_id : this.data.attrs.report_id,
                            fltr_id : this.data.js.fltr_id,
                            oper_id : this.data.js.oper_id,
                            data : []};
        this._eo.filters.data[0] = (this.data.js.initial) ? this.data.js.initial : [];
        this._lastSearchValue = this._eo.filters.data[0].slice(0);
        //Set namespace global for hier menu list linking display
        if(this._dataType === "products")
        {
            RightNow.UI.currentProduct = this._eo.filters.data[0][this._eo.filters.data[0].length - 1];
            RightNow.UI.linkingOn = this.data.js.linkingOn;
            RightNow.UI.linkingFilter = this.data.attrs.filter_name;
        
            // automatically set the search 
            // to fix when contact product role is assigned
            var eo = new RightNow.Event.EventObject();
            eo.data = {"name" : "p", "reset" : true};
            eo.filters.report_id = this.data.attrs.report_id;
            RightNow.Event.fire("evt_resetFilterRequest", eo); 
        }

    }
};
