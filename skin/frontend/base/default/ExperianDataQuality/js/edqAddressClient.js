ExperianDataQuality.DataQuality.Address = ExperianDataQuality.DataQuality.Address || {};

ExperianDataQuality.DataQuality.Address.Configuration = function Configuration(inputMappings, outputMappings) {
    var dq = ExperianDataQuality.DataQuality;

    this._stage = dq.WorkFlowStages.Input;

    dq.Configuration.apply(this, [inputMappings, outputMappings, dq.ConfigurationType.Address]);
};
ExperianDataQuality.system.inherit(ExperianDataQuality.DataQuality.Address.Configuration, ExperianDataQuality.DataQuality.Configuration);
ExperianDataQuality.DataQuality.Address.Configuration.SearchServiceUrl = '';
ExperianDataQuality.DataQuality.Address.Configuration.RefineServiceUrl = '';
ExperianDataQuality.DataQuality.Address.Configuration.GetAddressServiceUrl = '';
ExperianDataQuality.DataQuality.Address.Configuration.IsManualEntryAllowed = true;

ExperianDataQuality.DataQuality.Address.Client = function Client(configurations) {
    var dq = ExperianDataQuality.DataQuality;

    dq.Client.apply(this, [configurations]);
};
ExperianDataQuality.system.inherit(ExperianDataQuality.DataQuality.Address.Client, ExperianDataQuality.DataQuality.Client);

ExperianDataQuality.DataQuality.Address.Client.prototype._initializeForConfiguration = function _initializeForConfiguration(configuration) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var client = this;

    var dataSetElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.DataSet);
    if (sys.isNull(dataSetElement)) {
        return;
    }

    var previousOnChange = dataSetElement.onchange;
    dataSetElement.onchange = function (event, value) {
        dq.ManualEntryState = dq.Deactive;
        if (sys.isFunction(previousOnChange)) {
            previousOnChange.apply(this, [event]);
        };
        if(sys.containsInArray(dataSetElement.value, dq.Address.LicensedDataSets) && client._isExistingAddressEdited(configuration) === false) {
            client._showVerifiedAddressPromptSet(configuration, false, dataSetElement.value);
        } 
        else {
           client._showPromptSetFor(configuration, this.value);
        }        
    };
    dataSetElement.onchange();
    
    var chagenAddressElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.ChangeAddressButton);
    chagenAddressElement.onclick = function () { dq.ManualEntryState = dq.Deactive; client._showPromptSetFor(configuration, dataSetElement.value); };
    
    var findAddressElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.FindAddressButton);
    findAddressElement.onclick = function () { client._findAddress(configuration); };

    var manualEntryElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.ManualEntry);
    if(!sys.isNull(manualEntryElement)) {
        manualEntryElement.onclick = function () { client._showPromptSetFor(configuration); dq.ManualEntryState = dq.Active; };
    }
    
    var knownPostCodeElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.KnownPostCode);
    knownPostCodeElement.onclick = function () { client._showPromptSetFor(configuration, dataSetElement.value); };

    var unknownPostCodeElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.UnknownPostCode);
    unknownPostCodeElement.onclick = function () { client._showUnknownPostcodePromptSet(configuration, dataSetElement.value); };

    var selectSuggestionButtonElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.SelectButton);
    selectSuggestionButtonElement.onclick = function () { client._selectAddress(dataSetElement.value, configuration); };

    var suggestionsElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.Suggestions);
    suggestionsElement.ondblclick = function (event) { client._selectAddress(dataSetElement.value, configuration); };
    
    var refineElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.RefineInput);

    var tryAgainElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.TryAgain);
    tryAgainElement.onclick = function () {
        switch (configuration.stage) { 
            case dq.WorkFlowStages.Suggestions:
                client._restoreInputValues(configuration._inputMappings);
                client._selectAddress(dataSetElement.value, configuration);
                break;
            case dq.WorkFlowStages.Refining:
                client._restoreInputValues(configuration._inputMappings);
                client._refineAddress(dataSetElement.value, refineElement.value, configuration);
                break;
            default:
                client._restoreInputValues(configuration._inputMappings);
                client._findAddress(configuration);
                break;
        }
    };

    var backButtonElements = configuration._getHtmlElementsFromInputMappingByEdqFieldName(dq.BackButton);
    
    var backButtonElementOnClick = function () {
        switch (configuration.stage) {
            case dq.WorkFlowStages.Verified:
                if (suggestionsElement.options.length <= 1) {
                    client._showPromptSetFor(configuration, dataSetElement.value);
                    client._restoreInputValues(configuration._inputMappings);
                    break;
                }
                client._showSuggestionsProptset(configuration);
                break;
            case dq.WorkFlowStages.Refining:
                if (suggestionsElement.options.length <= 1) {
                    client._showPromptSetFor(configuration, dataSetElement.value);
                    client._restoreInputValues(configuration._inputMappings);
                } else {
                    client._showSuggestionsProptset(configuration);
                }
                break;
            case dq.WorkFlowStages.Suggestions:
                dq.Address.Client.clearSuggestionList(suggestionsElement);
                client._showPromptSetFor(configuration, dataSetElement.value);
                client._restoreInputValues(configuration._inputMappings);
                break;
            default:
                client._showPromptSetFor(configuration, dataSetElement.value);
                client._restoreInputValues(configuration._inputMappings);
                break;    
        }
        client._handleErrorMessage(configuration, dq.ErrorMessage);
    };
    
    for(var i = 0, max = backButtonElements.length; i < max; i++) {
        var backButtonHtmlElement = sys.getElementById(backButtonElements[i]);
        
        backButtonHtmlElement.onclick = backButtonElementOnClick;
    }
     
    var nextButtonElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.NextButton);
    nextButtonElement.onclick = function () { client._refineAddress(dataSetElement.value, refineElement.value, configuration); };
};

ExperianDataQuality.DataQuality.Address.Client.prototype._isExistingAddressEdited = function _isExistingAddressEdited(configuration) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;
    
    var areAllFieldsEmpty = true;

    for (var i = 0, max = configuration._inputMappings.length; i < max; i++) {
        var inputMapping = configuration._inputMappings[i];

        var value = dq.Client.collectValuesFromInputMapping(inputMapping);
       
        switch (inputMapping.edqFieldName) {
            case dq.Line1:
            case dq.Line2:
            case dq.Line3:
            case dq.Line4:
            case dq.Line5: 
                if (!sys.isNullOrEmpty(value)) {                    
                    areAllFieldsEmpty = false;
                }
                dq.Snapshot[inputMapping.edqFieldName] = value;
                break;
            default:
                break;
        }
    }

    return areAllFieldsEmpty;
};

ExperianDataQuality.DataQuality.Address.Client.prototype._showPromptSetFor = function _showPromptSetFor(configuration, dataSet) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;
    
    if(dq.ManualEntryState === dq.Active) {
        return;
    }
        
    var visibleElements = []; 
    if (sys.containsInArray(dataSet, dq.Address.LicensedDataSets)) {
        switch(dataSet) {
            case 'GB':
                visibleElements = [dq.DataSet, dq.FindAddressButton, dq.BuildingNumber, dq.Postcode, dq.UnknownPostCode];
                break;
            case 'DE':
                visibleElements = [dq.DataSet, dq.FindAddressButton, dq.BuildingNumber, dq.Postcode, dq.UnknownPostCode, dq.Street];
                break;
            case 'IE':
                visibleElements = [dq.DataSet, dq.FindAddressButton, dq.BuildingNumber, dq.Town, dq.Street];
                break; 
        } 
        if( dq.Address.Configuration.IsManualEntryAllowed) { visibleElements.push(dq.ManualEntry); }
    }
    else {
        visibleElements = [dq.Line1, dq.Line2, dq.Line3, dq.Line4, dq.Line5, dq.DataSet];
    }

    this._hideAllInputMappingElementsExcept(configuration._inputMappings, visibleElements);
    this._handleErrorMessage(configuration, dq.ErrorMessage);
    configuration.stage = dq.WorkFlowStages.Input;
};

ExperianDataQuality.DataQuality.Address.Client.prototype._showUnknownPostcodePromptSet = function _showUnknownPostcodePromptSet(configuration, dataSet) {
    var dq = ExperianDataQuality.DataQuality;
    
    if(dq.ManualEntryState === dq.Active) {
        return;
    }
    
    var visibleElements; 
    if(dataSet === 'GB' || dataSet === 'DE') {
        visibleElements = [dq.DataSet, dq.FindAddressButton, dq.BuildingNumber, dq.ManualEntry, dq.Town, dq.Street, dq.KnownPostCode];
    }        
    this._hideAllInputMappingElementsExcept(configuration._inputMappings, visibleElements);
    configuration.stage = dq.WorkFlowStages.Input;
};

ExperianDataQuality.DataQuality.Address.Client.prototype._showSuggestionsProptset = function _showSuggestionsProptset(configuration) {
    var dq = ExperianDataQuality.DataQuality;
    
    if(dq.ManualEntryState === dq.Active) {
        return;
    }

    this._hideAllInputMappingElementsExcept(configuration._inputMappings, [dq.BackButton, dq.SelectButton, dq.Suggestions, dq.ManualEntry]);
    configuration.stage = dq.WorkFlowStages.Suggestions;
};

ExperianDataQuality.DataQuality.Address.Client.prototype._showRefineInputPromptSet = function _showRefineInputPromptSet(configuration) {
    var dq = ExperianDataQuality.DataQuality;
    
    if(dq.ManualEntryState === dq.Active) {
        return;
    }
    
    this._hideAllInputMappingElementsExcept(configuration._inputMappings, [dq.RefineInput, dq.BackButton, dq.NextButton, dq.ManualEntry]);
    configuration.stage = dq.WorkFlowStages.Refining;
};

ExperianDataQuality.DataQuality.Address.Client.prototype._showVerifiedAddressPromptSet = function _showVerifiedAddressPromptSet(configuration, isVerified, dataSet) {
    var dq = ExperianDataQuality.DataQuality;
    
    if(dq.ManualEntryState === dq.Active) {
        return;
    }
    
    var vasableElements = [dq.Line1, dq.Line2, dq.Line3, dq.Line4, dq.Line5, dq.DataSet];
    
    if(isVerified) {
        configuration.stage = dq.WorkFlowStages.Verified;
        vasableElements.push(dq.BackButton);
    } else {
        vasableElements.push(dq.ChangeAddressButton);
    }
    
    this._hideAllInputMappingElementsExcept(configuration._inputMappings, vasableElements);
};

ExperianDataQuality.DataQuality.Address.Client.prototype._findAddress = function _findAddress(configuration) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality; 

    var parameters = {};
    var areAllFieldsEmpty = true;

    for (var i = 0, max = configuration._inputMappings.length; i < max; i++) {
        var inputMapping = configuration._inputMappings[i];
       
        var value = dq.Client.collectValuesFromInputMapping(inputMapping);
        //TBD
        switch (inputMapping.edqFieldName) {            
            case dq.DataSet:
            case dq.Street:
            case dq.BuildingNumber:
            case dq.Town:
            case dq.Postcode:
                if (inputMapping.edqFieldName !== dq.DataSet && !sys.isNullOrEmpty(value)) {
                    areAllFieldsEmpty = false;
                }
                parameters[inputMapping.edqFieldName] = value;
                dq.Snapshot[inputMapping.edqFieldName] = value;
                break;
            default:
                break;
        }
    }

    if (areAllFieldsEmpty) {
        this._showErrorMessage(ExperianDataQuality.DataQuality.Address.PleaseEnterExactDetailsMessage, configuration);
        return;
    }
     
    this._executeFindAddressRequest(configuration, parameters, dq.Address.Configuration.SearchServiceUrl);
};

ExperianDataQuality.DataQuality.Address.Client.prototype._refineAddress = function _refineAddress(dataSet, refineInput, configuration) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    if (sys.isNullOrEmpty(refineInput)) {
        this._showErrorMessage(ExperianDataQuality.DataQuality.Address.PleaseEnterExactDetailsMessage, configuration);
        return;
    }

    var suggestionsHtmlElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.Suggestions);
    if (sys.isNull(suggestionsHtmlElement) || sys.isNull(suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex])) {
        this._showErrorMessage(ExperianDataQuality.DataQuality.Address.PleaseEnterExactDetailsMessage, configuration);
        return;
    }

    var partialText = suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex].innerHTML;
    var unresolvableRange = suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex].attributes['range'].value;

    if (!this._validateRefinementInput(refineInput, partialText, unresolvableRange)) {
        this._showErrorMessage(ExperianDataQuality.DataQuality.Address.AddressOutsideOfRanageMessage, configuration);
        return;
    }

    var parameters = {};
    parameters[dq.Moniker] = suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex].value;
    parameters[dq.RefineInput] = refineInput;
    parameters[dq.DataSet] = dataSet;
    
    dq.Snapshot[dq.RefineInput] = refineInput;
    
    this._executeFindAddressRequest(configuration, parameters, dq.Address.Configuration.RefineServiceUrl);
};

ExperianDataQuality.DataQuality.Address.Client.prototype._validateRefinementInput = function _validateRefinementInput(refineInput, partialAddress, unresolvableRange) {
    var dq = ExperianDataQuality.DataQuality;
    var sys = ExperianDataQuality.system;
    
    if (refineInput.length > 40) { return false; }
    
    if (!sys.toBoolean(unresolvableRange)) { return true; }
    
    var range = partialAddress.match(/[0-9a-zA-Z]+\s[...]+\s[0-9a-zA-Z]+/gim)[0].split('...');
    if (range.length > 2) {
        return true;
    }
    
    var startRange = isNaN(parseInt(range[0].trim())) ? range[0].trim() : parseInt(range[0].trim());
    var endRange = isNaN(parseInt(range[1].trim())) ? range[1].trim() : parseInt(range[1].trim());
    var castedInput =  isNaN(parseInt(refineInput.trim())) ? refineInput.trim() : parseInt(refineInput.trim());

    var isValid = true;
    if (castedInput < startRange || castedInput > endRange) {
        isValid = false;
    } else {
        var criteria = partialAddress.substr(partialAddress.indexOf('['), partialAddress.indexOf(']'));

        if (sys.isNullOrEmpty(criteria) && isNaN(castedInput)) {
            isValid = false;
        } else {
            if (sys.beginsWith('[even]', criteria) || sys.endsWith('[even]', criteria)) { 
                isValid = (!isNaN(castedInput % 2) && castedInput % 2 === 0);
            }
            if (sys.beginsWith('[odd]', criteria) || sys.endsWith('[odd]', criteria)) {
                isValid = (!isNaN(castedInput % 2) && castedInput % 2 !== 0);
            }
        }
    }

    return isValid;
};

ExperianDataQuality.DataQuality.Address.Client.prototype._executeFindAddressRequest = function _executeFindAddressRequest(configuration, parameters, url) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;
    var addressClient = dq.Address.Client;

    var client = this;

    client._handleLoader(configuration, true);
    this._executeXMLHttpRequest(url, parameters, function (response, errorMessage, xhr) {
        client._handleLoader(configuration, false);
        
        if (!sys.isNullOrEmpty(errorMessage)) {
            client._promptSetForErrorMessage(errorMessage, configuration);
            return;
        }
        
        if(!sys.isNull(response.AddressLineDictionary)) {
            client.setValues(response.AddressLineDictionary, configuration);
            client._showVerifiedAddressPromptSet(configuration, true, parameters[dq.DataSet]);
            return;
        }

        var suggestionsHtmlElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.Suggestions);
        if (response.Total < 2) {
            var details = response.PickListEntries[0];
            
            if (details && details.PickList === 'No matches' || details.PickList === 'Search cancelled (too many matches)') {
                details.PickList = details.PickList === 'No matches' ? ExperianDataQuality.DataQuality.Address.NoMatchesMessage : ExperianDataQuality.DataQuality.Address.ToManyMatchesMessage;
                client._showErrorMessage(details.PickList, configuration);
                return;
            } 
            
            if(details.FullAddress) { 
                client._getAddress(parameters[dq.DataSet], details.Moniker, configuration);
                return;
            } 
            
            addressClient.populateSuggestionList(suggestionsHtmlElement, response.PickListEntries, response.Prompt);
            suggestionsHtmlElement.options[0].selected = true;
            client._selectAddress(parameters[dq.DataSet], configuration);
        } else {
            var suggestionsHtmlElementLabel = configuration._getHtmlElementLabelFromInputMappingByEdqFieldName(dq.Suggestions);
            suggestionsHtmlElementLabel.innerHTML = response.Prompt === 'Enter selection' ? ExperianDataQuality.DataQuality.Address.EnterSelection : 'Enter selection';

            addressClient.populateSuggestionList(suggestionsHtmlElement, response.PickListEntries, response.Prompt);
            client._showSuggestionsProptset(configuration);
        }       
    });
};

ExperianDataQuality.DataQuality.Address.Client.populateSuggestionList = function populateSuggestionList(suggestionsHtmlElement, options, prompt) {
    var dq = ExperianDataQuality.DataQuality;

    suggestionsHtmlElement.innerHTML = '';
    for (var i = 0; i < options.length; i++) {
        var element = document.createElement(dq.HtmlTags.Option);
        element.innerHTML = options[i].PickList + ' ' + options[i].Postcode;
        element.textContent = options[i].PickList + ' ' + options[i].Postcode;
        element.value = options[i].Moniker;
        element.setAttribute('range', options[i].UnresolvableRange);
        element.setAttribute('full', options[i].FullAddress);
        element.setAttribute('prompt', prompt);
        suggestionsHtmlElement.appendChild(element);
    }
};

ExperianDataQuality.DataQuality.Address.Client.clearSuggestionList = function clearSuggestionList(suggestionsHtmlElement) {
    if (suggestionsHtmlElement === null) { return; }
    if (suggestionsHtmlElement.options === null) { return; }
    while (suggestionsHtmlElement.options.length > 0) {
            suggestionsHtmlElement.remove(0);
    }
    suggestionsHtmlElement.multiple = true;
};

ExperianDataQuality.DataQuality.Address.Client.prototype._selectAddress = function _selectAddress(dataSet, configuration) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;
    
    var suggestionsHtmlElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(dq.Suggestions);

    if (sys.isNull(suggestionsHtmlElement) || suggestionsHtmlElement.selectedIndex === -1) {
        this._showErrorMessage(ExperianDataQuality.DataQuality.Address.PleaseSelectASuggestions, configuration);
        return;
    }

    var isFullAddress = sys.toBoolean(suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex].attributes['full'].value);
    if (isFullAddress) {
        var moniker = suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex].value;
        this._getAddress(dataSet, moniker, configuration);
    } else {
        var refineInputHtmlElementLabel = configuration._getHtmlElementLabelFromInputMappingByEdqFieldName(dq.RefineInput);
        var prompt = suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex].attributes['prompt'].value;
        refineInputHtmlElementLabel.innerHTML = ExperianDataQuality.DataQuality.Address.RefinementText + ' ' + suggestionsHtmlElement.options[suggestionsHtmlElement.selectedIndex].innerHTML;
        
        this._showRefineInputPromptSet(configuration);
    }
};

ExperianDataQuality.DataQuality.Address.Client.prototype._getAddress = function _getAddress(dataSet, moniker, configuration) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var parameters = {};
    parameters[dq.Moniker] = moniker;
    parameters[dq.DataSet] = dataSet;
    
    var client = this;
    client._handleLoader(configuration, true);
    this._executeXMLHttpRequest(dq.Address.Configuration.GetAddressServiceUrl, parameters, function (response, errorMessage, xhr) {
        client._handleLoader(configuration, false);
        
        if (!sys.isNullOrEmpty(errorMessage)) {
            client._promptSetForErrorMessage(errorMessage, configuration);
            return;
        }

        client.setValues(response.AddressLineDictionary, configuration);
        client._showVerifiedAddressPromptSet(configuration, true, dataSet);
    });
};

ExperianDataQuality.DataQuality.Address.Client.prototype._promptSetForErrorMessage = function _promptSetForErrorMessage(errorMessage, configuration) {
    var dq = ExperianDataQuality.DataQuality;

    this._showErrorMessage(errorMessage, configuration);
    this._hideAllInputMappingElementsExcept(configuration._inputMappings, [dq.ManualEntry, dq.BackButton, dq.TryAgain, dq.ErrorMessage]);
};

ExperianDataQuality.DataQuality.Address.Client.prototype._showErrorMessage = function _showErrorMessage(message, configuration) {
    var dq = ExperianDataQuality.DataQuality;
    var sys = ExperianDataQuality.system;

    var errorMessageOutputMapping = configuration._getOutputMappingByEdqFieldName(dq.ErrorMessage);
    var errorMessageHtmlElement = sys.getElementById(errorMessageOutputMapping.htmlElementId);
    if (sys.isNull(errorMessageHtmlElement)) {
        return;
    }

    if (errorMessageHtmlElement.tagName === dq.HtmlTags.Label || errorMessageHtmlElement.tagName === dq.HtmlTags.Div) {
        errorMessageHtmlElement.innerHTML = message;
    } else {
        errorMessageHtmlElement.value = message;
    }
    dq.Client.showElement(errorMessageHtmlElement);
};

ExperianDataQuality.DataQuality.Address.Client.prototype._restoreInputValues = function _restoreInputValues(mapping) {
    var dq = ExperianDataQuality.DataQuality;
    var sys = ExperianDataQuality.system;

    for (var i = 0, max = mapping.length; i < max; i++) {
        var field = sys.getElementById(mapping[i].htmlElementIds[0]);
        if (sys.isNull(field)) {
            continue;
        }
        
        switch (mapping[i].edqFieldName) {
            case dq.DataSet:
            case dq.Street:
            case dq.City:
            case dq.State:
            case dq.Postcode:
            case dq.BuildingNumber:
            case dq.Postcode:
            case dq.Town:
            case dq.RefineInput:
            case dq.Line1:
            case dq.Line2:   
            case dq.Line3:
            case dq.Line4:
            case dq.Line5: 
                if (dq.Snapshot[mapping[i].edqFieldName] !== undefined) {
                    field.value = dq.Snapshot[mapping[i].edqFieldName];
                }
                break;
            default:
                continue;
        }
    }
}; 

ExperianDataQuality.DataQuality.Address.Client.prototype._handleLoader = function _handleLoader(configuration, showLoader) {
    var dq = ExperianDataQuality.DataQuality;
    var sys = ExperianDataQuality.system;
    
    var loader = null;
    this._handleErrorMessage(configuration, dq.ErrorMessage);
    for (var i = 0, max = configuration._inputMappings.length; i < max; i++) {
        var fieldName = configuration._inputMappings[i].edqFieldName;
        var htmlElement = null;
        
        if(fieldName === dq.BackButton) {
            htmlElement = configuration._getHtmlElementsFromInputMappingByEdqFieldName(fieldName);
        } else {
            htmlElement = configuration._getHtmlElementFromInputMappingByEdqFieldName(fieldName);
        }
        
        if(sys.isArray(htmlElement)) {
            for(var j = 0, max2 = htmlElement.length; j < max2; j++) {
                var element = sys.getElementById(htmlElement[j]);

                if (sys.isNull(element)) {
                    continue;
                }
                
                if(element.tagName === dq.HtmlTags.Button ||  element.tagName === dq.HtmlTags.Select) {
                    element.disabled = showLoader ? true : false;
                }
            }
        } else {
            if (sys.isNull(htmlElement)) {
                continue;
            }

            if(htmlElement.tagName === dq.HtmlTags.Button ||  htmlElement.tagName === dq.HtmlTags.Select) {
                htmlElement.disabled = showLoader ? true : false;
            }
        }
        
        if(fieldName === dq.Loader) {
            loader = htmlElement;
        }
        
        if(fieldName === dq.UnknownPostCode || fieldName === dq.KnownPostCode) {
            if(showLoader) {
                htmlElement.oldEvent = htmlElement.onclick;
                htmlElement.onclick = null;
            } else {
                if(htmlElement.onclick === null) {
                    htmlElement.onclick = htmlElement.oldEvent;
                } 
            }
        }
    }
    
    if(showLoader) {
        dq.Client.showElement(loader);
    } else {
        dq.Client.hideElement(loader);
    }    
};

ExperianDataQuality.DataQuality.Address.LicensedDataSets = ExperianDataQuality.DataQuality.Address.LicensedDataSets || [];
ExperianDataQuality.DataQuality.Address.EnterSelection = ExperianDataQuality.DataQuality.Address.EnterSelection || 'Enter selection';
ExperianDataQuality.DataQuality.Address.RefinementText = ExperianDataQuality.DataQuality.Address.RefinementText || 'Your selection covers a range of addresses. Enter your exact details:';
ExperianDataQuality.DataQuality.Address.ToManyMatchesMessage = ExperianDataQuality.DataQuality.Address.ToManyMatchesMessage  || 'Search cancelled (too many matches)';
ExperianDataQuality.DataQuality.Address.NoMatchesMessage = ExperianDataQuality.DataQuality.Address.NoMatchesMessage || 'No matches';
ExperianDataQuality.DataQuality.Address.PleaseSelectASuggestions = ExperianDataQuality.DataQuality.Address.PleaseSelectASuggestions || "Please select a suggestion.";
ExperianDataQuality.DataQuality.Address.PleaseEnterExactDetailsMessage = ExperianDataQuality.DataQuality.Address.PleaseEnterExactDetailsMessage || "Please enter exact details.";
ExperianDataQuality.DataQuality.Address.AddressOutsideOfRanageMessage =  ExperianDataQuality.DataQuality.Address.AddressOutsideOfRanageMessage  || "This address is outside of the range. Please try again or click Back and select the correct range.";