var ExperianDataQuality = ExperianDataQuality || {};
ExperianDataQuality.system = ExperianDataQuality.system || {};

ExperianDataQuality.system.isString = function isString(value) { return typeof value === "string"; };
ExperianDataQuality.system.isUndefined = function isUndefined(value) { return typeof(value) === "undefined"; };
ExperianDataQuality.system.isNull = function isNull(value) { return ExperianDataQuality.system.isUndefined(value) || value === null; };
ExperianDataQuality.system.isNullOrEmpty = function isNullOrEmpty(str) { return ExperianDataQuality.system.isNull(str) || str === ""; };
ExperianDataQuality.system.isFunction = function isFunction(obj) { return !ExperianDataQuality.system.isNull(obj) && Object.prototype.toString.call(obj) === '[object Function]'; };
ExperianDataQuality.system.isArray = function isArray(obj) { return !ExperianDataQuality.system.isNull(obj) && Object.prototype.toString.call(obj) === '[object Array]'; };
ExperianDataQuality.system.logError = function logError(obj) {
	if (window.console) {
		window.console.log(obj);
	} else { //IE does not have a logger.
		//alert(obj);
	};
};

ExperianDataQuality.system.containsInArray = function containsInArray(needle, haystack) {
    if (haystack.indexOf(needle) !== -1) {
        return true;
    }
    return false;
};

ExperianDataQuality.system.contains = function contains(needle, haystack) {
    if (typeof haystack[needle] === 'undefined') {
        return false;
    }
    return true;
};

ExperianDataQuality.system.getElementValue = function getElementValue(htmlElementId) {
    var htmlElement = this.getElementById(htmlElementId);
    if (this.isNull(htmlElement)) {
        return undefined;
    }

    return htmlElement.value;
};

ExperianDataQuality.system.setElementValue = function setElementValue(htmlElementId, value) {
    var htmlElement = this.getElementById(htmlElementId);
    if (this.isNull(htmlElement)) {
        return;
    }

    if (htmlElement.tagName === "LABEL") {
        htmlElement.innerHTML = value;
    } else {
        htmlElement.value = value;
    }
};

ExperianDataQuality.system.getElementById = function getElementById(selector) {
    return document.getElementById(selector);
};

ExperianDataQuality.system.getElementLabelById = function getElementLabelById(htmlElementId) {
    var labels = document.getElementsByTagName('Label');
   
    for (var i = 0, max = labels.length; i < max; i++) { 
        if(!this.isNull(labels[i].attributes['for'])) {        
            if(labels[i].attributes['for'].value === htmlElementId) {
                return labels[i];
            }    
        }
    }

    return undefined;
};

ExperianDataQuality.system.toBoolean = function toBoolean(value) {
    if (value.toString().toUpperCase() === 'TRUE' || value.toString() === '1') {
        return true;
    }
    return false;
};

ExperianDataQuality.system.beginsWith = function beginsWith(needle, haystack) {
    return (haystack.substr(0, needle.length) === needle);
};

ExperianDataQuality.system.endsWith = function endsWith(needle, haystack) {
    return haystack.indexOf(needle, haystack.length - needle.length) !== -1;
};

ExperianDataQuality.system.removeSpecialCharacters = function removeSpecialCharacters(value) {
    return value.replace(/[^\d.]/g, "");
};

ExperianDataQuality.system.inherit = (function () {
    var F = function (){};
    return function (C, P) {
        F.prototype = P.prototype;
        C.prototype = new F();
        C.uber = P.prototype;
        C.prototype.constructor = C;
    };
}());

if (Array && !Array.indexOf){
    Array.prototype.indexOf = function (elt /*, from*/) {
        var len = this.length;

        var from = Number(arguments[1]) || 0;
        from = (from < 0)? Math.ceil(from) : Math.floor(from);
        if (from < 0) { from += len; }
        for (; from < len; from++) {
                if (from in this && this[from] === elt) { return from;}
        }
        return -1;
    };
};

if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/g, ''); 
    };
};