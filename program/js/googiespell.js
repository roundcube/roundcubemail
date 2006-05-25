/*
Last Modified: 28/04/06 16:28:09

  AmiJs library
    A very small library with DOM and Ajax functions.
    For a much larger script look on http://www.mochikit.com/
  AUTHOR
    4mir Salihefendic (http://amix.dk) - amix@amix.dk
  LICENSE
    Copyright (c) 2006 Amir Salihefendic. All rights reserved.
    Copyright (c) 2005 Bob Ippolito. All rights reserved.
    http://www.opensource.org/licenses/mit-license.php
  VERSION
    2.1
  SITE
    http://amix.dk/amijs
**/

var AJS = {
////
// Accessor functions
////
  /**
   * @returns The element with the id
   */
  getElement: function(id) {
    if(typeof(id) == "string") 
      return document.getElementById(id);
    else
      return id;
  },

  /**
   * @returns The elements with the ids
   */
  getElements: function(/*id1, id2, id3*/) {
    var elements = new Array();
      for (var i = 0; i < arguments.length; i++) {
        var element = this.getElement(arguments[i]);
        elements.push(element);
      }
      return elements;
  },

  /**
   * @returns The GET query argument
   */
  getQueryArgument: function(var_name) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
      var pair = vars[i].split("=");
      if (pair[0] == var_name) {
        return pair[1];
      }
    }
    return null;
  },

  /**
   * @returns If the browser is Internet Explorer
   */
  isIe: function() {
    return (navigator.userAgent.toLowerCase().indexOf("msie") != -1 && navigator.userAgent.toLowerCase().indexOf("opera") == -1);
  },

  /**
   * @returns The document body   
   */
  getBody: function() {
    return this.getElementsByTagAndClassName('body')[0] 
  },

  /**
   * @returns All the elements that have a specific tag name or class name
   */
  getElementsByTagAndClassName: function(tag_name, class_name, /*optional*/ parent) {
    var class_elements = new Array();
    if(!this.isDefined(parent))
      parent = document;
    if(!this.isDefined(tag_name))
      tag_name = '*';

    var els = parent.getElementsByTagName(tag_name);
    var els_len = els.length;
    var pattern = new RegExp("(^|\\s)" + class_name + "(\\s|$)");

    for (i = 0, j = 0; i < els_len; i++) {
      if ( pattern.test(els[i].className) || class_name == null ) {
        class_elements[j] = els[i];
        j++;
      }
    }
    return class_elements;
  },


////
// DOM manipulation
////
  /**
   * Appends some nodes to a node
   */
  appendChildNodes: function(node/*, nodes...*/) {
    if(arguments.length >= 2) {
      for(var i=1; i < arguments.length; i++) {
        var n = arguments[i];
        if(typeof(n) == "string")
          n = document.createTextNode(n);
        if(this.isDefined(n))
          node.appendChild(n);
      }
    }
    return node;
  },

  /**
   * Replaces a nodes children with another node(s)
   */
  replaceChildNodes: function(node/*, nodes...*/) {
    var child;
    while ((child = node.firstChild)) {
      node.removeChild(child);
    }
    if (arguments.length < 2) {
      return node;
    } else {
      return this.appendChildNodes.apply(this, arguments);
    }
  },

  /**
   * Insert a node after another node
   */
  insertAfter: function(node, referenceNode) {
    referenceNode.parentNode.insertBefore(node, referenceNode.nextSibling);
  },
  
  /**
   * Insert a node before another node
   */
  insertBefore: function(node, referenceNode) {
    referenceNode.parentNode.insertBefore(node, referenceNode);
  },
  
  /**
   * Shows the element
   */
  showElement: function(elm) {
    elm.style.display = '';
  },
  
  /**
   * Hides the element
   */
  hideElement: function(elm) {
    elm.style.display = 'none';
  },

  isElementHidden: function(elm) {
    return elm.style.visibility == "hidden";
  },
  
  /**
   * Swaps one element with another. To delete use swapDOM(elm, null)
   */
  swapDOM: function(dest, src) {
    dest = this.getElement(dest);
    var parent = dest.parentNode;
    if (src) {
      src = this.getElement(src);
      parent.replaceChild(src, dest);
    } else {
      parent.removeChild(dest);
    }
    return src;
  },

  /**
   * Removes an element from the world
   */
  removeElement: function(elm) {
    this.swapDOM(elm, null);
  },

  /**
   * @returns Is an object a dictionary?
   */
  isDict: function(o) {
    var str_repr = String(o);
    return str_repr.indexOf(" Object") != -1;
  },
  
  /**
   * Creates a DOM element
   * @param {String} name The elements DOM name
   * @param {Dict} attrs Attributes sent to the function
   */
  createDOM: function(name, attrs) {
    var i=0;
    elm = document.createElement(name);

    if(this.isDict(attrs[i])) {
      for(k in attrs[0]) {
        if(k == "style")
          elm.style.cssText = attrs[0][k];
        else if(k == "class")
          elm.className = attrs[0][k];
        else
          elm.setAttribute(k, attrs[0][k]);
      }
      i++;
    }

    if(attrs[0] == null)
      i = 1;

    for(i; i < attrs.length; i++) {
      var n = attrs[i];
      if(this.isDefined(n)) {
        if(typeof(n) == "string")
          n = document.createTextNode(n);
        elm.appendChild(n);
      }
    }
    return elm;
  },

  UL: function() { return this.createDOM.apply(this, ["ul", arguments]); },
  LI: function() { return this.createDOM.apply(this, ["li", arguments]); },
  TD: function() { return this.createDOM.apply(this, ["td", arguments]); },
  TR: function() { return this.createDOM.apply(this, ["tr", arguments]); },
  TH: function() { return this.createDOM.apply(this, ["th", arguments]); },
  TBODY: function() { return this.createDOM.apply(this, ["tbody", arguments]); },
  TABLE: function() { return this.createDOM.apply(this, ["table", arguments]); },
  INPUT: function() { return this.createDOM.apply(this, ["input", arguments]); },
  SPAN: function() { return this.createDOM.apply(this, ["span", arguments]); },
  B: function() { return this.createDOM.apply(this, ["b", arguments]); },
  A: function() { return this.createDOM.apply(this, ["a", arguments]); },
  DIV: function() { return this.createDOM.apply(this, ["div", arguments]); },
  IMG: function() { return this.createDOM.apply(this, ["img", arguments]); },
  BUTTON: function() { return this.createDOM.apply(this, ["button", arguments]); },
  H1: function() { return this.createDOM.apply(this, ["h1", arguments]); },
  H2: function() { return this.createDOM.apply(this, ["h2", arguments]); },
  H3: function() { return this.createDOM.apply(this, ["h3", arguments]); },
  BR: function() { return this.createDOM.apply(this, ["br", arguments]); },
  TEXTAREA: function() { return this.createDOM.apply(this, ["textarea", arguments]); },
  FORM: function() { return this.createDOM.apply(this, ["form", arguments]); },
  P: function() { return this.createDOM.apply(this, ["p", arguments]); },
  SELECT: function() { return this.createDOM.apply(this, ["select", arguments]); },
  OPTION: function() { return this.createDOM.apply(this, ["option", arguments]); },
  TN: function(text) { return document.createTextNode(text); },
  IFRAME: function() { return this.createDOM.apply(this, ["iframe", arguments]); },
  SCRIPT: function() { return this.createDOM.apply(this, ["script", arguments]); },

////
// Ajax functions
////
  /**
   * @returns A new XMLHttpRequest object 
   */
  getXMLHttpRequest: function() {
    var try_these = [
      function () { return new XMLHttpRequest(); },
      function () { return new ActiveXObject('Msxml2.XMLHTTP'); },
      function () { return new ActiveXObject('Microsoft.XMLHTTP'); },
      function () { return new ActiveXObject('Msxml2.XMLHTTP.4.0'); },
      function () { throw "Browser does not support XMLHttpRequest"; }
    ];
    for (var i = 0; i < try_these.length; i++) {
      var func = try_these[i];
      try {
        return func();
      } catch (e) {
      }
    }
  },
  
  /**
   * Use this function to do a simple HTTP Request
   */
  doSimpleXMLHttpRequest: function(url) {
    var req = this.getXMLHttpRequest();
    req.open("GET", url, true);
    return this.sendXMLHttpRequest(req);
  },

  getRequest: function(url, data) {
    var req = this.getXMLHttpRequest();
    req.open("POST", url, true);
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    return this.sendXMLHttpRequest(req);
  },

  /**
   * Send a XMLHttpRequest
   */
  sendXMLHttpRequest: function(req, data) {
    var d = new AJSDeferred(req);

    var onreadystatechange = function () {
      if (req.readyState == 4) {
        try {
          status = req.status;
        }
        catch(e) {};
        if(status == 200 || status == 304 || req.responseText == null) {
          d.callback(req, data);
        }
        else {
          d.errback();
        }
      }
    }
    req.onreadystatechange = onreadystatechange;
    return d;
  },
  
  /**
   * Represent an object as a string
   */
  reprString: function(o) {
    return ('"' + o.replace(/(["\\])/g, '\\$1') + '"'
    ).replace(/[\f]/g, "\\f"
    ).replace(/[\b]/g, "\\b"
    ).replace(/[\n]/g, "\\n"
    ).replace(/[\t]/g, "\\t"
    ).replace(/[\r]/g, "\\r");
  },
  
  /**
   * Serialize an object to JSON notation
   */
  serializeJSON: function(o) {
    var objtype = typeof(o);
    if (objtype == "undefined") {
      return "undefined";
    } else if (objtype == "number" || objtype == "boolean") {
      return o + "";
    } else if (o === null) {
      return "null";
    }
    if (objtype == "string") {
      return this.reprString(o);
    }
    var me = arguments.callee;
    var newObj;
    if (typeof(o.__json__) == "function") {
      newObj = o.__json__();
      if (o !== newObj) {
        return me(newObj);
      }
    }
    if (typeof(o.json) == "function") {
      newObj = o.json();
      if (o !== newObj) {
        return me(newObj);
      }
    }
    if (objtype != "function" && typeof(o.length) == "number") {
      var res = [];
      for (var i = 0; i < o.length; i++) {
        var val = me(o[i]);
        if (typeof(val) != "string") {
          val = "undefined";
        }
        res.push(val);
      }
      return "[" + res.join(",") + "]";
    }
    res = [];
    for (var k in o) {
      var useKey;
      if (typeof(k) == "number") {
        useKey = '"' + k + '"';
      } else if (typeof(k) == "string") {
        useKey = this.reprString(k);
      } else {
        // skip non-string or number keys
        continue;
      }
      val = me(o[k]);
      if (typeof(val) != "string") {
        // skip non-serializable values
        continue;
      }
      res.push(useKey + ":" + val);
    }
    return "{" + res.join(",") + "}";
  },

  /**
   * Send and recive JSON using GET
   */
  loadJSONDoc: function(url) {
    var d = this.getRequest(url);
    var eval_req = function(req) {
      var text = req.responseText;
      return eval('(' + text + ')');
    };
    d.addCallback(eval_req);
    return d;
  },
  
  
////
// Misc.
////
  /**
   * Alert the objects key attrs 
   */
  keys: function(obj) {
    var rval = [];
    for (var prop in obj) {
      rval.push(prop);
    }
    return rval;
  },

  urlencode: function(str) {
    return encodeURIComponent(str.toString());
  },

  /**
   * @returns True if the object is defined, otherwise false
   */
  isDefined: function(o) {
    return (o != "undefined" && o != null)
  },
  
  /**
   * @returns True if an object is a array, false otherwise
   */
  isArray: function(obj) {
    try { return (typeof(obj.length) == "undefined") ? false : true; }
    catch(e)
    { return false; }
  },

  isObject: function(obj) {
    return (obj && typeof obj == 'object');
  },

  /**
   * Export DOM elements to the global namespace
   */
  exportDOMElements: function() {
    UL = this.UL;
    LI = this.LI;
    TD = this.TD;
    TR = this.TR;
    TH = this.TH;
    TBODY = this.TBODY;
    TABLE = this.TABLE;
    INPUT = this.INPUT;
    SPAN = this.SPAN;
    B = this.B;
    A = this.A;
    DIV = this.DIV;
    IMG = this.IMG;
    BUTTON = this.BUTTON;
    H1 = this.H1;
    H2 = this.H2;
    H3 = this.H3;
    BR = this.BR;
    TEXTAREA = this.TEXTAREA;
    FORM = this.FORM;
    P = this.P;
    SELECT = this.SELECT;
    OPTION = this.OPTION;
    TN = this.TN;
    IFRAME = this.IFRAME;
    SCRIPT = this.SCRIPT;
  },

  /**
   * Export AmiJS functions to the global namespace
   */
  exportToGlobalScope: function() {
    getElement = this.getElement;
    getQueryArgument = this.getQueryArgument;
    isIe = this.isIe;
    $ = this.getElement;
    getElements = this.getElements;
    getBody = this.getBody;
    getElementsByTagAndClassName = this.getElementsByTagAndClassName;
    appendChildNodes = this.appendChildNodes;
    ACN = appendChildNodes;
    replaceChildNodes = this.replaceChildNodes;
    RCN = replaceChildNodes;
    insertAfter = this.insertAfter;
    insertBefore = this.insertBefore;
    showElement = this.showElement;
    hideElement = this.hideElement;
    isElementHidden = this.isElementHidden;
    swapDOM = this.swapDOM;
    removeElement = this.removeElement;
    isDict = this.isDict;
    createDOM = this.createDOM;
    this.exportDOMElements();
    getXMLHttpRequest = this.getXMLHttpRequest;
    doSimpleXMLHttpRequest = this.doSimpleXMLHttpRequest;
    getRequest = this.getRequest;
    sendXMLHttpRequest = this.sendXMLHttpRequest;
    reprString = this.reprString;
    serializeJSON = this.serializeJSON;
    loadJSONDoc = this.loadJSONDoc;
    keys = this.keys;
    isDefined = this.isDefined;
    isArray = this.isArray;
  }
}



AJSDeferred = function(req) {
  this.callbacks = [];
  this.req = req;

  this.callback = function (res) {
    while (this.callbacks.length > 0) {
      var fn = this.callbacks.pop();
      res = fn(res);
    }
  };

  this.errback = function(e){
    alert("Error encountered:\n" + e);
  };

  this.addErrback = function(fn) {
    this.errback = fn;
  };

  this.addCallback = function(fn) {
    this.callbacks.unshift(fn);
  };

  this.addCallbacks = function(fn1, fn2) {
    this.addCallback(fn1);
    this.addErrback(fn2);
  };

  this.sendReq = function(data) {
    if(AJS.isObject(data)) {
      var post_data = [];
      for(k in data) {
        post_data.push(k + "=" + AJS.urlencode(data[k]));
      }
      post_data = post_data.join("&");
      this.req.send(post_data);
    }
    else if(AJS.isDefined(data))
      this.req.send(data);
    else {
      this.req.send("");
    }
  };
};
AJSDeferred.prototype = new AJSDeferred();






/****
Last Modified: 28/04/06 15:26:06

 GoogieSpell
   Google spell checker for your own web-apps :)
   Copyright Amir Salihefendic 2006
 LICENSE
  GPL (see gpl.txt for more information)
  This basically means that you can't use this script with/in proprietary software!
  There is another license that permits you to use this script with proprietary software. Check out:... for more info.
  AUTHOR
   4mir Salihefendic (http://amix.dk) - amix@amix.dk
 VERSION
	 3.22
****/
var GOOGIE_CUR_LANG = "en";

function GoogieSpell(img_dir, server_url) {
  var cookie_value;
  var lang;
  cookie_value = getCookie('language');

  if(cookie_value != null)
    GOOGIE_CUR_LANG = cookie_value;

  this.img_dir = img_dir;
  this.server_url = server_url;

  this.lang_to_word = {"da": "Dansk", "de": "Deutsch", "en": "English",
                       "es": "Espa&#241;ol", "fr": "Fran&#231;ais", "it": "Italiano", 
                       "nl": "Nederlands", "pl": "Polski", "pt": "Portugu&#234;s",
                       "fi": "Suomi", "sv": "Svenska"};
  this.langlist_codes = AJS.keys(this.lang_to_word);

  this.show_change_lang_pic = true;

  this.lang_state_observer = null;

  this.spelling_state_observer = null;

  this.request = null;
  this.error_window = null;
  this.language_window = null;
  this.edit_layer = null;
  this.orginal_text = null;
  this.results = null;
  this.text_area = null;
  this.gselm = null;
  this.ta_scroll_top = 0;
  this.el_scroll_top = 0;

  this.lang_chck_spell = "Check spelling";
  this.lang_rsm_edt = "Resume editing";
  this.lang_close = "Close";
  this.lang_no_error_found = "No spelling errors found";
  this.lang_revert = "Revert to";
  this.show_spell_img = false;  // modified by roundcube
}

GoogieSpell.prototype.setStateChanged = function(current_state) {
  if(this.spelling_state_observer != null)
    this.spelling_state_observer(current_state);
}

GoogieSpell.item_onmouseover = function(e) {
  var elm = GoogieSpell.getEventElm(e);
  if(elm.className != "googie_list_close" && elm.className != "googie_list_revert")
    elm.className = "googie_list_onhover";
  else
    elm.parentNode.className = "googie_list_onhover";
}

GoogieSpell.item_onmouseout = function(e) {
  var elm = GoogieSpell.getEventElm(e);
  if(elm.className != "googie_list_close" && elm.className != "googie_list_revert")
    elm.className = "googie_list_onout";
  else
    elm.parentNode.className = "googie_list_onout";
}

GoogieSpell.prototype.getGoogleUrl = function() {
  return this.server_url + GOOGIE_CUR_LANG;
}

GoogieSpell.prototype.spellCheck = function(elm, name) {
  this.ta_scroll_top = this.text_area.scrollTop;

  this.appendIndicator(elm);

  try {
    this.hideLangWindow();
  }
  catch(e) {}
  
  this.gselm = elm;

  this.createEditLayer(this.text_area.offsetWidth, this.text_area.offsetHeight);

  this.createErrorWindow();
  AJS.getBody().appendChild(this.error_window);

  try { netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead"); } 
  catch (e) { }

  this.gselm.onclick = null;

  this.orginal_text = this.text_area.value;
  var me = this;

  //Create request
  var d = AJS.getRequest(this.getGoogleUrl());
  var reqdone = function(req) {
    var r_text = req.responseText;
    if(r_text.match(/<c.*>/) != null) {
      var results = GoogieSpell.parseResult(r_text);
      //Before parsing be sure that errors were found
      me.results = results;
      me.showErrorsInIframe(results);
      me.resumeEditingState();
    }
    else {
      me.flashNoSpellingErrorState();
    }
    me.removeIndicator();
  };

  var reqfailed = function(req) {
    alert("An error was encountered on the server. Please try again later.");
    AJS.removeElement(me.gselm);
    me.checkSpellingState();
    me.removeIndicator();
  };
  
  d.addCallback(reqdone);
  d.addErrback(reqfailed);

  var req_text = GoogieSpell.escapeSepcial(this.orginal_text);
  d.sendReq(GoogieSpell.createXMLReq(req_text));
}

GoogieSpell.escapeSepcial = function(val) {
  return val.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

GoogieSpell.createXMLReq = function (text) {
  return '<?xml version="1.0" encoding="utf-8" ?><spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1"><text>' + text + '</text></spellrequest>';
}

//Retunrs an array
//result[item] -> ['attrs']
//                ['suggestions']
GoogieSpell.parseResult = function(r_text) {
  var re_split_attr_c = /\w="\d+"/g;
  var re_split_text = /\t/g;

  var matched_c = r_text.match(/<c[^>]*>[^<]*<\/c>/g);
  var results = new Array();
  
  for(var i=0; i < matched_c.length; i++) {
    var item = new Array();

    //Get attributes
    item['attrs'] = new Array();
    var split_c = matched_c[i].match(re_split_attr_c);
    for(var j=0; j < split_c.length; j++) {
      var c_attr = split_c[j].split(/=/);
      item['attrs'][c_attr[0]] = parseInt(c_attr[1].replace('"', ''));
    }

    //Get suggestions
    item['suggestions'] = new Array();
    var only_text = matched_c[i].replace(/<[^>]*>/g, "");
    var split_t = only_text.split(re_split_text);
    for(var k=0; k < split_t.length; k++) {
    if(split_t[k] != "")
      item['suggestions'].push(split_t[k]);
    }
    results.push(item);
  }
  return results;
}

/****
 Error window (the drop-down window)
****/
GoogieSpell.prototype.createErrorWindow = function() {
  this.error_window = AJS.DIV();
  this.error_window.className = "googie_window";
}

GoogieSpell.prototype.hideErrorWindow = function() {
  this.error_window.style.visibility = "hidden";
}

GoogieSpell.prototype.updateOrginalText = function(offset, old_value, new_value, id) {
  var part_1 = this.orginal_text.substring(0, offset);
  var part_2 = this.orginal_text.substring(offset+old_value.length);
  this.orginal_text = part_1 + new_value + part_2;
  var add_2_offset = new_value.length - old_value.length;
  for(var j=0; j < this.results.length; j++) {
    //Don't edit the offset of the current item
    if(j != id && j > id){
      this.results[j]['attrs']['o'] += add_2_offset;
    }
  }
}

GoogieSpell.prototype.saveOldValue = function (id, old_value) {
  this.results[id]['is_changed'] = true;
  this.results[id]['old_value'] = old_value;
}

GoogieSpell.prototype.showErrorWindow = function(elm, id) {
  var me = this;

  var abs_pos = GoogieSpell.absolutePosition(elm);
  abs_pos.y -= this.edit_layer.scrollTop;
  this.error_window.style.visibility = "visible";
  this.error_window.style.top = (abs_pos.y+20) + "px";
  this.error_window.style.left = (abs_pos.x) + "px";
  this.error_window.innerHTML = "";

  //Build up the result list
  var table = AJS.TABLE({'class': 'googie_list'});
  var list = AJS.TBODY();

  var suggestions = this.results[id]['suggestions'];
  var offset = this.results[id]['attrs']['o'];
  var len = this.results[id]['attrs']['l'];

  if(suggestions.length == 0) {
    var row = AJS.TR();
    var item = AJS.TD();
    var dummy = AJS.SPAN();
    item.appendChild(AJS.TN("No suggestions :("));
    row.appendChild(item);
    list.appendChild(row);
  }

  for(i=0; i < suggestions.length; i++) {
    var row = AJS.TR();
    var item = AJS.TD();
    var dummy = AJS.SPAN();
    dummy.innerHTML = suggestions[i];
    item.appendChild(AJS.TN(dummy.innerHTML));
    
    item.onclick = function(e) {
      var l_elm = GoogieSpell.getEventElm(e);
      var old_value = elm.innerHTML;
      var new_value = l_elm.innerHTML;

      elm.style.color = "green";
      elm.innerHTML = l_elm.innerHTML;
      me.hideErrorWindow();

      me.updateOrginalText(offset, old_value, new_value, id);

      //Update to the new length
      me.results[id]['attrs']['l'] = new_value.length;
      me.saveOldValue(id, old_value);
    };
    item.onmouseover = GoogieSpell.item_onmouseover;
    item.onmouseout = GoogieSpell.item_onmouseout;
    row.appendChild(item);
    list.appendChild(row);
  }
  
  //The element is changed, append the revert
  if(this.results[id]['is_changed']) {
    var old_value = this.results[id]['old_value'];
    var offset = this.results[id]['attrs']['o'];
    var revert_row = AJS.TR();
    var revert = AJS.TD();

    revert.onmouseover = GoogieSpell.item_onmouseover;
    revert.onmouseout = GoogieSpell.item_onmouseout;
    var rev_span = AJS.SPAN({'class': 'googie_list_revert'});
    rev_span.innerHTML = this.lang_revert + " " + old_value;
    revert.appendChild(rev_span);

    revert.onclick = function(e) { 
      me.updateOrginalText(offset, elm.innerHTML, old_value, id);
      elm.style.color = "#b91414";
      elm.innerHTML = old_value;
      me.hideErrorWindow();
    };

    revert_row.appendChild(revert);
    list.appendChild(revert_row);
  }

  //Append the edit box
  var edit_row = AJS.TR();
  var edit = AJS.TD();

  var edit_input = AJS.INPUT({'style': 'width: 120px; margin:0; padding:0'});

  var onsub = function () {
    if(edit_input.value != "") {
      me.saveOldValue(id, elm.innerHTML);
      me.updateOrginalText(offset, elm.innerHTML, edit_input.value, id);
      elm.style.color = "green"
      elm.innerHTML = edit_input.value;
      
      me.hideErrorWindow();
      return false;
    }
  };
  
  var ok_pic = AJS.IMG({'src': this.img_dir + "ok.gif", 'style': 'width: 32px; height: 16px; margin-left: 2px; margin-right: 2px;'});
  var edit_form = AJS.FORM({'style': 'margin: 0; padding: 0'}, edit_input, ok_pic);
  ok_pic.onclick = onsub;
  edit_form.onsubmit = onsub;
  
  edit.appendChild(edit_form);
  edit_row.appendChild(edit);
  list.appendChild(edit_row);

  //Close button
  var close_row = AJS.TR();
  var close = AJS.TD();

  close.onmouseover = GoogieSpell.item_onmouseover;
  close.onmouseout = GoogieSpell.item_onmouseout;

  var spn_close = AJS.SPAN({'class': 'googie_list_close'});
  spn_close.innerHTML = this.lang_close;
  close.appendChild(spn_close);
  close.onclick = function() { me.hideErrorWindow()};
  close_row.appendChild(close);
  list.appendChild(close_row);

  table.appendChild(list);
  this.error_window.appendChild(table);
}


/****
  Edit layer (the layer where the suggestions are stored)
****/
GoogieSpell.prototype.createEditLayer = function(width, height) {
  this.edit_layer = AJS.DIV({'class': 'googie_edit_layer'});
  
  //Set the style so it looks like edit areas
  this.edit_layer.className = this.text_area.className;
  this.edit_layer.style.border = "1px solid #999";
  this.edit_layer.style.overflow = "auto";
  this.edit_layer.style.backgroundColor = "#F1EDFE";
  this.edit_layer.style.padding = "3px";

  this.edit_layer.style.width = (width-8) + "px";
  this.edit_layer.style.height = height + "px";
}

GoogieSpell.prototype.resumeEditing = function(e, me) {
  this.setStateChanged("check_spelling");
  me.switch_lan_pic.style.display = "inline";

  this.el_scroll_top = me.edit_layer.scrollTop;

  var elm = GoogieSpell.getEventElm(e);
  AJS.replaceChildNodes(elm, this.createSpellDiv());

  elm.onclick = function(e) {
    me.spellCheck(elm, me.text_area.id);
  };
  me.hideErrorWindow();

  //Remove the EDIT_LAYER
  me.edit_layer.parentNode.removeChild(me.edit_layer);

  me.text_area.value = me.orginal_text;
  AJS.showElement(me.text_area);
  me.gselm.className = "googie_no_style";

  me.text_area.scrollTop = this.el_scroll_top;

  elm.onmouseout = null;
}

GoogieSpell.prototype.createErrorLink = function(text, id) {
  var elm = AJS.SPAN({'class': 'googie_link'});
  var me = this;
  elm.onclick = function () {
    me.showErrorWindow(elm, id);
  };
  elm.innerHTML = text;
  return elm;
}

GoogieSpell.createPart = function(txt_part) {
  if(txt_part == " ")
    return AJS.TN(" ");
  var result = AJS.SPAN();

  var is_first = true;
  var is_safari = (navigator.userAgent.toLowerCase().indexOf("safari") != -1);

  var part = AJS.SPAN();
  txt_part = GoogieSpell.escapeSepcial(txt_part);
  txt_part = txt_part.replace(/\n/g, "<br>");
  txt_part = txt_part.replace(/  /g, " &nbsp;");
  txt_part = txt_part.replace(/^ /g, "&nbsp;");
  txt_part = txt_part.replace(/ $/g, "&nbsp;");
  
  part.innerHTML = txt_part;

  return part;
}

GoogieSpell.prototype.showErrorsInIframe = function(results) {
  var output = AJS.DIV();
  output.style.textAlign = "left";
  var pointer = 0;
  for(var i=0; i < results.length; i++) {
    var offset = results[i]['attrs']['o'];
    var len = results[i]['attrs']['l'];
    
    var part_1_text = this.orginal_text.substring(pointer, offset);
    var part_1 = GoogieSpell.createPart(part_1_text);
    output.appendChild(part_1);
    pointer += offset - pointer;
    
    //If the last child was an error, then insert some space
    output.appendChild(this.createErrorLink(this.orginal_text.substr(offset, len), i));
    pointer += len;
  }
  //Insert the rest of the orginal text
  var part_2_text = this.orginal_text.substr(pointer, this.orginal_text.length);

  var part_2 = GoogieSpell.createPart(part_2_text);
  output.appendChild(part_2);

  this.edit_layer.appendChild(output);

  //Hide text area
  AJS.hideElement(this.text_area);
  this.text_area.parentNode.insertBefore(this.edit_layer, this.text_area.nextSibling);
  this.edit_layer.scrollTop = this.ta_scroll_top;
}

GoogieSpell.Position = function(x, y) {
  this.x = x;
  this.y = y;
}	

//Get the absolute position of menu_slide
GoogieSpell.absolutePosition = function(element) {
  //Create a new object that has elements y and x pos...
  var posObj = new GoogieSpell.Position(element.offsetLeft, element.offsetTop);

  //Check if the element has an offsetParent - if it has .. loop until it has not
  if(element.offsetParent) {
    var temp_pos =	GoogieSpell.absolutePosition(element.offsetParent);
    posObj.x += temp_pos.x;
    posObj.y += temp_pos.y;
  }
  return posObj;
}

GoogieSpell.getEventElm = function(e) {
	var targ;
	if (!e) var e = window.event;
	if (e.target) targ = e.target;
	else if (e.srcElement) targ = e.srcElement;
	if (targ.nodeType == 3) // defeat Safari bug
		targ = targ.parentNode;
  return targ;
}

GoogieSpell.prototype.removeIndicator = function(elm) {
  // modified by roundcube
  if (window.rcube_webmail_client)
    rcube_webmail_client.set_busy(false);
  //AJS.removeElement(this.indicator);
}

GoogieSpell.prototype.appendIndicator = function(elm) {
  // modified by roundcube
  if (window.rcube_webmail_client)
    rcube_webmail_client.set_busy(true, 'checking');
/*
  var img = AJS.IMG({'src': this.img_dir + 'indicator.gif', 'style': 'margin-right: 5px;'});
  img.style.width = "16px";
  img.style.height = "16px";
  this.indicator = img;
  img.style.textDecoration = "none";
  AJS.insertBefore(img, elm);
  */
}

/****
 Choose language
****/
GoogieSpell.prototype.createLangWindow = function() {
  this.language_window = AJS.DIV({'class': 'googie_window'});
  this.language_window.style.width = "130px";

  //Build up the result list
  var table = AJS.TABLE({'class': 'googie_list'});
  var list = AJS.TBODY();

  this.lang_elms = new Array();

  for(i=0; i < this.langlist_codes.length; i++) {
    var row = AJS.TR();
    var item = AJS.TD();
    item.googieId = this.langlist_codes[i];
    this.lang_elms.push(item);
    var lang_span = AJS.SPAN();
    lang_span.innerHTML = this.lang_to_word[this.langlist_codes[i]];
    item.appendChild(AJS.TN(lang_span.innerHTML));

    var me = this;
    
    item.onclick = function(e) {
      var elm = GoogieSpell.getEventElm(e);
      me.deHighlightCurSel();

      me.setCurrentLanguage(elm.googieId);

      if(me.lang_state_observer != null) {
        me.lang_state_observer();
      }

      me.highlightCurSel();
      me.hideLangWindow();
    };

    item.onmouseover = function(e) { 
      var i_it = GoogieSpell.getEventElm(e);
      if(i_it.className != "googie_list_selected")
        i_it.className = "googie_list_onhover";
    };
    item.onmouseout = function(e) { 
      var i_it = GoogieSpell.getEventElm(e);
      if(i_it.className != "googie_list_selected")
        i_it.className = "googie_list_onout"; 
    };

    row.appendChild(item);
    list.appendChild(row);
  }

  this.highlightCurSel();

  //Close button
  var close_row = AJS.TR();
  var close = AJS.TD();
  close.onmouseover = GoogieSpell.item_onmouseover;
  close.onmouseout = GoogieSpell.item_onmouseout;
  var spn_close = AJS.SPAN({'class': 'googie_list_close'});
  spn_close.innerHTML = this.lang_close;
  close.appendChild(spn_close);
  var me = this;
  close.onclick = function(e) {
    me.hideLangWindow(); GoogieSpell.item_onmouseout(e);
  };
  close_row.appendChild(close);
  list.appendChild(close_row);

  table.appendChild(list);
  this.language_window.appendChild(table);
}

GoogieSpell.prototype.setCurrentLanguage = function(lan_code) {
  GOOGIE_CUR_LANG = lan_code;

  //Set cookie
  var now = new Date();
  now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);
  setCookie('language', lan_code, now);
}

GoogieSpell.prototype.hideLangWindow = function() {
  this.language_window.style.visibility = "hidden";
  this.switch_lan_pic.className = "googie_lang_3d_on";
}

GoogieSpell.prototype.deHighlightCurSel = function() {
  this.lang_cur_elm.className = "googie_list_onout";
}

GoogieSpell.prototype.highlightCurSel = function() {
  for(var i=0; i < this.lang_elms.length; i++) {
    if(this.lang_elms[i].googieId == GOOGIE_CUR_LANG) {
      this.lang_elms[i].className = "googie_list_selected";
      this.lang_cur_elm = this.lang_elms[i];
    }
    else {
      this.lang_elms[i].className = "googie_list_onout";
    }
  }
}

GoogieSpell.prototype.showLangWindow = function(elm, ofst_top, ofst_left) {
  if(!AJS.isDefined(ofst_top))
    ofst_top = 20;
  if(!AJS.isDefined(ofst_left))
    ofst_left = 50;

  this.createLangWindow();
  AJS.getBody().appendChild(this.language_window);

  var abs_pos = GoogieSpell.absolutePosition(elm);
  AJS.showElement(this.language_window);
  this.language_window.style.top = (abs_pos.y+ofst_top) + "px";
  this.language_window.style.left = (abs_pos.x+ofst_left-this.language_window.offsetWidth) + "px";
  this.highlightCurSel();
  this.language_window.style.visibility = "visible";
}

GoogieSpell.prototype.flashNoSpellingErrorState = function() {
  this.setStateChanged("no_error_found");
  var me = this;
  AJS.hideElement(this.switch_lan_pic);
  this.gselm.innerHTML = this.lang_no_error_found;
  this.gselm.className = "googie_check_spelling_ok";
  this.gselm.style.textDecoration = "none";
  this.gselm.style.cursor = "default";
  var fu = function() {
    AJS.removeElement(me.gselm);
    me.checkSpellingState();
  };
  setTimeout(fu, 1000);
}

GoogieSpell.prototype.resumeEditingState = function() {
  this.setStateChanged("resume_editing");
  var me = this;
  AJS.hideElement(me.switch_lan_pic);

  //Change link text to resume
  me.gselm.innerHTML = this.lang_rsm_edt;
  me.gselm.onclick = function(e) {
    me.resumeEditing(e, me);
  }
  me.gselm.className = "googie_check_spelling_ok";
  me.edit_layer.scrollTop = me.ta_scroll_top;
}

GoogieSpell.prototype.createChangeLangPic = function() {
  var switch_lan = AJS.A({'class': 'googie_lang_3d_on', 'style': 'padding-left: 6px;'}, AJS.IMG({'src': this.img_dir + 'change_lang.gif', 'alt': "Change language"}));
  switch_lan.onmouseover = function() {
    if(this.className != "googie_lang_3d_click")
      this.className = "googie_lang_3d_on";
  }

  var me = this;
  switch_lan.onclick = function() {
    if(this.className == "googie_lang_3d_click") {
      me.hideLangWindow();
    }
    else {
      me.showLangWindow(switch_lan);
      this.className = "googie_lang_3d_click";
    }
  }
  return switch_lan;
}

GoogieSpell.prototype.createSpellDiv = function() {
  var chk_spell = AJS.SPAN({'class': 'googie_check_spelling_link'});
  chk_spell.innerHTML = this.lang_chck_spell;
  var spell_img = null;
  if(this.show_spell_img)
    spell_img = AJS.IMG({'src': this.img_dir + "spellc.gif"});
  return AJS.SPAN(spell_img, " ", chk_spell);
}

GoogieSpell.prototype.checkSpellingState = function() {
  this.setStateChanged("check_spelling");
  var me = this;
  if(this.show_change_lang_pic)
    this.switch_lan_pic = this.createChangeLangPic();
  else
    this.switch_lan_pic = AJS.SPAN();

  var span_chck = this.createSpellDiv();
  span_chck.onclick = function() {
    me.spellCheck(span_chck);
  }
  AJS.appendChildNodes(this.spell_container, span_chck, " ", this.switch_lan_pic);
  // modified by roundcube
  this.check_link = span_chck;
}

GoogieSpell.prototype.setLanguages = function(lang_dict) {
  this.lang_to_word = lang_dict;
  this.langlist_codes = AJS.keys(lang_dict);
}

GoogieSpell.prototype.decorateTextarea = function(id, /*optional*/spell_container_id, force_width) {
  var me = this;
  
  if(typeof(id) == "string")
    this.text_area = AJS.getElement(id);
  else
    this.text_area = id;

  var r_width;

  if(this.text_area != null) {
    if(AJS.isDefined(spell_container_id)) {
      if(typeof(spell_container_id) == "string")
        this.spell_container = AJS.getElement(spell_container_id);
      else
        this.spell_container = spell_container_id;
    }
    else {
      var table = AJS.TABLE();
      var tbody = AJS.TBODY();
      var tr = AJS.TR();
      if(AJS.isDefined(force_width)) {
        r_width = force_width;
      }
      else {
        r_width = this.text_area.offsetWidth + "px";
      }

      var spell_container = AJS.TD();
      this.spell_container = spell_container;

      tr.appendChild(spell_container);

      tbody.appendChild(tr);
      table.appendChild(tbody);

      AJS.insertBefore(table, this.text_area);

      //Set width
      table.style.width = '100%';  // modified by roundcube (old: r_width)
      spell_container.style.width = r_width;
      spell_container.style.textAlign = "right";
    }

    this.checkSpellingState();
  }
  else {
    alert("Text area not found");
  }
}
