
PXHR = function() {
    try { this.xhr = new XMLHttpRequest; } catch (e) {;}
    try { this.xhr = new ActiveXObject('Msxml2.XMLHTTP'); } catch (e) {;}
    try { this.xhr = new ActiveXObject('Microsoft.XMLHTTP'); } catch (e) {;}
    try { this.xhr = new ActiveXObject('Msxml2.XMLHTTP.4.0'); } catch (e) {;}
    var pxhr = this;
    this.xhr.onreadystatechange = function() {
        pxhr._updateProps();
        return pxhr.onreadystatechange ? pxhr.onreadystatechange() : null;
    };
    pxhr._updateProps();
}

PXHR.prototype = 
{
    abort: function() { return this.xhr.abort();},
    getAllResponseHeaders: function() {return this.xhr.getAllResponseHeaders();},
    getResponseHeader: function(header) {return this.xhr.getResponseHeader(header);},
    overrideMimeType: function(){return this.xhr.overrideMimeType()},
    send: function(data) {return this.xhr.send(data);},
    setRequestHeader: function(header, value) {return this.xhr.setRequestHeader(header, value);},
    open: function(method, url, async, username, password) 
    {
        if(method.toLowerCase() == 'get' && this.sameServer(url) && !url.contains('csrftoken'))
        {
            if(url.contains('?'))
            {
                url += '&csrftoken='+this.getToken();        
            }
            else
            {
                url += '?csrftoken='+this.getToken();        
            }    
        }
        if (username) 
        {
            return this.xhr.open(method, url, async, username, password);
        }
        return this.xhr.open(method, url, async);
    }
}


PXHR.prototype._updateProps = function() {
    this.readyState = this.xhr.readyState;
    this.timeout = this.xhr.timeout;
    this.upload = this.xhr.upload;
    this.withCredentials = this.xhr.withCredentials;
    if (this.readyState == 4) {
        this.response = this.xhr.response;
        this.responseText = this.xhr.responseText;
        this.responseType = this.xhr.responseType;
        this.responseXML = this.xhr.responseXML;
        this.status = this.xhr.status;
        this.statusText = this.xhr.statusText;
    }
}

//external vars: server,
PXHR.sameServer = function (path,csrftoken)
{
    return path.toLowerCase().contains(server) || !path.toLowerCase().contains('http');
}
PXHR.getToken = function()
{
    return csrftoken;        
}
window.XMLHttpRequest = PXHR;