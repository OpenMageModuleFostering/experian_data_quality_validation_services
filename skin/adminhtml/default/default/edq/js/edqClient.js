var EDQ = EDQ || {};
EDQ.DataQuality = EDQ.DataQuality || {};

//Configuration_Start
EDQ.DataQuality.Configuration = function Configuration(outputMappings, inputMappings, type) {
    var dq = EDQ.DataQuality;

    this._inputMappings = inputMappings || [];
    this._outputMappings = outputMappings || [];
    this.type = type;
};

EDQ.DataQuality.Configuration.prototype.addOutputMapping = function addOutputMapping(htmlFieldId, edqFieldName) {
    var dq = EDQ.DataQuality;
    var sys = EDQ.system;

    var mapping = this.getOutputMappingByElementId(htmlFieldId);
    if (!sys.isNull(mapping)) {
        mapping.addEDQFieldName(edqFieldName);
    } else {
        mapping = new dq.OutputMapping(htmlFieldId, edqFieldName);
        this._outputMappings.push(mapping);
    };
};

EDQ.DataQuality.Configuration.prototype.getOutputMappingByElementId = function getOutputMappingByElementId(htmlFieldId) {
    var sys = EDQ.system;

    if (sys.isNull(this._outputMappings) || this._outputMappings.length === 0) {
        return null;
    }

    for (var i = 0; i < this._outputMappings.length; i++) {
        if (this._outputMappings[i].htmlFieldId === htmlFieldId) {
            return this._outputMappings[i];
        }
    }
};

EDQ.DataQuality.Configuration.prototype._getOutputMappingByEdqFieldName = function _getOutputMappingByEdqFieldName(edqFieldName) {
    var sys = EDQ.system;

    if (sys.isNull(this._outputMappings) || this._outputMappings.length === 0) {
        return null;
    }

    for (var i = 0; i < this._outputMappings.length; i++) {
        for (var j = 0; j < this._outputMappings[i].edqFieldNames.length; j++) {
            if (this._outputMappings[i].edqFieldNames[j] === edqFieldName) {
                return this._outputMappings[i];
            }
        }
    }

    return null;
};

EDQ.DataQuality.Configuration.prototype.addInputMapping = function addInputMapping(edqFieldName, htmlFieldId) {
    var dq = EDQ.DataQuality;
    var sys = EDQ.system;

    var mapping = this.getInputMappingByEdqFieldName(edqFieldName);
    if (!sys.isNull(mapping)) {
        mapping.addHtmlFieldId(htmlFieldId);
    } else {
        mapping = new dq.InputMapping(edqFieldName, htmlFieldId);
        this._inputMappings.push(mapping);
    };
};

EDQ.DataQuality.Configuration.prototype.getInputMappingByEdqFieldName = function getInputMappingByEdqFieldName(edqFieldName) {
    var sys = EDQ.system;

    if (sys.isNull(this._inputMappings) || this._inputMappings.length === 0) {
        return null;
    }

    for (var i = 0; i < this._inputMappings.length; i++) {
        if (this._inputMappings[i].edqFieldName === edqFieldName) {
            return this._inputMappings[i];
        } 
    }
};
//Configuration_End

//Mapping_Start
EDQ.DataQuality.OutputMapping = function OutputMapping(htmlFieldId, edqFieldName) {
    this.htmlFieldId = htmlFieldId;
    this.edqFieldNames = [];
    this.addEDQFieldName(edqFieldName);
};

EDQ.DataQuality.OutputMapping.prototype.addEDQFieldName = function addEDQFieldName(edqFieldName) {
    var sys = EDQ.system;

    if (sys.isNull(edqFieldName) || typeof edqFieldName !== "string") { throw "Invalid edqFieldName value: " + edqFieldName; }

    this.edqFieldNames.push(edqFieldName);
};

EDQ.DataQuality.InputMapping = function InputMapping(edqFieldName, htmlFieldId) {
    this.edqFieldName = edqFieldName;
    this.htmlFieldIds = [];
    this.addHtmlFieldId(htmlFieldId);
};

EDQ.DataQuality.InputMapping.prototype.addHtmlFieldId = function addHtmlFieldId(htmlFieldId) {
    var sys = EDQ.system;

    if (sys.isNull(htmlFieldId) || typeof htmlFieldId !== "string") { throw "Invalid htmlFieldId value: " + htmlFieldId; }

    this.htmlFieldIds.push(htmlFieldId);
};
//Mapping_End

//Client_Start
EDQ.DataQuality.Client = function Client(configurations, settings) {
    var sys = EDQ.system;
    var dq = EDQ.DataQuality;

    settings = settings || {};
    this.phoneServiceUrl = settings.phoneUrl || '';
    this.emailServiceUrl = settings.emailUrl || '';
    this.serviceUrl = settings.url || '';
    this.emailValidationKey = settings.emailValidationKey || '';
    this.phoneValidationKey = settings.phoneValidationKey || '';
    this.fadeoutValidationMessageTimeout = settings.fadeoutValidationMessageTimeout || 1000;
    this.defaultCountry = settings.defaultCountry || 'USA';
    this.delimiter = settings.delimiter || ', ';
    this._configurations = configurations; 
    this._token = settings.token || '';
};
EDQ.DataQuality.Client.prototype.initialize = function initialize() {
    var sys = EDQ.system;

    if (sys.isNull(this._configurations)) { return; }

    for (var i = 0; i < this._configurations.length; i++) {
        this._initializeForConfiguration(this._configurations[i]);
    }
};

EDQ.DataQuality.Client.prototype._initializeForConfiguration = function _initializeForConfiguration(configuration) {
    var dq = EDQ.DataQuality;
    var sys = EDQ.system;

    var client = this;
    var elementIds = this._getElementIdsWhichInvokeValidation(configuration);

    for (var i = 0; i < elementIds.length; i++) {
        var element = sys.getById(elementIds[i]);

        if (sys.isNull(element)) { continue; };
        if (element.edq_eventsAttached === true) { continue; };

        element.oldValue = element.value;

        if (element.tagName === "SELECT") {
            var previousOnChange = element.onchange;

            element.onchange = function (event) {
                if (sys.isFunction(previousOnChange)) {
                    previousOnChange.apply(this, [event]);
                };

                client._validateField(configuration, element, function () { element.oldValue = element.value; });                
            };
        } else {
            var previousOnBlur = element.onblur;

            element.onblur = function (event) {
                if (sys.isFunction(previousOnBlur)) {
                    previousOnBlur.apply(this, [event]);
                };

                if(element.oldValue !== element.value) {
                    client._validateField(configuration, element, function () { element.oldValue = element.value; });
                }
            };
        }

        element.edq_eventsAttached = true;
    };
}; 

EDQ.DataQuality.Client.prototype._getElementIdsWhichInvokeValidation = function _getElementIdsWhichInvokeValidation(configuration) {
    var sys = EDQ.system;
    var dq = EDQ.DataQuality;

    var elementIds = [];

    if (!sys.isNull(configuration._inputMappings) && configuration._inputMappings.length > 0) {
        for (var i = 0; i < configuration._inputMappings.length; i++) {
            for (var j = 0; j < configuration._inputMappings[i].htmlFieldIds.length; j++) {
                elementIds.push(configuration._inputMappings[i].htmlFieldIds[j]);
            }
        }
    } else if (!sys.isNull(configuration._outputMappings) && configuration._outputMappings.length > 0) {
        for (var i = 0; i < configuration._outputMappings.length; i++) {
            elementIds.push(configuration._outputMappings[i].htmlFieldId);
        }
    }

    return elementIds;
};

EDQ.DataQuality.Client.prototype._validateField = function _validateField(configuration, element, afterValuesSetCallback) {
    var sys = EDQ.system;
    var dq = EDQ.DataQuality;
    var client = dq.Client;

    var edqFieldName;
    var serviceUrl = '';;
    switch (configuration.type) {
        case 1:
            edqFieldName = dq.Email;
            serviceUrl = this.emailServiceUrl;
            break;
        case 2:
            edqFieldName = dq.Phone;
            serviceUrl = this.phoneServiceUrl;
            break;
        default:
            sys.logError('Unsupported configuration type: ' + configuration.type);
    }
    var sessionToken = this._token;
    var htmlFieldIds = this._getFieldsFromMapping(edqFieldName, configuration);
    var value = '';
    for (var i = 0; i < htmlFieldIds.length; i++) {
        var element = client.getField(htmlFieldIds[i]);
        if (sys.isNull(element)) {
            continue;
        }

        value += element.value;
    }
    if(sys.isNullOrEmpty(value)) { return; }
 
    var countryCode = '';
    var phoneCountryExists = false;
    if (configuration.type === dq.ConfigurationType.Phone) {
        for (var i = 0; i < configuration._inputMappings.length; i++) {
            var inputMapping = configuration._inputMappings[i];
            if(inputMapping.edqFieldName === dq.PhoneCountry) {
                phoneCountryExists = true;
            }
        }

        if(phoneCountryExists) {
            var countryElements = this._getFieldsFromMapping(dq.PhoneCountry, configuration);
            var country = client.getFieldValue(countryElements[0]);
            countryCode = country;
        } else {
             countryCode = this.defaultCountry;
        }
    }
    
    client._setValidationMessageAndLoader(configuration, 'show', '');  

    var postParameters = {}; 
    if(configuration.type === 1) {
        postParameters = { 'email-address': element.value, 'session-security-token' : sessionToken };
    } else {
        postParameters = { 'telephone-number': element.value, 'telephone-area-code': countryCode, 'session-security-token' : sessionToken };
    }
    
    var client = this;
    var request = new Ajax.Request(serviceUrl,
        {
            method: 'post',  
            onSuccess: function(transport) { 
                client.processResult(transport, configuration, countryCode); 
                if(!sys.isNull(afterValuesSetCallback)) {
                    afterValuesSetCallback();
                }
                dq.Client._twistLoader(configuration, '');
            }, 
            onFailure: function(transport) {  
                client.processError(transport.xhr, transport.statusText, transport.error, configuration);
                    if(!sys.isNull(afterValuesSetCallback)) {
                        afterValuesSetCallback();
                    }
                }, 
            parameters: postParameters
        }
    );
};

EDQ.DataQuality.Client.prototype._getFieldsFromMapping = function _getFieldsFromMapping(edqFieldName, configuration) {
    var sys = EDQ.system;

    var fields = [];
    var fields = configuration.getInputMappingByEdqFieldName(edqFieldName).htmlFieldIds;

    if (!sys.isNull(fields)) {
        return fields;
    } else {
        fields.push(configuration._getOutputMappingByEdqFieldName(edqFieldName).htmlFieldId);
    }

    return fields;
};    

EDQ.DataQuality.Client.prototype.processResult = function processResult(response, configuration, countryCode) {
    var dq = EDQ.DataQuality;
    
    var responseInJson = JSON.parse(response.responseText);   
    var certainty = responseInJson.Certainty;
    
    switch (configuration.type) {
        case 1:
            responseInJson.ValidationMessageForLabel = (dq.EmailResultCodesAndDescriptions[certainty.toUpperCase()] 
                                                      ? dq.EmailResultCodesAndDescriptions[certainty.toUpperCase()] 
                                                      : certainty);     
            if(responseInJson.Corrections) {
                if(certainty.toUpperCase() !== 'UNKNOWN' && certainty.toUpperCase() !== 'VERIFIED' ) { 
                    responseInJson.Corrections = '(Suggested: ' + responseInJson.Corrections.join(' ') + ')';                
                }  
                else {
                    responseInJson.Corrections = '';
                }
            }            
            break;
        case 2:
            responseInJson.ValidationMessageForLabel = (dq.PhoneResultCodesAndDescriptions[certainty.toUpperCase()] 
                                                      ? dq.PhoneResultCodesAndDescriptions[certainty.toUpperCase()]
                                                      : certainty);
            break;
    }
    
    responseInJson.ValidationMessage = responseInJson.ValidationMessageForLabel;
        
    this.setValues(responseInJson, configuration, countryCode); 
};

EDQ.DataQuality.Client.prototype.processError = function processError(xhr, statusText, error, configuration) {
    var dq = EDQ.DataQuality;
    var client = dq.Client;
    var sys = EDQ.system;

    sys.logError(statusText);
    sys.logError(error);
    
    client._setValidationMessageAndLoader(configuration, '', 'Unable to validate.'); 
};

EDQ.DataQuality.Client._setValidationMessageAndLoader = function _setValidationMessageAndLoader(configuration, loaderDisplayStatus, responseText) {
    var dq = EDQ.DataQuality;
    var client = dq.Client;
    var sys = EDQ.system;
    
    var suggestionMessageLabelMapping = configuration._getOutputMappingByEdqFieldName(dq.CorrectionsForLabel);
    var validationMessageMapping = configuration._getOutputMappingByEdqFieldName(dq.ValidationMessageForLabel);	
	
    if (!sys.isNullOrEmpty(validationMessageMapping)) {
        client._setValidationMessage(validationMessageMapping.htmlFieldId, responseText);
		
        if(!sys.isNullOrEmpty(suggestionMessageLabelMapping)) {
            client._setValidationMessage(suggestionMessageLabelMapping.htmlFieldId, '');
        }
        
        client._twistLoader(configuration, loaderDisplayStatus);
    }
};

EDQ.DataQuality.Client.prototype.setValues = function setValues(result, configuration, countryCode) {
    var dq = EDQ.DataQuality;
    var sys = EDQ.system; 

    if (sys.isNull(result[dq.ErrorMessage])) {
        for (var i = 0; i < configuration._outputMappings.length; i++) {
            this._setValue(configuration._outputMappings[i], result, configuration.type, countryCode);
        }
    }
};

EDQ.DataQuality.Client.prototype._setValue = function _setValue(mapping, result, type, countryCode) {
    var dq = EDQ.DataQuality;
    var sys = EDQ.system;
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
            val += this.delimiter + ' ';
        };

        val += fieldValue;
    }

    if(edqFieldName === dq.ValidationMessageForLabel || edqFieldName === dq.CorrectionsForLabel)
    {         
        client._setValidationMessage(mapping.htmlFieldId, result[edqFieldName], result.Certainty, type);
    }  
    else 
    {
        client.setFieldValue(mapping.htmlFieldId, val);
    }
};

EDQ.DataQuality.Client._setValidationMessage = function _setValidationMessage(textId, text, certainty, type) {
    var sys = EDQ.system;
    var client = EDQ.DataQuality.Client;

    var validationMessageElement = client.getField(textId);

    if (!sys.isNull(validationMessageElement)) {
        if(validationMessageElement.tagName === "INPUT") {
            validationMessageElement.value = text;
        } else {
            validationMessageElement.innerHTML = text;
        
            validationMessageElement.style.display = sys.isNullOrEmpty(text) ? 'none' : 'inline'; 
            if(certainty !== undefined && type !== undefined) {            
                switch (type) {
                    case 1:                    
                        validationMessageElement.style.color = certainty.toUpperCase() === 'VERIFIED' || certainty.toUpperCase() === 'UNKNOWN'
                                                            ? 'rgb(105, 153, 53)' 
                                                            : '#D40707';                
                        break;
                    case 2:
                        validationMessageElement.style.color = certainty.toUpperCase() === 'UNVERIFIED' || certainty.toUpperCase() === "YOUR REQUEST CANNOT BE COMPLETED. YOUR TOKEN IS EXPIRED"
                                                            ? '#D40707'
                                                            : 'rgb(105, 153, 53)'; 
                        break;
                }
            }
        }
    }
};

EDQ.DataQuality.Client.setFieldValue = function setFieldValue(htmlFieldId, value) {
    var sys = EDQ.system;
    var client = EDQ.DataQuality.Client;

    var field = client.getField(htmlFieldId);

    if (sys.isNull(field)) {
        return;
    }

    field.value = value;
};

EDQ.DataQuality.Client.getFieldValue = function getFieldValue(htmlFieldId) {
    var client = EDQ.DataQuality.Client;
    var sys = EDQ.system;

    var field = client.getField(htmlFieldId);
    if (sys.isNull(field)) {
        return undefined;
    }

    return field.value;
};

EDQ.DataQuality.Client.getField = function getField(htmlFieldId) {
    return document.getElementById(htmlFieldId);
};

EDQ.DataQuality.Client._twistLoader = function _twistLoader(configuration, displayStatus) {
    var dq = EDQ.DataQuality;
    var sys = EDQ.system;
    var client = EDQ.DataQuality.Client;
    
    
    var loaderMapping = configuration._getOutputMappingByEdqFieldName(dq.Loader); 
    
    if(loaderMapping === undefined) {
        return;
    }

    var loeaderElement = client.getField(loaderMapping.htmlFieldId);

    if (!sys.isNull(loeaderElement)) { 
        loeaderElement.style.display = displayStatus === 'show' ? 'inline' : 'none' ;
    }
};
//Client_End

EDQ.DataQuality.ConfigurationType = { Email: 1, Phone: 2 };

EDQ.DataQuality.Phone = 'Number';
EDQ.DataQuality.AreaCode = 'AreaCode';
EDQ.DataQuality.Email = 'Email';
EDQ.DataQuality.Message = 'Message';
EDQ.DataQuality.Certainty = 'Certainty';
EDQ.DataQuality.ValidationMessageForLabel = 'ValidationMessageForLabel';
EDQ.DataQuality.ValidationMessage = 'ValidationMessage';
EDQ.DataQuality.Loader = 'Loader';
EDQ.DataQuality.ErrorMessage = 'ErrorMessage';
EDQ.DataQuality.ValidationStatusVerified = 'Verified';
EDQ.DataQuality.ValidationStatusNotVerified = 'Not verified';
EDQ.DataQuality.ValidationStatus = 'ValidationStatus';
EDQ.DataQuality.Country = 'Country';
EDQ.DataQuality.PhoneCountry = 'PhoneCountry';
EDQ.DataQuality.Suggested = 'Suggested';
EDQ.DataQuality.CorrectionsForLabel = 'Corrections';
EDQ.DataQuality.ResultCode = 'ResultCode';
EDQ.DataQuality.PhoneType = 'PhoneType';

EDQ.DataQuality.PhoneResultCodesAndDescriptions = {
    'VERIFIED'                    : 'Verified',
    'UNVERIFIED'                  : 'Could not be Verified',
    'UNKNOWN'                     : 'Verified',
    'ABSENT'                      : 'Verified',
    'TELESERVICE NOT PROVISIONED' : 'Verified',
    'CALL BARRED'                 : 'Verified'
};

EDQ.DataQuality.EmailResultCodesAndDescriptions = {
    'VERIFIED'      : 'Email address validated!',
    'UNDELIVERABLE' : 'Sorry, username does not exist, or mailbox is suspended or disabled. To proceed, please provide a valid e-mail address.',
    'UNREACHABLE'   : 'Sorry, email domain could not be reached or verified. To proceed, please provide a valid e-mail address.',
    'ILLEGITIMATE'  : 'Email could not be verified. To proceed, please provide a valid e-mail address.',
    'DISPOSABLE'    : 'Sorry, email is suspected as disposable. To proceed, please provide a valid e-mail address.',
    'UNKNOWN'       : 'Email address validated!'
};