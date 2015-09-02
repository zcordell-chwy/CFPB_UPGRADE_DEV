RightNow.Widget.CompanyPublicComment = function(data, instanceID)
{
    //Data object contains all widget attributes, values, etc.
    this.data = data;
    this.instanceID = instanceID;

    //Perform any initial javascript logic here
    this.initialize();

};

RightNow.Widget.CompanyPublicComment.prototype = {
    //Define any widget functions here
    
    initialize : function()
    {
        var elements = YAHOO.util.Dom.getElementsByClassName('commentRadios');
        for (var i = elements.length - 1; i >= 0; i--) {
            YAHOO.util.Event.addListener(elements[i], "click", this.radioClick, elements[i], this);
        };

    },

    radioClick : function(type, args)
    {
        // grab the text from the radio button label and replace the textbox text with this value
        var radioLabel = YAHOO.util.Dom.getNextSibling(args);
        var commentTextArea = YAHOO.util.Dom.get('div_company_comment_form_textbox').getElementsByTagName("textarea")[0];

        // need to do this because Firefox doesn't handle innerText
        if (radioLabel.innerText) {
            commentTextArea.innerText = radioLabel.innerText;
        } else {
            commentTextArea.textContent = radioLabel.textContent;
        }
        
        // use args.value (which is radio button value) to index into category array, then find matching menu item
        this.selectCategory(this.data.js.radioLabelCategories[args.value]);
    },
    
    selectCategory : function(searchValue) {
        var categoryMenu = YAHOO.util.Dom.get('div_company_comment_category_menu').getElementsByTagName('select')[0];
        
        categoryMenu.selectedIndex = -1; // initialize to no selection
        for(var i = 0, j = categoryMenu.options.length; i < j; ++i) {
            var menuText = categoryMenu.options[i].innerText || categoryMenu.options[i].textContent; // need to do this because Firefox doesn't handle innerText
            if(menuText === searchValue) {
               categoryMenu.selectedIndex = i;
               break;
            }
        }
    }
};
