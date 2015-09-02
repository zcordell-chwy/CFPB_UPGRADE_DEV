RightNow.Widget.FileAttachmentUpload2DirectedRequests = function(data, instanceID)
{
    this.data = data;
    this.instanceID = instanceID;
    this._eo = new RightNow.Event.EventObject();
    this._eo.w_id = this.data.info.w_id;
    this._attachmentCount = this.data.js.attachmentCount || 0;
    this._attachments = [];
    this._parentForm = RightNow.UI.findParentForm("rn_" + this.instanceID);
    this._origEncType = "";
    this._attachmentList = null;
    this._statusMessage = document.getElementById("rn_" + this.instanceID + "_StatusMessage");
    this._inputField = document.getElementById("rn_" + this.instanceID + "_FileInput");
    if(!this._inputField) return;

    YAHOO.util.Event.addListener(this._inputField, "change", this._onFileAdded, null, this);
    YAHOO.util.Event.addListener(this._inputField, "keypress", this._onKeyPress, null, this);
    //pasting === bad
    YAHOO.util.Event.addListener(this._inputField, "paste", function(){return false;});

    if(this._parentForm)
    {
        this._origEncType = document.getElementById(this._parentForm).enctype;
        RightNow.Event.subscribe("evt_fileUploadResponse", this._fileUploadReturn, this);
        RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidateUpdate, this);
    }
    else
    {
        RightNow.UI.addDevelopmentHeaderError("FileAttachmentUpload2 must be placed within a form with a unique ID.");
    }

    this.data.attrs.max_attachments = (this.data.attrs.max_attachments === 0) ? Number.MAX_VALUE : this.data.attrs.max_attachments;
    if(this._attachmentCount === this.data.attrs.max_attachments)
        this._inputField.disabled = true;

    //click browse button
    RightNow.Event.subscribe("evt_browseClick", function() {
        this._inputField.click();
    }, this);
};

RightNow.Widget.FileAttachmentUpload2DirectedRequests.prototype = {
    /**
    * Event handler for when user performs a keypress in the file input field.
    * Overrides older browser behavior that allows users to type input, causing upload errors.
    * Allows tabbing and enter keypress to continue through.
    * @param event Event keypress event
    */
    _onKeyPress: function(event)
    {
        var Key = YAHOO.util.KeyListener.KEY,
            keyPressed = event.keyCode;
        //allow tabbing and enter keypress through
        if(keyPressed && keyPressed !== Key.ENTER  && keyPressed !== Key.TAB)
        {
            YAHOO.util.Event.stopEvent(event);
        }
        else if(YAHOO.env.ua.ie && keyPressed === Key.ENTER)
        {
            //IE submits the form when the user hits enter while focused on the
            //input field/button; make it behave like real browsers by invoking
            //the file upload dialog.
            YAHOO.util.Event.getTarget(event).click();
            YAHOO.util.Event.stopEvent(event);
        }
    },

    /**
     * Event handler for when value changes in file attachment input
     */
    _onFileAdded: function()
    {
        if(this._inputField.value === "" || this._uploading)
            return;
        
        // Validate file extension
        if (!this._validFileExtension(this._inputField.value)) {
            if (this._statusMessage) {
                YAHOO.util.Dom.removeClass(this._statusMessage, "rn_ScreenReaderOnly");
                this._statusMessage.innerHTML = this.data.attrs.label_invalid_file_error + ": " + this._inputField.value;
            }
            return;
        }

        this._uploading = true;
        YAHOO.util.Dom.removeClass("rn_" + this.instanceID + "_LoadingIcon", "rn_Hidden");
        if(this._statusMessage)
        {
            YAHOO.util.Dom.removeClass(this._statusMessage, "rn_ScreenReaderOnly");
            this._statusMessage.innerHTML = RightNow.Interface.getMessage("UPLOADING_ELLIPSIS_MSG");
        }

        //temporarily set the parent form's encode type for this request
        var parentForm = document.getElementById(this._parentForm);
        parentForm.enctype = parentForm.encoding = "multipart/form-data";
        YAHOO.util.Connect.setForm(parentForm, true);
        RightNow.Event.fire("evt_fileUploadRequest", this._eo);
    },
    
    /**
     * Ensure that the file extension is allowed to be uploaded
     */
    _validFileExtension: function(filename)
    {
        // Get file extension
        var parts = filename.split('.');
        if (parts.length <= 1) {
            return true;
        }
        var extension = parts.pop();
        
        // Get restricted extensions
        var a = this.data.attrs.restricted_file_extensions.split(',');
        var len = a.length;
        var restrictedExtensions = {};
        for (var i = 0; i < len; ++i) {
            restrictedExtensions[a[i]] = '';
        }
        
        // Conditional check
        if ( extension in restrictedExtensions ) {
            return false;
        }
        return true;
    },

    /**
     * Event handler for when server responds with file attachment information
     * @param type String Event name
     * @param response Object Event arguments
     */
    _fileUploadReturn: function(type, response)
    {
        YAHOO.util.Dom.addClass("rn_" + this.instanceID + "_LoadingIcon", "rn_Hidden");
        if(this._statusMessage)
        {
            YAHOO.util.Dom.addClass(this._statusMessage, "rn_ScreenReaderOnly");
            this._statusMessage.innerHTML = RightNow.Interface.getMessage("FILE_UPLOAD_COMPLETE_LBL");
            this._statusMessage.tabIndex=0;
            RightNow.UI.updateVirtualBuffer();
            this._statusMessage.focus();
        }

        //standards-based browsers allow JS to set value to blank
        this._inputField.value = "";
        //We'll deal with you yet, donkey browsers!
        if(YAHOO.env.ua.ie)
        {
            //And before you say anything: yes this is stupid; yes this is IE.
            var inputField = this._inputField.cloneNode(false);
            this._inputField.parentNode.replaceChild(inputField, this._inputField);
            this._inputField = document.getElementById(inputField.id);
            //IE9 apparently changed so that we need to resubscribe, presumably because now
            //when cloneNode is called, all the events have been unsubscribed
            if(YAHOO.env.ua.ie > 8)
                YAHOO.util.Event.addListener(this._inputField, "change", this._onFileAdded, null, this);
        }

        //reset parent form's encode type back to it's original
        var parentForm = document.getElementById(this._parentForm);
        parentForm.enctype = parentForm.encoding = this._origEncType;

        var attachmentInfo = response[0],
            displayLimitMessage = false;
        this._uploading = false;
        //Check for errors produced by php
        if(!attachmentInfo)
        {
            RightNow.UI.Dialog.messageDialog(RightNow.Interface.getMessage("ERROR_REQUEST_ACTION_COMPLETED_MSG"), {icon: "WARN"});
            return;
        }
        //size error
        else if(attachmentInfo.error === 2)
        {
            RightNow.UI.Dialog.messageDialog(this.data.attrs.label_generic_error, {icon: "WARN"});
            return;
        }
        //upload error
        else if(attachmentInfo.error === 4 || attachmentInfo.error === 88)
        {
            RightNow.UI.Dialog.messageDialog(RightNow.Interface.getMessage("FILE_PATH_FOUND_MSG"), {icon: "WARN"});
            return;
        }
        //Empty file uploaded
        else if(attachmentInfo.error === 10)
        {
            RightNow.UI.Dialog.messageDialog(RightNow.Interface.getMessage("FILE_ATT_UPLOAD_EMPTY_PLS_ENSURE_MSG"));
            return;
        }
        //File name too long
        else if(attachmentInfo.errorMessage)
        {
            RightNow.UI.Dialog.messageDialog(attachmentInfo.errorMessage, {icon: "WARN"});
            return;
        }
        //check if the max upload threshold has been hit
        this._attachmentCount++;
        if(this._attachmentCount === this.data.attrs.max_attachments)
        {
            this._inputField.disabled = true;
            displayLimitMessage = true;
        }
        else if(this._attachmentCount > this.data.attrs.max_attachments)
        {
            this._inputField.disabled = true;
            return;
        }
        attachmentInfo.name = attachmentInfo.name.replace("&amp;", "&");
        var nextAttachment = {"name" : attachmentInfo.name, "tmp_name" : attachmentInfo.tmp_name,
            "type" : attachmentInfo.type, "size" : attachmentInfo.size};
        this._attachments.push(nextAttachment);

        //Convert byte size to kilobyte size and round to 2 decimal places
        attachmentInfo.size /= 1024;
        attachmentInfo.size = Math.round(attachmentInfo.size * 100) / 100;

        //create/update UI list to display attachments
        if(!this._attachmentList)
            this._attachmentList = YAHOO.util.Dom.insertAfter(document.createElement("ul"), this._statusMessage);

        this._attachmentList.innerHTML += "<li>" + attachmentInfo.name + "&nbsp;(" + attachmentInfo.size + "K)&nbsp;<a href='' onclick='RightNow.Widget.getWidgetInstance(\"" + this.instanceID + "\").removeClick(this, " + (this._attachments.length - 1) +  ");return false;'/>" + this.data.attrs.label_remove + " <span class='rn_ScreenReaderOnly'>" + attachmentInfo.name + "</span></a></li>";
        if(displayLimitMessage)
            this._attachmentList.innerHTML += "<li>" + this.data.attrs.label_max_attachment_limit + "</li>";

        document.getElementById('file_input_overlay').value='';
    },

    /**
     * Event handler for when file attachment item is removed
     * @param item Object DOM element of item label
     * @param index Object Index of file attachment to remove
     */
    removeClick: function(item, index)
    {
        this._attachments[index] = null;
        item.parentNode.parentNode.removeChild(item.parentNode);
        if(this._statusMessage)
        {
            this._statusMessage.innerHTML = RightNow.Interface.getMessage("FILE_DELETED_LBL");
            YAHOO.util.Dom.addClass(this._statusMessage, "rn_ScreenReaderOnly");
            this._statusMessage.tabIndex=0;
            RightNow.UI.updateVirtualBuffer();
            this._statusMessage.focus();
        }

        this._attachmentCount--;
        this._inputField.disabled = false;

        if(this._attachmentCount === this.data.attrs.max_attachments - 1)
            this._attachmentList.removeChild(this._attachmentList.lastChild);
    },

    /**
     * Event handler when submitting form. File information for all attachments is sent
     * @param type String Event name
     * @param args Object Event arguments
     */
    _onValidateUpdate: function(type, args)
    {
        this._eo.data = {"name" : "fattach", "custom" : "false", "table" : this.data.attrs.table, "required" : false,
            "form" : this._parentForm};
        if (RightNow.UI.Form.form === this._parentForm)
        {
            var results = null,
                  fattachIndex = 0;
            if(this._attachmentCount > 0)
            {
                //build up array of file attachment data
                results = {};
                for(var i = 0, fileAttachment; i < this._attachments.length; i++)
                {
                    if(this._attachments[i] != null)
                    {
                        fileAttachment = {"localfname" : this._attachments[i].tmp_name,
                        "action" : RightNow.Interface.Constants.ACTION_ADD,
                        "size" : this._attachments[i].size,
                        "private" : 0,
                        "userfname" : this._attachments[i].name};
                        if(this._attachments[i].type)
                            fileAttachment.content_type = this._attachments[i].type;
                        else
                            fileAttachment.content_type = "application/octet-stream";
                        results["fattach_item" + fattachIndex] = fileAttachment;
                        fattachIndex++;
                    }
                }
            }
            this._eo.data.value = results;
            this._eo.w_id = this.data.info.w_id;

            RightNow.Event.fire("evt_formFieldValidateResponse", this._eo);
        }
        else
        {
            RightNow.Event.fire("evt_formFieldValidateResponse", this._eo);
        }
        RightNow.Event.fire("evt_formFieldCountRequest");
    }
};
