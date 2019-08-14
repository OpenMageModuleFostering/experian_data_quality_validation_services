var ExperianDataQuality = ExperianDataQuality || {};
ExperianDataQuality.DataQuality = ExperianDataQuality.DataQuality || {};
ExperianDataQuality.DataQuality.HtmlTags = ExperianDataQuality.DataQuality.HtmlTags || {};

//Configuration_Start
ExperianDataQuality.DataQuality.Configuration = function Configuration(inputMappings, outputMappings, type) {
    this._inputMappings = inputMappings || [];
    this._outputMappings = outputMappings || [];
    this._type = type;
};

ExperianDataQuality.DataQuality.Configuration.prototype.addInputMapping = function addInputMapping(edqFieldName, htmlElementId) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var mapping = this.getInputMappingByEdqFieldName(edqFieldName);
    if (!sys.isNull(mapping)) {
        mapping.addHtmlElementId(htmlElementId);
    } else {
        mapping = new dq.InputMapping(edqFieldName, htmlElementId);
        this._inputMappings.push(mapping);
    };
};

ExperianDataQuality.DataQuality.Configuration.prototype._getHtmlElementLabelFromInputMappingByEdqFieldName = function _getHtmlElementLabelFromInputMappingByEdqFieldName(edqFieldName) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var htmlElement = this._getHtmlElementFromInputMappingByEdqFieldName(edqFieldName);
    if (sys.isNull(htmlElement)) {
        return undefined;
    }

    return sys.getElementLabelById(htmlElement.id);
};

ExperianDataQuality.DataQuality.Configuration.prototype._getHtmlElementFromInputMappingByEdqFieldName = function _getHtmlElementFromInputMappingByEdqFieldName(edqFieldName) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var inputMapping = this.getInputMappingByEdqFieldName(edqFieldName);

    if (sys.isNull(inputMapping) || inputMapping.htmlElementIds.length === 0) {
        return undefined;
    }

    return sys.getElementById(inputMapping.htmlElementIds[0]);
};

ExperianDataQuality.DataQuality.Configuration.prototype._getHtmlElementsFromInputMappingByEdqFieldName = function _getHtmlElementsFromInputMappingByEdqFieldName(edqFieldName) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var inputMapping = this.getInputMappingByEdqFieldName(edqFieldName);

    if (sys.isNull(inputMapping) || inputMapping.htmlElementIds.length === 0) {
        return undefined;
    }

    return inputMapping.htmlElementIds;
};

ExperianDataQuality.DataQuality.Configuration.prototype.getInputMappingByEdqFieldName = function getInputMappingByEdqFieldName(edqFieldName) {
    var sys = ExperianDataQuality.system;

    if (this._inputMappings.length === 0) {
        return undefined;
    }

    for (var i = 0, max = this._inputMappings.length; i < max; i++) {
        if (this._inputMappings[i].edqFieldName === edqFieldName) {
            return this._inputMappings[i];
        }
    }
};

ExperianDataQuality.DataQuality.Configuration.prototype.addOutputMapping = function addOutputMapping(htmlElementId, edqFieldName) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var mapping = this.getOutputMappingByHtmlElementId(htmlElementId);
    if (!sys.isNull(mapping)) {
        mapping.addEdqFieldName(edqFieldName);
    } else {
        mapping = new dq.OutputMapping(htmlElementId, edqFieldName);
        this._outputMappings.push(mapping);
    };
};

ExperianDataQuality.DataQuality.Configuration.prototype.getOutputMappingByHtmlElementId = function getOutputMappingByHtmlElementId(htmlElementId) {
    var sys = ExperianDataQuality.system;

    if (this._outputMappings.length === 0) {
        return undefined;
    }
    for (var i = 0, max = this._outputMappings.length; i < max; i++) { 
        if (this._outputMappings[i].htmlElementId === htmlElementId) {
            return this._outputMappings[i];
        }
    }
};

ExperianDataQuality.DataQuality.Configuration.prototype._getOutputMappingByEdqFieldName = function _getOutputMappingByEdqFieldName(edqFieldName) {
    var sys = ExperianDataQuality.system;

    if (this._outputMappings.length === 0) {
        return undefined;
    }

    for (var i = 0, max = this._outputMappings.length; i < max; i++) {
        for (var j = 0, total = this._outputMappings[i].edqFieldNames.length; j < total; j++) {
            if (this._outputMappings[i].edqFieldNames[j] === edqFieldName) {
                return this._outputMappings[i];
            }
        }
    }

    return undefined;
};
//Configuration_End

//Mapping_Start
ExperianDataQuality.DataQuality.InputMapping = function InputMapping(edqFieldName, htmlElementIdOrIds) {
    var sys = ExperianDataQuality.system;

    this.edqFieldName = edqFieldName;
    if (sys.isArray(htmlElementIdOrIds)) {
        this.htmlElementIds = htmlElementIdOrIds;
    } 
    else {
        this.htmlElementIds = new Array();
        
        if(edqFieldName === 'Line4') { 
            this.htmlElementIds = [htmlElementIdOrIds];
        }
        else {        
            this.addHtmlElementId(htmlElementIdOrIds);
        }
    }
};

ExperianDataQuality.DataQuality.InputMapping.prototype.addHtmlElementId = function addHtmlElementId(htmlElementId) {
    var sys = ExperianDataQuality.system;

    if (sys.isNull(htmlElementId) || typeof htmlElementId !== "string") { 
        throw "Invalid htmlElementId value: " + htmlElementId; 
    }
    
    if (!sys.contains(htmlElementId, this.htmlElementIds)) {
        this.htmlElementIds = [htmlElementId];
    }
};

ExperianDataQuality.DataQuality.OutputMapping = function OutputMapping(htmlElementId, edqFieldNameOrNames) {
    var sys = ExperianDataQuality.system;

    this.htmlElementId = htmlElementId;       
    if (sys.isArray(edqFieldNameOrNames)) {
        this.edqFieldNames = edqFieldNameOrNames;
    } else {
        this.edqFieldNames = [];
        this.addEdqFieldName(edqFieldNameOrNames);        
    };
};

ExperianDataQuality.DataQuality.OutputMapping.prototype.addEdqFieldName = function addEdqFieldName(edqFieldName) {
    var sys = ExperianDataQuality.system;

    if (sys.isNull(edqFieldName) || !sys.isString(edqFieldName)) {
        throw "Invalid edqFieldName value. edqFieldName is either null, undefined or not a string";
    }

    if (!sys.contains(edqFieldName, this.edqFieldNames)) {
        this.edqFieldNames.push(edqFieldName);
    }
};
//Mapping_End

//Client_Start
ExperianDataQuality.DataQuality.Client = function Client(configurations) {
    this._configurations = configurations || []; 
};

ExperianDataQuality.DataQuality.Client.prototype.addConfiguration = function addConfiguration(configuration) { 
    var sys = ExperianDataQuality.system;

    if (sys.isNull(configuration)) { 
        return; 
    }

    if (!sys.contains(configuration, this._configurations)) { 
        this._configurations.push(configuration);
    }
};

ExperianDataQuality.DataQuality.Client.prototype.initialize = function initialize() {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    for (var i = 0, max = this._configurations.length; i < max; i++) {
        this._initializeForConfiguration(this._configurations[i]);
    }
};

ExperianDataQuality.DataQuality.Client.prototype._initializeForConfiguration = function _initializeForConfiguration(configuration) { };

ExperianDataQuality.DataQuality.Client.prototype._hideAllInputMappingElementsExcept = function _hideAllInputMappingElementsExcept(inputMappings, edqFields) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    for (var j = 0, max = inputMappings.length; j < max; j++) {
        var edqField = inputMappings[j].edqFieldName;
        
        for(var k = 0, max2 = inputMappings[j].htmlElementIds.length; k < max2; k++) {
            
            var showElement = sys.containsInArray(edqField, edqFields);
            
            var elementId = inputMappings[j].htmlElementIds[k];
            
            if(showElement === true) {
                if(edqField === dq.BackButton) {
                    var backButtonIdSuffix = "";
                    if(sys.containsInArray(dq.Suggestions, edqFields)) {
                        backButtonIdSuffix = "suggestions";
                    } else if(sys.containsInArray(dq.TryAgain, edqFields)) {
                        backButtonIdSuffix = "tryagain";
                    } else if(sys.containsInArray(dq.NextButton, edqFields)) {
                        backButtonIdSuffix = "next";
                    }
                    
                    if(backButtonIdSuffix !== "" && !sys.endsWith(backButtonIdSuffix, elementId)) {
                        showElement = false;
                    }
                }
            }
            
            if (showElement) {
                this._showElementAndLabel(elementId);
            } else {
                this._hideElementAndLabel(elementId); 
            }
        
        }
    } 
};

ExperianDataQuality.DataQuality.Client.prototype._executeXMLHttpRequest = function _executeXMLHttpRequest(url, parameters, callback) {
    var sys = ExperianDataQuality.system;
	
    var xhr;

    if(typeof XMLHttpRequest !== 'undefined') {
        xhr = new XMLHttpRequest(); // code for IE7+, Firefox, Chrome, Opera, Safari
    } else {
        var versions = ["MSXML2.XmlHttp.5.0",
                        "MSXML2.XmlHttp.4.0",
                        "MSXML2.XmlHttp.3.0", 
                        "MSXML2.XmlHttp.2.0",
                        "Microsoft.XmlHttp"]; // code for IE6, IE5

        for(var i = 0, max = versions.length; i < max; i++) {
            try {
                xhr = new ActiveXObject(versions[i]);
                break;
            } catch(e){
                continue;
            }
        } 
    }

    xhr.onreadystatechange = function ensureReadiness() {
        if(xhr.readyState < 4 || xhr.status !== 200) {
            if (xhr.status === 404) 
            {
                callback('', xhr.statusText, xhr); 
            }
            return;
        } 
 
        if (xhr.readyState === 4) {
            var response;
            var errorMessage;

            try {
                response = JSON.parse(xhr.responseText);
                errorMessage = response.error;
            } catch (error) {
                sys.logError(error);
                errorMessage = "Invalid response";
            }
			
            
            callback(response, errorMessage, xhr);
        }
    };
    
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
    xhr.setRequestHeader('Accept', 'application/json, text/html, application/xml, text/xml, */*'); 
    xhr.ontimeout = function () { callback('', 'Operation timed out.', xhr); };
    
    var requestString = 'form_key' + '=' + window.FORM_KEY + '&';
    for(parameter in parameters) {
        requestString += parameter + '=' + encodeURIComponent(parameters[parameter]) + '&';
    } 
    
    xhr.send(requestString);
    
    if (xhr.status === 404) 
    { 
        callback('', xhr.statusText, xhr); 
    }
};

ExperianDataQuality.DataQuality.Client.prototype._showElementAndLabel = function _showElementAndLabel(elementId) {
    this._executeActionOnElementAndLabel(elementId, ExperianDataQuality.DataQuality.Client.showElement);
};

ExperianDataQuality.DataQuality.Client.prototype._hideElementAndLabel = function _hideElementAndLabel(elementId) { 
  this._executeActionOnElementAndLabel(elementId, ExperianDataQuality.DataQuality.Client.hideElement);
};

ExperianDataQuality.DataQuality.Client.prototype._executeActionOnElementAndLabel = function _executeActionOnElementAndLabel(elementId, action) {
    var sys = ExperianDataQuality.system;

    var field = sys.getElementById(elementId);
    if (!sys.isNull(field)) {
        action(field);
    }

    var label = sys.getElementLabelById(elementId);
    if(!sys.isNull(label)) {
        action(label);    
    }
};

ExperianDataQuality.DataQuality.Client.prototype._handleErrorMessage = function _handleErrorMessage(configuration, edqFieldForMessage, message) {
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;

    var errorMessageLabelMapping = configuration._getOutputMappingByEdqFieldName(edqFieldForMessage);
    if (!sys.isNullOrEmpty(errorMessageLabelMapping)) {
        dq.Client._setValidationMessage(errorMessageLabelMapping.htmlElementId, message);
    }
};

ExperianDataQuality.DataQuality.Client._setValidationMessage = function _setValidationMessage(textId, text) {
    var sys = ExperianDataQuality.system;
    var client = ExperianDataQuality.DataQuality.Client;

    var validationMessageElement = sys.getElementById(textId);

    if (!sys.isNull(validationMessageElement)) {
        validationMessageElement.innerHTML = text;
        validationMessageElement.style.display = sys.isNullOrEmpty(text) ? 'none' : 'inline';
    }
};

ExperianDataQuality.DataQuality.Client.prototype.setValues = function setValues(result, configuration) {
    var dq = ExperianDataQuality.DataQuality;
    var sys = ExperianDataQuality.system; 

    if (!sys.isNull(result[dq.ErrorMessage])) { return; }
	
    for (var i = 0; i < configuration._outputMappings.length; i++) {
        dq.Client._setValue(configuration._outputMappings[i], result, configuration._type);
    }
};

ExperianDataQuality.DataQuality.Client._setValue = function _setValue(mapping, result, type) { 
    var sys = ExperianDataQuality.system;
    var dq = ExperianDataQuality.DataQuality;
    
    var client = dq.Client;

    var val = '';
    var edqFieldName = ''; 
    for (var j = 0; j < mapping.edqFieldNames.length; j++) {
        edqFieldName = mapping.edqFieldNames[j];        
        var fieldValue = result[edqFieldName];

        if (sys.isNullOrEmpty(fieldValue)) {
            continue;
        };

        if (val.length > 0) {
            val += client.delimiter;
        };

        val += fieldValue;
    }

    if (edqFieldName === dq.Corrections) {
        var returnMessage = sys.isNullOrEmpty(val) ? '' : '(Suggested: ' + val + ' )';
        client._setValidationMessage(mapping.htmlElementId, returnMessage);
    } else {
        client.setElementValue(mapping.htmlElementId, val);
    }
}; 
ExperianDataQuality.DataQuality.Client.delimiter = " ";

ExperianDataQuality.DataQuality.Client.setElementValue = function setElementValue(htmlElementId, value) {
	var sys = ExperianDataQuality.system;
	
	sys.setElementValue(htmlElementId, value);
};

ExperianDataQuality.DataQuality.Client.showElement = function showElement(element) {
    ExperianDataQuality.DataQuality.Client._executeShowOrHideElementContainerOrElement(element, true);
};

ExperianDataQuality.DataQuality.Client.hideElement = function hideElement(element) {
    ExperianDataQuality.DataQuality.Client._executeShowOrHideElementContainerOrElement(element, false);
};

ExperianDataQuality.DataQuality.Client._executeShowOrHideElementContainerOrElement = function _executeShowOrHideElementContainerOrElement(element, show) {
    var dq = ExperianDataQuality.DataQuality;
    var sys = ExperianDataQuality.system;
    var client = dq.Client;

    if (sys.isNull(element)) {
        return;
    }
    
    if(element.tagName === dq.HtmlTags.Input && !show && element.id.match(/-edq-/) !== null) {
        element.value = '';
    }
    
    if(element.tagName === dq.HtmlTags.Label || element.tagName === dq.HtmlTags.Div) {
        element.style.display = show ? "" : "none";
    }
    
    var container = client._tryGetElementContainer(element);
    if(!sys.isNull(container)) {
        container.style.display = show ? "" : "none";
        return;
    }

    element.style.display = show ? "" : "none";
};

ExperianDataQuality.DataQuality.Client._tryGetElementContainer = function _tryGetElementContainer(element) {
    var client = ExperianDataQuality.DataQuality.Client;
    var sys = ExperianDataQuality.system;
    
    var parentNodeOfElement = element.parentNode;
    var classesOfElement = parentNodeOfElement.className;
    
    while(ExperianDataQuality.DataQuality.HtmlTags.ListItem !== parentNodeOfElement.nodeName) 
    {        
        if(( ExperianDataQuality.DataQuality.HtmlTags.Div === parentNodeOfElement.nodeName && classesOfElement.match(/edq_wrapper/) !== null))
        {
            break;
        }
        
        parentNodeOfElement = parentNodeOfElement.parentNode;
        classesOfElement = parentNodeOfElement.className;
    }
     
    if (client._isContainerElement(parentNodeOfElement)) {
        return parentNodeOfElement;
    }

    return undefined;
};

ExperianDataQuality.DataQuality.Client._isContainerElement = function _isContainerElement(element){
    var dq = ExperianDataQuality.DataQuality;
    
    if ((element.tagName === dq.HtmlTags.Div
      || element.tagName === dq.HtmlTags.ListItem
      || element.tagName === dq.HtmlTags.Span
      || element.tagName === dq.HtmlTags.TableHeader
      || element.tagName === dq.HtmlTags.TableData
      || element.tagName === dq.HtmlTags.TableRow)) {
        return true;
    }

    return false;
};

ExperianDataQuality.DataQuality.Client.collectValuesFromInputMapping = function collectValuesFromInputMapping(inputMapping) {
    var sys = ExperianDataQuality.system;

    value = '';
    for (var i = 0, max = inputMapping.htmlElementIds.length; i < max; i++) {
        var htmlElement = sys.getElementById(inputMapping.htmlElementIds[i]);

        if (!sys.isNull(htmlElement) && !sys.isNullOrEmpty(htmlElement.value)) {
            value += htmlElement.value + ' ';
        }
    }

    return value.trim();
};

//Client_End
ExperianDataQuality.DataQuality.Active = 'Active';
ExperianDataQuality.DataQuality.Deactive = 'Deactive';
ExperianDataQuality.DataQuality.ManualEntryState = ExperianDataQuality.DataQuality.Deactive;

ExperianDataQuality.DataQuality.ConfigurationType = { Address: 3 };
ExperianDataQuality.DataQuality.WorkFlowStages = { Input: 0, Suggestions: 1, Refining: 2, Verified: 3 };

ExperianDataQuality.DataQuality.Number = 'Number';
ExperianDataQuality.DataQuality.AreaCode = 'DefaultCountryCode';
ExperianDataQuality.DataQuality.EmailAddress = 'EmailAddress';
ExperianDataQuality.DataQuality.Message = 'Message';
ExperianDataQuality.DataQuality.Certainty = 'Certainty';
ExperianDataQuality.DataQuality.Loader = 'Loader';
ExperianDataQuality.DataQuality.ErrorMessage = 'ErrorMessage';
ExperianDataQuality.DataQuality.ValidationStatusVerified = 'Verified';
ExperianDataQuality.DataQuality.ValidationStatusNotVerified = 'Not verified';
ExperianDataQuality.DataQuality.ValidationStatus = 'ValidationStatus';
ExperianDataQuality.DataQuality.Country = 'Country';
ExperianDataQuality.DataQuality.PhoneCountry = 'PhoneCountry';
ExperianDataQuality.DataQuality.Suggested = 'Suggested';
ExperianDataQuality.DataQuality.Corrections = 'Corrections';
  
ExperianDataQuality.DataQuality.DataSet = 'DataSet'; 
ExperianDataQuality.DataQuality.Street = 'Street1';
ExperianDataQuality.DataQuality.City = 'City';
ExperianDataQuality.DataQuality.StreetNumber = 'StreetNumber';
ExperianDataQuality.DataQuality.Postcode = 'Postcode';
ExperianDataQuality.DataQuality.State = 'State';
ExperianDataQuality.DataQuality.BuildingNumber = 'BuildingNumberOrName';
ExperianDataQuality.DataQuality.Town = 'Town';
ExperianDataQuality.DataQuality.FindAddressButton = 'FindAddressButton';
ExperianDataQuality.DataQuality.ChangeAddressButton = 'ChangeAddressButton';
ExperianDataQuality.DataQuality.BackButton = 'BackButton';
ExperianDataQuality.DataQuality.SelectButton = 'SelectButton';
ExperianDataQuality.DataQuality.NextButton = 'NextButton';
ExperianDataQuality.DataQuality.Suggestions = 'Suggestions';
ExperianDataQuality.DataQuality.UnknownPostCode = 'UnknownPostCode';
ExperianDataQuality.DataQuality.KnownPostCode = 'KnownPostCode';
ExperianDataQuality.DataQuality.RefineInput = 'Refinement';
ExperianDataQuality.DataQuality.ManualEntry = 'ManualEntry';
ExperianDataQuality.DataQuality.TryAgain = 'TryAgain';
ExperianDataQuality.DataQuality.Moniker = 'Moniker';
ExperianDataQuality.DataQuality.Layout = 'Layout';
ExperianDataQuality.DataQuality.PickList = 'PickList';

ExperianDataQuality.DataQuality.Line1 = 'Line0';
ExperianDataQuality.DataQuality.Line2 = 'Line1';
ExperianDataQuality.DataQuality.Line3 = 'Line2';
ExperianDataQuality.DataQuality.Line4 = 'Line3';
ExperianDataQuality.DataQuality.Line5 = 'Line4';
ExperianDataQuality.DataQuality.Line6 = 'Line5';

ExperianDataQuality.DataQuality.HtmlTags.Span = 'SPAN';
ExperianDataQuality.DataQuality.HtmlTags.Div = 'DIV';
ExperianDataQuality.DataQuality.HtmlTags.ListItem = 'LI';
ExperianDataQuality.DataQuality.HtmlTags.TableHeader = 'TH';
ExperianDataQuality.DataQuality.HtmlTags.TableData = 'TD';
ExperianDataQuality.DataQuality.HtmlTags.TableRow = 'TR';
ExperianDataQuality.DataQuality.HtmlTags.Select = 'SELECT';
ExperianDataQuality.DataQuality.HtmlTags.Label = 'LABEL';
ExperianDataQuality.DataQuality.HtmlTags.Button = 'BUTTON';
ExperianDataQuality.DataQuality.HtmlTags.Link = 'A';
ExperianDataQuality.DataQuality.HtmlTags.Option = 'OPTION';
ExperianDataQuality.DataQuality.HtmlTags.Input = 'INPUT';

ExperianDataQuality.DataQuality.Snapshot = { 
    'Line0'                : '',
    'Line1'                : '',
    'Line2'                : '',
    'Line3'                : '',
    'Line4'                : '',
    //'DataSet'              : '',
    'Street1'              : '',
    'City'                 : '',
    'Postcode'             : '',
    'State'                : '',
    'BuildingNumberOrName' : ''
};