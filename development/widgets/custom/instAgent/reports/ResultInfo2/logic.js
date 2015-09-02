RightNow.Widget.ResultInfo2 = function(data, instanceID) {
    this.data = data;
    this.instanceID = instanceID;
    RightNow.Event.subscribe("evt_reportResponse", this._onReportChanged, this);
};
RightNow.Widget.ResultInfo2.prototype = {
    /**
     * Event handler received when report data is changed
     *
     * @param type String Event type
     * @param args Object Arguments passed with event
     */
    _onReportChanged: function(type, args)
    {
        if(args[0].data && args[0].data.report_id == this.data.attrs.report_id)
        {
            var newData = args[0].data,
                resultQuery = "",
                parameterList = "";

            if(this.data.attrs.add_params_to_url !== "" && args[0].filters && args[0].filters.allFilters && args[0].filters.allFilters.format)
            {
                var allFilters = RightNow.Lang.cloneObject(args[0].filters.allFilters);
                allFilters.format.parmList = this.data.attrs.add_params_to_url;
                parameterList = RightNow.Url.buildUrlLinkString(allFilters);
            }

            //construct search results message for the searched-on terms
            if(this.data.attrs.display_results && newData.search_term)
            {
                var stopWords = newData.stopword,
                      noDictWords = newData.not_dict,
                      searchTerms = newData.search_term.split(" "),
                      displayedNoResultsMsg = false;

                for(var i = 0, word, strippedWord; i < searchTerms.length; i++)
                {
                    word = searchTerms[i];
                    strippedWord = word.replace(/\W/, "");
                    if(stopWords && strippedWord && stopWords.indexOf(strippedWord) !== -1)
                        word = "<strike title='" + this.data.attrs.label_common + "'>" + word + "</strike>";
                    else if(noDictWords && strippedWord && noDictWords.indexOf(strippedWord) !== -1)
                        word = "<strike title='" + this.data.attrs.label_dictionary + "'>" + word + "</strike>";
                    else
                        word = "<a href='" + RightNow.Url.addParameter(this.data.js.linkUrl + encodeURIComponent(word.replace(/\&amp;/g, "&")) + parameterList + "/search/1", "session", RightNow.Url.getSession()) + "'>" + word + "</a>";
                    resultQuery += word + " ";
                }
                resultQuery = YAHOO.lang.trim(resultQuery);
            }

            // suggested
            var suggestedDiv = document.getElementById("rn_" + this.instanceID + "_Suggestion");
            if(suggestedDiv)
            {
                if(newData.ss_data)
                {
                    var links = this.data.attrs.label_suggestion + " ";
                    for(var i = 0; i < newData.ss_data.length; i++)
                        links += '<a href="' + this.data.js.linkUrl + newData.ss_data[i] + parameterList + '">' + newData.ss_data[i] + '</a>&nbsp;';
                    suggestedDiv.innerHTML =  links;
                    YAHOO.util.Dom.removeClass(suggestedDiv, "rn_Hidden");
                }
                else
                {
                    YAHOO.util.Dom.addClass(suggestedDiv, "rn_Hidden");
                }
            }

            // spelling
            var spellingDiv = document.getElementById("rn_" + this.instanceID + "_Spell");
            if(spellingDiv)
            {
                if(newData.spelling)
                {
                    spellingDiv.innerHTML = this.data.attrs.label_spell + ' <a href="' + this.data.js.linkUrl + newData.spelling + parameterList + '">' + newData.spelling + ' </a>';
                    YAHOO.util.Dom.removeClass(spellingDiv, "rn_Hidden");
                }
                else
                {
                    YAHOO.util.Dom.addClass(spellingDiv, "rn_Hidden");
                }
            }

            // no results
            var noResultsDiv = document.getElementById("rn_" + this.instanceID + "_NoResults");
            if(noResultsDiv)
            {
                if(newData.total_num === 0 && resultQuery && !newData.topics)
                {
                    noResultsDiv.innerHTML = this.data.attrs.label_no_results + "<br/><br/>" + this.data.attrs.label_no_results_suggestions;
                    YAHOO.util.Dom.removeClass(noResultsDiv, "rn_Hidden");
                    displayedNoResultsMsg = true;
                }
                else
                {
                    YAHOO.util.Dom.addClass(noResultsDiv, "rn_Hidden");
                }
            }

            // search results
            var resultsDiv = document.getElementById("rn_" + this.instanceID + "_Results");
            if(resultsDiv)
            {
                if(!displayedNoResultsMsg && !newData.truncated)
                {
                    if(resultQuery.length > 0)
                        resultsDiv.innerHTML = RightNow.Text.sprintf(this.data.attrs.label_results_search_query, newData.start_num, newData.end_num, newData.total_num, resultQuery);
                    else
                        resultsDiv.innerHTML = RightNow.Text.sprintf(this.data.attrs.label_results, newData.start_num, newData.end_num, newData.total_num);
                    YAHOO.util.Dom.removeClass(resultsDiv, "rn_Hidden");
                }
                else
                {
                    YAHOO.util.Dom.addClass(resultsDiv, "rn_Hidden");
                }
            }
        }
    }
};

