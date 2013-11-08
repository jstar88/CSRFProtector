(function() {
	PXHR = function(type) {
		if (!type) {
			this.original = new window.oldXMLHttpRequest;
		} else {
			this.original = new window.oldXMLHttpRequest(type);
		}
		var pxhr = this;
		this.original.onreadystatechange = function() {
			pxhr._updateProps();
			return pxhr.onreadystatechange ? pxhr.onreadystatechange() : null;
		};
		this._updateProps();
		this.original.onload = function() {
			return pxhr.onload ? pxhr.onload() : null;
		}
	}
    /******* PUBLIC FUNCTIONS: AS XMLHTTPREQUEST API  *******/
	PXHR.prototype = {
		abort: function() {
			return this.original.abort();
		},
		getAllResponseHeaders: function() {
			return this.original.getAllResponseHeaders();
		},
		getResponseHeader: function(header) {
			return this.original.getResponseHeader(header);
		},
		overrideMimeType: function() {
			return this.original.overrideMimeType()
		},
		send: function(data) {
			return this.original.send(data);
		},
		setRequestHeader: function(header, value) {
			return this.original.setRequestHeader(header, value);
		},
		open: function(method, url, async, username, password) {
			if (method.toLowerCase() == 'get' && this.sameServer(url)) {
                if (url.contains('?')){
                    url += '&';
                }
                else{
                    url += '?';
                }              
                if(!url.contains('csrftoken')){
                    url += 'csrftoken=' + this.getToken(url);        
                }
				if (!url.contains('csrftokenAjax')) {
					url += '&csrftokenAjax=1' + this.getToken(url);
				}
			}
			if (username) {
				return this.original.open(method, url, async, username, password);
			}
			return this.original.open(method, url, async);
		}
	}
	PXHR.prototype.onreadystatechange = function() {}
    
    /******* PUBLIC PROPERTIES: AS XMLHTTPREQUEST API   *******/
	PXHR.prototype._updateProps = function() {
		this.readyState = this.original.readyState;
		this.timeout = this.original.timeout;
		this.upload = this.original.upload;
		this.withCredentials = this.original.withCredentials;
		if (this.readyState == 4) {
			this.response = this.original.response;
			this.responseText = this.original.responseText;
			this.responseType = this.original.responseType;
			this.responseXML = this.original.responseXML;
			this.status = this.original.status;
			this.statusText = this.original.statusText;
			this.updateCsrftoken();
		}
	}
    
    /******* PRIVATE FUNCTIONS: UTILS  *******/
	PXHR.prototype.sameServer = function(path) {
		return path.toLowerCase().contains(server) || !path.toLowerCase().contains('http');
	}
	PXHR.prototype.getToken = function(url) {
	    url = this.pathinfo(url);
		if (typeof csrftoken[url] != "undefined") {
			return csrftoken[url]; // not sure if is equal to server['remote uri']
		}
		return csrftoken['global'];
	}
	PXHR.prototype.updateCsrftoken = function() {
		var ob = document.getElementById("csrftokenUpdater");
		if (ob == null) return;
		var s = document.createElement("script");
		s.type = "text/javascript";
		s.text = ob.text;
		ob.remove();
		document.getElementsByTagName("head")[0].appendChild(s);
	}
	PXHR.prototype.pathinfo = function(str) {
		return "/"+str.replace(/^(?:\/\/|[^\/]+)*\//, "");
	}
    /******* OTHERS UTILS  *******/
	Element.prototype.remove = function() {
		this.parentElement.removeChild(this);
	}
	NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
		for (var i = 0, len = this.length; i < len; i++) {
			if (this[i] && this[i].parentElement) {
				this[i].parentElement.removeChild(this[i]);
			}
		}
	}
    
    /******* PXHR INJECTION  *******/
	if (window.XMLHttpRequest) {
		window.oldXMLHttpRequest = window.XMLHttpRequest;
		window.XMLHttpRequest = PXHR;
	} else if (window.ActiveXObject) {
		window.oldXMLHttpRequest = window.ActiveXObject;
		window.ActiveXObject = PXHR;
	}
})();