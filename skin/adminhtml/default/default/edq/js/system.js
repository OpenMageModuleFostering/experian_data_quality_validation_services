var EDQ = EDQ || {};
EDQ.system = EDQ.system || {};

EDQ.system.isUndefined = function isUndefined(value) { return typeof(value) == "undefined"; };
EDQ.system.isNull = function isNull(value) { return EDQ.system.isUndefined(value) || value == null; };
EDQ.system.isNullOrEmpty = function isNullOrEmpty(str) { return EDQ.system.isNull(str) || str == ""; };
EDQ.system.isFunction = function isFunction(obj) { return !EDQ.system.isNull(obj) && Object.prototype.toString.call(obj) === '[object Function]'; };
EDQ.system.logError = function logError(obj) {
	if (window.console) {
		window.console.log(obj);
	} else { //IE does not have a logger.
		//alert(obj);
	};
};

EDQ.system.inherit = (function () {
    var F = function (){};
    return function (C, P) {
        F.prototype = P.prototype;
        C.prototype = new F();
        C.uber = P.prototype;
        C.prototype.constructor = C;
    }
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

EDQ.system.getById = function getById(selector) {
    return document.getElementById(selector);
};

EDQ.system.beginsWith = function beginsWith(needle, haystack) {
    return (haystack.substr(0, needle.length) == needle);
};