/*
Last Modified: 29/04/07 18:44:48

AJS JavaScript library
    A very small library with a lot of functionality
AUTHOR
    4mir Salihefendic (http://amix.dk) - amix@amix.dk
LICENSE
    Copyright (c) 2006 Amir Salihefendic. All rights reserved.
    Copyright (c) 2005 Bob Ippolito. All rights reserved.
    http://www.opensource.org/licenses/mit-license.php
VERSION
    4.0
SITE
    http://orangoo.com/AmiNation/AJS
**/
if(!AJS) {
var AJS = {
    BASE_URL: "",

    drag_obj: null,
    drag_elm: null,
    _drop_zones: [],
    _drag_zones: [],
    _cur_pos: null,

    ajaxErrorHandler: null,

////
// General accessor functions
////
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

    isIe: function() {
        return (navigator.userAgent.toLowerCase().indexOf("msie") != -1 && navigator.userAgent.toLowerCase().indexOf("opera") == -1);
    },
    isNetscape7: function() {
        return (navigator.userAgent.toLowerCase().indexOf("netscape") != -1 && navigator.userAgent.toLowerCase().indexOf("7.") != -1);
    },
    isSafari: function() {
        return (navigator.userAgent.toLowerCase().indexOf("khtml") != -1);
    },
    isOpera: function() {
        return (navigator.userAgent.toLowerCase().indexOf("opera") != -1);
    },
    isMozilla: function() {
        return (navigator.userAgent.toLowerCase().indexOf("gecko") != -1 && navigator.productSub >= 20030210);
    },
    isMac: function() {
        return (navigator.userAgent.toLowerCase().indexOf('macintosh') != -1);
    },


////
// Array functions
////
    //Shortcut: AJS.$A
    createArray: function(v) {
        if(AJS.isArray(v) && !AJS.isString(v))
            return v;
        else if(!v)
            return [];
        else
            return [v];
    },

    forceArray: function(args) {
        var r = [];
        AJS.map(args, function(elm) {
            r.push(elm);
        });
        return r;
    },

    join: function(delim, list) {
        try {
            return list.join(delim);
        }
        catch(e) {
            var r = list[0] || '';
            AJS.map(list, function(elm) {
                r += delim + elm;
            }, 1);
            return r + '';
        }
    },

    isIn: function(elm, list) {
        var i = AJS.getIndex(elm, list);
        if(i != -1)
            return true;
        else
            return false;
    },

    getIndex: function(elm, list/*optional*/, eval_fn) {
        for(var i=0; i < list.length; i++)
            if(eval_fn && eval_fn(list[i]) || elm == list[i])
                return i;
        return -1;
    },

    getFirst: function(list) {
        if(list.length > 0)
            return list[0];
        else
            return null;
    },

    getLast: function(list) {
        if(list.length > 0)
            return list[list.length-1];
        else
            return null;
    },

    update: function(l1, l2) {
        for(var i in l2)
            l1[i] = l2[i];
        return l1;
    },

    flattenList: function(list) {
        var r = [];
        var _flatten = function(r, l) {
            AJS.map(l, function(o) {
                if(o == null) {}
                else if (AJS.isArray(o))
                    _flatten(r, o);
                else
                    r.push(o);
            });
        }
        _flatten(r, list);
        return r;
    },


////
// Functional programming
////
    map: function(list, fn,/*optional*/ start_index, end_index) {
        var i = 0, l = list.length;
        if(start_index)
             i = start_index;
        if(end_index)
             l = end_index;
        for(i; i < l; i++) {
            var val = fn.apply(null, [list[i], i]);
            if(val != undefined)
                return val;
        }
    },

    rmap: function(list, fn) {
        var i = list.length-1, l = 0;
        for(i; i >= l; i--) {
            var val = fn.apply(null, [list[i], i]);
            if(val != undefined)
                return val;
        }
    },

    filter: function(list, fn, /*optional*/ start_index, end_index) {
        var r = [];
        AJS.map(list, function(elm) {
            if(fn(elm))
                r.push(elm);
        }, start_index, end_index);
        return r;
    },

    partial: function(fn) {
        var args = AJS.$FA(arguments);
        args.shift();
        return function() {
            args = args.concat(AJS.$FA(arguments));
            return fn.apply(window, args);
        }
    },


////
// DOM functions
////
    //Shortcut: AJS.$
    getElement: function(id) {
        if(AJS.isString(id) || AJS.isNumber(id))
            return document.getElementById(id);
        else
            return id;
    },

    //Shortcut: AJS.$$
    getElements: function(/*id1, id2, id3*/) {
        var args = AJS.forceArray(arguments);
        var elements = new Array();
            for (var i = 0; i < args.length; i++) {
                var element = AJS.getElement(args[i]);
                elements.push(element);
            }
            return elements;
    },

    //Shortcut: AJS.$bytc
    getElementsByTagAndClassName: function(tag_name, class_name, /*optional*/ parent) {
        var class_elements = [];
        if(!AJS.isDefined(parent))
            parent = document;
        if(!AJS.isDefined(tag_name))
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

    _nodeWalk: function(elm, tag_name, class_name, fn_next_elm) {
        var p = fn_next_elm(elm);

        var checkFn;
        if(tag_name && class_name) {
            checkFn = function(p) {
                return AJS.nodeName(p) == tag_name && AJS.hasClass(p, class_name);
            }
        }
        else if(tag_name) {
            checkFn = function(p) { return AJS.nodeName(p) == tag_name; }
        }
        else {
            checkFn = function(p) { return AJS.hasClass(p, class_name); }
        }

        while(p) {
            if(checkFn(p))
                return p;
            p = fn_next_elm(p);
        }
        return null;
    },

    getParentBytc: function(elm, tag_name, class_name) {
        return AJS._nodeWalk(elm, tag_name, class_name, function(m) { return m.parentNode; });
    },

    getPreviousSiblingBytc: function(elm, tag_name, class_name) {
        return AJS._nodeWalk(elm, tag_name, class_name, function(m) { return m.previousSibling; });
    },

    getNextSiblingBytc: function(elm, tag_name, class_name) {
        return AJS._nodeWalk(elm, tag_name, class_name, function(m) { return m.nextSibling; });
    },

    //Shortcut: AJS.$f
    getFormElement: function(form, name) {
        form = AJS.$(form);
        var r = null;
        AJS.map(form.elements, function(elm) {
            if(elm.name && elm.name == name)
                r = elm;
        });
        return r;
    },

    formContents: function(form) {
        var form = AJS.$(form);
        var r = {};
        var fn = function(elms) {
            AJS.map(elms, function(e) {
                if(e.name)
                    r[e.name] = e.value || '';
            });
        }
        fn(AJS.$bytc('input', null, form));
        fn(AJS.$bytc('textarea', null, form));
        return r;
    },

    getBody: function() {
        return AJS.$bytc('body')[0]
    },

    nodeName: function(elm) {
        return elm.nodeName.toLowerCase();
    },

    hasParent: function(elm, parent_to_consider, max_look_up) {
        if(elm == parent_to_consider)
            return true;
        if(max_look_up == 0)
            return false;
        return AJS.hasParent(elm.parentNode, parent_to_consider, max_look_up-1);
    },

    isElementHidden: function(elm) {
        return ((elm.style.display == "none") || (elm.style.visibility == "hidden"));
    },

    //Shortcut: AJS.DI
    documentInsert: function(elm) {
        if(typeof(elm) == 'string')
            elm = AJS.HTML2DOM(elm);
        document.write('<span id="dummy_holder"></span>');
        AJS.swapDOM(AJS.$('dummy_holder'), elm);
    },

    cloner: function(element) {
        return function() {
            return element.cloneNode(true);
        }
    },

    appendToTop: function(elm/*, elms...*/) {
        var args = AJS.forceArray(arguments).slice(1);
        if(args.length >= 1) {
            var first_child = elm.firstChild;
            if(first_child) {
                while(true) {
                    var t_elm = args.shift();
                    if(t_elm)
                        AJS.insertBefore(t_elm, first_child);
                    else
                        break;
                }
            }
            else {
                AJS.ACN.apply(null, arguments);
            }
        }
        return elm;
    },

    //Shortcut: AJS.ACN
    appendChildNodes: function(elm/*, elms...*/) {
        if(arguments.length >= 2) {
            AJS.map(arguments, function(n) {
                if(AJS.isString(n))
                    n = AJS.TN(n);
                if(AJS.isDefined(n))
                    elm.appendChild(n);
            }, 1);
        }
        return elm;
    },

    //Shortcut: AJS.RCN
    replaceChildNodes: function(elm/*, elms...*/) {
        var child;
        while ((child = elm.firstChild))
            elm.removeChild(child);
        if (arguments.length < 2)
            return elm;
        else
            return AJS.appendChildNodes.apply(null, arguments);
        return elm;
    },

    insertAfter: function(elm, reference_elm) {
        reference_elm.parentNode.insertBefore(elm, reference_elm.nextSibling);
        return elm;
    },

    insertBefore: function(elm, reference_elm) {
        reference_elm.parentNode.insertBefore(elm, reference_elm);
        return elm;
    },

    showElement: function(/*elms...*/) {
        var args = AJS.forceArray(arguments);
        AJS.map(args, function(elm) { elm.style.display = ''});
    },

    hideElement: function(elm) {
        var args = AJS.forceArray(arguments);
        AJS.map(args, function(elm) { elm.style.display = 'none'});
    },

    swapDOM: function(dest, src) {
        dest = AJS.getElement(dest);
        var parent = dest.parentNode;
        if (src) {
            src = AJS.getElement(src);
            parent.replaceChild(src, dest);
        } else {
            parent.removeChild(dest);
        }
        return src;
    },

    removeElement: function(/*elm1, elm2...*/) {
        var args = AJS.forceArray(arguments);
        AJS.map(args, function(elm) { AJS.swapDOM(elm, null); });
    },

    createDOM: function(name, attrs) {
        var i=0, attr;
        elm = document.createElement(name);

        if(AJS.isDict(attrs[i])) {
            for(k in attrs[0]) {
                attr = attrs[0][k];
                if(k == "style")
                    elm.style.cssText = attr;
                else if(k == "class" || k == 'className')
                    elm.className = attr;
                else {
                    elm.setAttribute(k, attr);
                }
            }
            i++;
        }

        if(attrs[0] == null)
            i = 1;

        AJS.map(attrs, function(n) {
            if(n) {
                if(AJS.isString(n) || AJS.isNumber(n))
                    n = AJS.TN(n);
                elm.appendChild(n);
            }
        }, i);
        return elm;
    },

    _createDomShortcuts: function() {
        var elms = [
                "ul", "li", "td", "tr", "th",
                "tbody", "table", "input", "span", "b",
                "a", "div", "img", "button", "h1",
                "h2", "h3", "br", "textarea", "form",
                "p", "select", "option", "optgroup", "iframe", "script",
                "center", "dl", "dt", "dd", "small",
                "pre"
        ];
        var extends_ajs = function(elm) {
            AJS[elm.toUpperCase()] = function() {
                return AJS.createDOM.apply(null, [elm, arguments]); 
            };
        }
        AJS.map(elms, extends_ajs);
        AJS.TN = function(text) { return document.createTextNode(text) };
    },

    getCssDim: function(dim) {
        if(AJS.isString(dim))
            return dim;
        else
            return dim + "px";
    },
    getCssProperty: function(elm, prop) {
        elm = AJS.$(elm);
        var y;
        if(elm.currentStyle)
            y = elm.currentStyle[prop];
	else if (window.getComputedStyle)
            y = document.defaultView.getComputedStyle(elm,null).getPropertyValue(prop);
	return y;
    },

    setStyle: function(/*elm1, elm2..., property, new_value*/) {
        var args = AJS.forceArray(arguments);
        var new_val = args.pop();
        var property = args.pop();
        AJS.map(args, function(elm) { 
            elm.style[property] = AJS.getCssDim(new_val);
        });
    },

    setWidth: function(/*elm1, elm2..., width*/) {
        var args = AJS.forceArray(arguments);
        args.splice(args.length-1, 0, 'width');
        AJS.setStyle.apply(null, args);
    },
    setHeight: function(/*elm1, elm2..., height*/) {
        var args = AJS.forceArray(arguments);
        args.splice(args.length-1, 0, 'height');
        AJS.setStyle.apply(null, args);
    },
    setLeft: function(/*elm1, elm2..., left*/) {
        var args = AJS.forceArray(arguments);
        args.splice(args.length-1, 0, 'left');
        AJS.setStyle.apply(null, args);
    },
    setTop: function(/*elm1, elm2..., top*/) {
        var args = AJS.forceArray(arguments);
        args.splice(args.length-1, 0, 'top');
        AJS.setStyle.apply(null, args);
    },
    setClass: function(/*elm1, elm2..., className*/) {
        var args = AJS.forceArray(arguments);
        var c = args.pop();
        AJS.map(args, function(elm) { elm.className = c});
    },
    addClass: function(/*elm1, elm2..., className*/) {
        var args = AJS.forceArray(arguments);
        var cls = args.pop();
        var add_class = function(o) {
            if(!new RegExp("(^|\\s)" + cls + "(\\s|$)").test(o.className))
                o.className += (o.className ? " " : "") + cls;
        };
        AJS.map(args, function(elm) { add_class(elm); });
    },
    hasClass: function(elm, cls) {
        if(!elm.className)
            return false;
        return elm.className == cls || 
               elm.className.search(new RegExp(" " + cls + "|^" + cls)) != -1
    },
    removeClass: function(/*elm1, elm2..., className*/) {
        var args = AJS.forceArray(arguments);
        var cls = args.pop();
        var rm_class = function(o) {
            o.className = o.className.replace(new RegExp("\\s?" + cls, 'g'), "");
        };
        AJS.map(args, function(elm) { rm_class(elm); });
    },

    setHTML: function(elm, html) {
        elm.innerHTML = html;
        return elm;
    },

    RND: function(tmpl, ns, scope) {
        scope = scope || window;
        var fn = function(w, g) {
            g = g.split("|");
            var cnt = ns[g[0]];
            for(var i=1; i < g.length; i++)
                cnt = scope[g[i]](cnt);
            if(cnt == '')
                return '';
            if(cnt == 0 || cnt == -1)
                cnt += '';
            return cnt || w;
        };
        return tmpl.replace(/%\(([A-Za-z0-9_|.]*)\)/g, fn);
    },

    HTML2DOM: function(html,/*optional*/ first_child) {
        var d = AJS.DIV();
        d.innerHTML = html;
        if(first_child)
            return d.childNodes[0];
        else
            return d;
    },

    preloadImages: function(/*img_src1, ..., img_srcN*/) {
        AJS.AEV(window, 'load', AJS.$p(function(args) {
            AJS.map(args, function(src) {
                var pic = new Image();
                pic.src = src;
            });
        }, arguments));
    },


////
// Effects
////
    setOpacity: function(elm, p) {
        elm.style.opacity = p;
        elm.style.filter = "alpha(opacity="+ p*100 +")";
    },

    resetOpacity: function(elm) {
        elm.style.opacity = 1;
        elm.style.filter = "";
    },

////
// Ajax functions
////
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

    getRequest: function(url, data, type) {
        if(!type)
            type = "POST";
        var req = AJS.getXMLHttpRequest();

        if(url.indexOf("http://") == -1) {
            if(AJS.BASE_URL != '') {
                if(AJS.BASE_URL.lastIndexOf('/') != AJS.BASE_URL.length-1)
                    AJS.BASE_URL += '/';
                url = AJS.BASE_URL + url;
            }
        }

        req.open(type, url, true);
        if(type == "POST")
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        return AJS._sendXMLHttpRequest(req);
    },

    _sendXMLHttpRequest: function(req, data) {
        var d = new AJSDeferred(req);

        var onreadystatechange = function () {
            if (req.readyState == 4) {
                var status = '';
                try {
                    status = req.status;
                }
                catch(e) {};
                if(status == 200 || status == 304 || req.responseText == null) {
                    d.callback();
                }
                else {
                    if(d.errbacks.length == 0) {
                        if(AJS.ajaxErrorHandler)
                            AJS.ajaxErrorHandler(req.responseText, req);
                    }
                    else 
                        d.errback();
                }
            }
        }
        req.onreadystatechange = onreadystatechange;
        return d;
    },

    _reprString: function(o) {
        return ('"' + o.replace(/(["\\])/g, '\\$1') + '"'
        ).replace(/[\f]/g, "\\f"
        ).replace(/[\b]/g, "\\b"
        ).replace(/[\n]/g, "\\n"
        ).replace(/[\t]/g, "\\t"
        ).replace(/[\r]/g, "\\r");
    },

    _reprDate: function(db) {
        var year = db.getFullYear();
        var dd = db.getDate();
        var mm = db.getMonth()+1;

        var hh = db.getHours();
        var mins = db.getMinutes();

        function leadingZero(nr) {
            if (nr < 10) nr = "0" + nr;
            return nr;
        }
        if(hh == 24) hh = '00';

        var time = leadingZero(hh) + ':' + leadingZero(mins);
        return '"' + year + '-' + mm + '-' + dd + 'T' + time + '"';
    },

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
            return AJS._reprString(o);
        }
        if(objtype == 'object' && o.getFullYear) {
            return AJS._reprDate(o);
        }
        var me = arguments.callee;
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
        // it's a function with no adapter, bad
        if (objtype == "function")
            return null;
        res = [];
        for (var k in o) {
            var useKey;
            if (typeof(k) == "number") {
                useKey = '"' + k + '"';
            } else if (typeof(k) == "string") {
                useKey = AJS._reprString(k);
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

    loadJSONDoc: function(url) {
        var d = AJS.getRequest(url);
        var eval_req = function(data, req) {
            var text = req.responseText;
            if(text == "Error")
                d.errback(req);
            else
                return AJS.evalTxt(text);
        };
        d.addCallback(eval_req);
        return d;
    },

    evalTxt: function(txt) {
        try {
            return eval('('+ txt + ')');
        }
        catch(e) {
            return eval(txt);
        }
    },

    evalScriptTags: function(html) {
        var script_data = html.match(/<script.*?>((\n|\r|.)*?)<\/script>/g);
        if(script_data != null) {
            for(var i=0; i < script_data.length; i++) {
                var script_only = script_data[i].replace(/<script.*?>/g, "");
                script_only = script_only.replace(/<\/script>/g, "");
                eval(script_only);
            }
        }
    },

    queryArguments: function(data) {
        var post_data = [];
        for(k in data)
            post_data.push(k + "=" + AJS.urlencode(data[k]));
        return post_data.join("&");
    },


////
// Position and size
////
    getMousePos: function(e) {
        var posx = 0;
        var posy = 0;
        if (!e) var e = window.event;
        if (e.pageX || e.pageY) {
            posx = e.pageX;
            posy = e.pageY;
        }
        else if (e.clientX || e.clientY) {
            posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }
        return {x: posx, y: posy};
    },

    getScrollTop: function() {
        //From: http://www.quirksmode.org/js/doctypes.html
        var t;
        if (document.documentElement && document.documentElement.scrollTop)
                t = document.documentElement.scrollTop;
        else if (document.body)
                t = document.body.scrollTop;
        return t;
    },

    absolutePosition: function(elm) {
        var posObj = {'x': elm.offsetLeft, 'y': elm.offsetTop};

        if(elm.offsetParent) {
            var next = elm.offsetParent;
            while(next) {
                posObj.x += next.offsetLeft;
                posObj.y += next.offsetTop;
                next = next.offsetParent;
            }
        }
        // safari bug
        if (AJS.isSafari() && elm.style.position == 'absolute' ) {
            posObj.x -= document.body.offsetLeft;
            posObj.y -= document.body.offsetTop;
        }
        return posObj;
    },

    getWindowSize: function(doc) {
        doc = doc || document;
        var win_w, win_h;
        if (self.innerHeight) {
            win_w = self.innerWidth;
            win_h = self.innerHeight;
        } else if (doc.documentElement && doc.documentElement.clientHeight) {
            win_w = doc.documentElement.clientWidth;
            win_h = doc.documentElement.clientHeight;
        } else if (doc.body) {
            win_w = doc.body.clientWidth;
            win_h = doc.body.clientHeight;
        }
        return {'w': win_w, 'h': win_h};
    },

    isOverlapping: function(elm1, elm2) {
        var pos_elm1 = AJS.absolutePosition(elm1);
        var pos_elm2 = AJS.absolutePosition(elm2);

        var top1 = pos_elm1.y;
        var left1 = pos_elm1.x;
        var right1 = left1 + elm1.offsetWidth;
        var bottom1 = top1 + elm1.offsetHeight;
        var top2 = pos_elm2.y;
        var left2 = pos_elm2.x;
        var right2 = left2 + elm2.offsetWidth;
        var bottom2 = top2 + elm2.offsetHeight;
        var getSign = function(v) {
            if(v > 0) return "+";
            else if(v < 0) return "-";
            else return 0;
        }

        if ((getSign(top1 - bottom2) != getSign(bottom1 - top2)) &&
                (getSign(left1 - right2) != getSign(right1 - left2)))
            return true;
        return false;
    },


////
// Events
////
    getEventElm: function(e) {
        if(e && !e.type && !e.keyCode)
            return e
        var targ;
        if (!e) var e = window.event;
        if (e.target) targ = e.target;
        else if (e.srcElement) targ = e.srcElement;
        if (targ.nodeType == 3) // defeat Safari bug
            targ = targ.parentNode;
        return targ;
    },

    _getRealScope: function(fn, /*optional*/ extra_args) {
        extra_args = AJS.$A(extra_args);
        var scope = fn._cscope || window;

        return function() {
            var args = AJS.$FA(arguments).concat(extra_args);
            return fn.apply(scope, args);
        };
    },

    _unloadListeners: function() {
        if(AJS.listeners)
            AJS.map(AJS.listeners, function(elm, type, fn) { AJS.REV(elm, type, fn) });
        AJS.listeners = [];
    },

    setEventKey: function(e) {
        e.key = e.keyCode ? e.keyCode : e.charCode;

        if(window.event) {
            e.ctrl = window.event.ctrlKey;
            e.shift = window.event.shiftKey;
        }
        else {
            e.ctrl = e.ctrlKey;
            e.shift = e.shiftKey;
        }
        switch(e.key) {
            case 63232:
                e.key = 38;
                break;
            case 63233:
                e.key = 40;
                break;
            case 63235:
                e.key = 39;
                break;
            case 63234:
                e.key = 37;
                break;
        }
    },

    //Shortcut: AJS.AEV
    addEventListener: function(elm, type, fn, /*optional*/listen_once, cancle_bubble) {
        if(!cancle_bubble)
            cancle_bubble = false;

        var elms = AJS.$A(elm);
        AJS.map(elms, function(elmz) {
            if(listen_once)
                fn = AJS._listenOnce(elmz, type, fn);
            
            //Hack since it does not work in all browsers
            if(AJS.isIn(type, ['submit', 'load', 'scroll', 'resize'])) {
                var old = elm['on' + type];
                elm['on' + type] = function() {
                    if(old) {
                        fn(arguments);
                        return old(arguments);
                    }
                    else
                        return fn(arguments);
                };
                return;
            }

            //Fix keyCode
            if(AJS.isIn(type, ['keypress', 'keydown', 'keyup', 'click'])) {
                var old_fn = fn;
                fn = function(e) {
                    AJS.setEventKey(e);
                    return old_fn.apply(null, arguments);
                }
            }

            if (elmz.attachEvent) {
                //FIXME: We ignore cancle_bubble for IE... could be a problem?
                elmz.attachEvent("on" + type, fn);
            }
            else if(elmz.addEventListener)
                elmz.addEventListener(type, fn, cancle_bubble);

            AJS.listeners = AJS.$A(AJS.listeners);
            AJS.listeners.push([elmz, type, fn]);
        });
    },

    //Shortcut: AJS.REV
    removeEventListener: function(elm, type, fn, /*optional*/cancle_bubble) {
        if(!cancle_bubble)
            cancle_bubble = false;
        if(elm.removeEventListener) {
            elm.removeEventListener(type, fn, cancle_bubble);
            if(AJS.isOpera())
                elm.removeEventListener(type, fn, !cancle_bubble);
        }
        else if(elm.detachEvent)
            elm.detachEvent("on" + type, fn);
    },

    //Shortcut: AJS.$b
    bind: function(fn, scope, /*optional*/ extra_args) {
        fn._cscope = scope;
        return AJS._getRealScope(fn, extra_args);
    },

    bindMethods: function(self) {
        for (var k in self) {
            var func = self[k];
            if (typeof(func) == 'function') {
                self[k] = AJS.$b(func, self);
            }
        }
    },

    _listenOnce: function(elm, type, fn) {
        var r_fn = function() {
            AJS.removeEventListener(elm, type, r_fn);
            fn(arguments);
        }
        return r_fn;
    },

    callLater: function(fn, interval) {
        var fn_no_send = function() {
            fn();
        };
        window.setTimeout(fn_no_send, interval);
    },

    preventDefault: function(e) {
        if(AJS.isIe()) 
            window.event.returnValue = false;
        else 
            e.preventDefault();
    },


////
// Drag and drop
////
    dragAble: function(elm, /*optional*/ handler, args) {
        if(!args)
            args = {};
        if(!AJS.isDefined(args['move_x']))
            args['move_x'] = true;
        if(!AJS.isDefined(args['move_y']))
            args['move_y'] = true;
        if(!AJS.isDefined(args['moveable']))
            args['moveable'] = false;
        if(!AJS.isDefined(args['hide_on_move']))
            args['hide_on_move'] = true;
        if(!AJS.isDefined(args['on_mouse_up']))
            args['on_mouse_up'] = null;
        if(!AJS.isDefined(args['cursor']))
            args['cursor'] = 'move';
        if(!AJS.isDefined(args['max_move']))
            args['max_move'] = {'top': null, 'left': null};

        elm = AJS.$(elm);

        if(!handler)
            handler = elm;

        handler = AJS.$(handler);
        var old_cursor = handler.style.cursor;
        handler.style.cursor = args['cursor'];
        elm.style.position = 'relative';

        AJS.addClass(handler, '_ajs_handler');
        handler._args = args;
        handler._elm = elm;
        AJS.AEV(handler, 'mousedown', AJS._dragStart);
    },

    _dragStart: function(e) {
        var handler = AJS.getEventElm(e);
        if(!AJS.hasClass(handler, '_ajs_handler')) {
            handler = AJS.getParentBytc(handler, null, '_ajs_handler');
        }
        if(handler)
            AJS._dragInit(e, handler._elm, handler._args);
    },

    dropZone: function(elm, args) {
        elm = AJS.$(elm);
        var item = {elm: elm};
        AJS.update(item, args);
        AJS._drop_zones.push(item);
    },

    removeDragAble: function(elm) {
        AJS.REV(elm, 'mousedown', AJS._dragStart);
        elm.style.cursor = '';
    },

    removeDropZone: function(elm) {
        var i = AJS.getIndex(elm, AJS._drop_zones, function(item) {
            if(item.elm == elm) return true;
        });
        if(i != -1) {
            AJS._drop_zones.splice(i, 1);
        }
    },

    _dragInit: function(e, click_elm, args) {
        AJS.drag_obj = new Object();
        AJS.drag_obj.args = args;

        AJS.drag_obj.click_elm = click_elm;
        AJS.drag_obj.mouse_pos = AJS.getMousePos(e);
        AJS.drag_obj.click_elm_pos = AJS.absolutePosition(click_elm);

        AJS.AEV(document, 'mousemove', AJS._dragMove, false, true);
        AJS.AEV(document, 'mouseup', AJS._dragStop, false, true);

        if (AJS.isIe())
            window.event.cancelBubble = true;
        AJS.preventDefault(e);
    },

    _initDragElm: function(elm) {
        if(AJS.drag_elm && AJS.drag_elm.style.display == 'none')
            AJS.removeElement(AJS.drag_elm);

        if(!AJS.drag_elm) {
            AJS.drag_elm = AJS.DIV();
            var d = AJS.drag_elm;
            AJS.insertBefore(d, AJS.getBody().firstChild);
            AJS.setHTML(d, elm.innerHTML);

            d.className = elm.className;
            d.style.cssText = elm.style.cssText;

            d.style.position = 'absolute';
            d.style.zIndex = 10000;

            var t = AJS.absolutePosition(elm);
            AJS.setTop(d, t.y);
            AJS.setLeft(d, t.x);

            if(AJS.drag_obj.args.on_init) {
                AJS.drag_obj.args.on_init(elm);
            }
        }
    },

    _dragMove: function(e) {
        var drag_obj = AJS.drag_obj;
        var click_elm = drag_obj.click_elm;

        AJS._initDragElm(click_elm);
        var drag_elm = AJS.drag_elm;

        if(drag_obj.args['hide_on_move'])
            click_elm.style.visibility = 'hidden';

        var cur_pos = AJS.getMousePos(e);

        var mouse_pos = drag_obj.mouse_pos;

        var click_elm_pos = drag_obj.click_elm_pos;

        var p_x, p_y;
        p_x = cur_pos.x - (mouse_pos.x - click_elm_pos.x);
        p_y = cur_pos.y - (mouse_pos.y - click_elm_pos.y);

        AJS.map(AJS._drop_zones, function(d_z) {
            if(AJS.isOverlapping(d_z['elm'], drag_elm)) {
                if(d_z['elm'] != drag_elm) {
                    var on_hover = d_z['on_hover'];
                    if(on_hover)
                        on_hover(d_z['elm'], click_elm, drag_elm);
                }
            }
        });

        if(drag_obj.args['on_drag'])
            drag_obj.args['on_drag'](click_elm, e);

        var max_move_top = drag_obj.args['max_move']['top'];
        var max_move_left = drag_obj.args['max_move']['left'];
        if(drag_obj.args['move_x']) {
            if(max_move_left == null || max_move_left <= p)
                AJS.setLeft(elm, p_x);
        }

        if(drag_obj.args['move_y']) {
            if(max_move_top == null || max_move_top <= p_y)
                AJS.setTop(elm, p_y);
        }
        if(AJS.isIe()) {
            window.event.cancelBubble = true;
            window.event.returnValue = false;
        }
        else
            e.preventDefault();

        //Moving scroll to the top, should move the scroll up
        var sc_top = AJS.getScrollTop();
        var sc_bottom = sc_top + AJS.getWindowSize().h;
        var d_e_top = AJS.absolutePosition(drag_elm).y;
        var d_e_bottom = drag_elm.offsetTop + drag_elm.offsetHeight;

        if(d_e_top <= sc_top + 20) {
            window.scrollBy(0, -15);
        }
        else if(d_e_bottom >= sc_bottom - 20) {
            window.scrollBy(0, 15);
        }
    },

    _dragStop: function(e) {
        var drag_obj = AJS.drag_obj;
        var drag_elm = AJS.drag_elm;
        var click_elm = drag_obj.click_elm;

        AJS.REV(document, "mousemove", AJS._dragMove, true);
        AJS.REV(document, "mouseup", AJS._dragStop, true);

        var dropped = false;
        AJS.map(AJS._drop_zones, function(d_z) {
            if(AJS.isOverlapping(d_z['elm'], click_elm)) {
                if(d_z['elm'] != click_elm) {
                    var on_drop = d_z['on_drop'];
                    if(on_drop) {
                        dropped = true;
                        on_drop(d_z['elm'], click_elm);
                    }
                }
            }
        });

        if(drag_obj.args['moveable']) {
            var t = parseInt(click_elm.style.top) || 0;
            var l = parseInt(click_elm.style.left) || 0;
            var drag_elm_xy = AJS.absolutePosition(drag_elm);
            var click_elm_xy = AJS.absolutePosition(click_elm);
            AJS.setTop(click_elm, t + drag_elm_xy.y - click_elm_xy.y);
            AJS.setLeft(click_elm, l + drag_elm_xy.x - click_elm_xy.x);
        }

        if(!dropped && drag_obj.args['on_mouse_up'])
            drag_obj.args['on_mouse_up'](click_elm, e);

        if(drag_obj.args['hide_on_move'])
            drag_obj.click_elm.style.visibility = 'visible';

        if(drag_obj.args.on_end) {
            drag_obj.args.on_end(click_elm);
        }

        AJS._dragObj = null;
        if(drag_elm)
            AJS.hideElement(drag_elm);
        AJS.drag_elm = null;
    },


////
// Misc.
////
    keys: function(obj) {
        var rval = [];
        for (var prop in obj) {
            rval.push(prop);
        }
        return rval;
    },

    values: function(obj) {
        var rval = [];
        for (var prop in obj) {
            rval.push(obj[prop]);
        }
        return rval;
    },

    urlencode: function(str) {
        return encodeURIComponent(str.toString());
    },

    isDefined: function(o) {
        return (o != "undefined" && o != null)
    },

    isArray: function(obj) {
        return obj instanceof Array;
    },

    isString: function(obj) {
        return (typeof obj == 'string');
    },

    isNumber: function(obj) {
        return (typeof obj == 'number');
    },

    isObject: function(obj) {
        return (typeof obj == 'object');
    },

    isFunction: function(obj) {
        return (typeof obj == 'function');
    },

    isDict: function(o) {
        var str_repr = String(o);
        return str_repr.indexOf(" Object") != -1;
    },

    exportToGlobalScope: function() {
        for(e in AJS)
            window[e] = AJS[e];
    },

    log: function(o) {
        if(window.console)
            console.log(o);
        else {
            var div = AJS.$('ajs_logger');
            if(!div) {
                div = AJS.DIV({id: 'ajs_logger', 'style': 'color: green; position: absolute; left: 0'});
                div.style.top = AJS.getScrollTop() + 'px';
                AJS.ACN(AJS.getBody(), div);
            }
            AJS.setHTML(div, ''+o);
        }
    }

}

AJS.Class = function(members) {
    var fn = function() {
        if(arguments[0] != 'no_init') {
            return this.init.apply(this, arguments);
        }
    }
    fn.prototype = members;
    AJS.update(fn, AJS.Class.prototype);
    return fn;
}
AJS.Class.prototype = {
    extend: function(members) {
        var parent = new this('no_init');
        for(k in members) {
            var prev = parent[k];
            var cur = members[k];
            if (prev && prev != cur && typeof cur == 'function') {
                cur = this._parentize(cur, prev);
            }
            parent[k] = cur;
        }
        return new AJS.Class(parent);
    },

    implement: function(members) {
        AJS.update(this.prototype, members);
    },

    _parentize: function(cur, prev) {
        return function(){
            this.parent = prev;
            return cur.apply(this, arguments);
        }
    }
};

//Shortcuts
AJS.$ = AJS.getElement;
AJS.$$ = AJS.getElements;
AJS.$f = AJS.getFormElement;
AJS.$b = AJS.bind;
AJS.$p = AJS.partial;
AJS.$FA = AJS.forceArray;
AJS.$A = AJS.createArray;
AJS.DI = AJS.documentInsert;
AJS.ACN = AJS.appendChildNodes;
AJS.RCN = AJS.replaceChildNodes;
AJS.AEV = AJS.addEventListener;
AJS.REV = AJS.removeEventListener;
AJS.$bytc = AJS.getElementsByTagAndClassName;

AJSDeferred = function(req) {
    this.callbacks = [];
    this.errbacks = [];
    this.req = req;
}
AJSDeferred.prototype = {
    excCallbackSeq: function(req, list) {
        var data = req.responseText;
        while (list.length > 0) {
            var fn = list.pop();
            var new_data = fn(data, req);
            if(new_data)
                data = new_data;
        }
    },

    callback: function () {
        this.excCallbackSeq(this.req, this.callbacks);
    },

    errback: function() {
        if(this.errbacks.length == 0)
            alert("Error encountered:\n" + this.req.responseText);

        this.excCallbackSeq(this.req, this.errbacks);
    },

    addErrback: function(fn) {
        this.errbacks.unshift(fn);
    },

    addCallback: function(fn) {
        this.callbacks.unshift(fn);
    },

    abort: function() {
        this.req.abort();
    },

    addCallbacks: function(fn1, fn2) {
        this.addCallback(fn1);
        this.addErrback(fn2);
    },

    sendReq: function(data) {
        if(AJS.isObject(data)) {
            this.req.send(AJS.queryArguments(data));
        }
        else if(AJS.isDefined(data))
            this.req.send(data);
        else {
            this.req.send("");
        }
    }
};

//Prevent memory-leaks
AJS.addEventListener(window, 'unload', AJS._unloadListeners);
AJS._createDomShortcuts()
}

script_loaded = true;

/****
Last Modified: 13/05/07 00:25:28

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
     4.0
****/
var GOOGIE_CUR_LANG = null;
var GOOGIE_DEFAULT_LANG = "en";

function GoogieSpell(img_dir, server_url) {
    var cookie_value;
    var lang;
    cookie_value = getCookie('language');

    if(cookie_value != null)
        GOOGIE_CUR_LANG = cookie_value;
    else
        GOOGIE_CUR_LANG = GOOGIE_DEFAULT_LANG;

    this.img_dir = img_dir;
    this.server_url = server_url;

    this.org_lang_to_word = {"da": "Dansk", "de": "Deutsch", "en": "English",
                                             "es": "Espa&#241;ol", "fr": "Fran&#231;ais", "it": "Italiano", 
                                             "nl": "Nederlands", "pl": "Polski", "pt": "Portugu&#234;s",
                                             "fi": "Suomi", "sv": "Svenska"};
    this.lang_to_word = this.org_lang_to_word;
    this.langlist_codes = AJS.keys(this.lang_to_word);

    this.show_change_lang_pic = true;
    this.change_lang_pic_placement = "left";

    this.report_state_change = true;

    this.ta_scroll_top = 0;
    this.el_scroll_top = 0;

    this.lang_chck_spell = "Check spelling";
    this.lang_revert = "Revert to";
    this.lang_close = "Close";
    this.lang_rsm_edt = "Resume editing";
    this.lang_no_error_found = "No spelling errors found";
    this.lang_no_suggestions = "No suggestions";
    
    this.show_spell_img = false;  // modified by roundcube
    this.decoration = true;
    this.use_close_btn = true;
    this.edit_layer_dbl_click = true;
    this.report_ta_not_found = true;

    //Extensions
    this.custom_ajax_error = null;
    this.custom_no_spelling_error = null;
    this.custom_menu_builder = []; //Should take an eval function and a build menu function
    this.custom_item_evaulator = null; //Should take an eval function and a build menu function
    this.extra_menu_items = [];
    this.custom_spellcheck_starter = null;
    this.main_controller = true;

    //Observers
    this.lang_state_observer = null;
    this.spelling_state_observer = null;
    this.show_menu_observer = null;
    this.all_errors_fixed_observer = null;

    //Focus links - used to give the text box focus
    this.use_focus = false;
    this.focus_link_t = null;
    this.focus_link_b = null;

    //Counters
    this.cnt_errors = 0;
    this.cnt_errors_fixed = 0;

    //Set document on click to hide the language and error menu
    var fn = function(e) {
        var elm = AJS.getEventElm(e);
        if(elm.googie_action_btn != "1" && this.isLangWindowShown())
            this.hideLangWindow();
        if(elm.googie_action_btn != "1" && this.isErrorWindowShown())
            this.hideErrorWindow();
    };
    AJS.AEV(document, "click", AJS.$b(fn, this));
}

GoogieSpell.prototype.decorateTextarea = function(id) {
    if(typeof(id) == "string")
        this.text_area = AJS.$(id);
    else
        this.text_area = id;

    var r_width, r_height;

    if(this.text_area != null) {
        if(!AJS.isDefined(this.spell_container) && this.decoration) {
            var table = AJS.TABLE();
            var tbody = AJS.TBODY();
            var tr = AJS.TR();
            if(AJS.isDefined(this.force_width))
                r_width = this.force_width;
            else
                r_width = this.text_area.offsetWidth + "px";

            if(AJS.isDefined(this.force_height))
                r_height = this.force_height;
            else
                r_height = "";

            var spell_container = AJS.TD();
            this.spell_container = spell_container;

            tr.appendChild(spell_container);

            tbody.appendChild(tr);
            table.appendChild(tbody);

            AJS.insertBefore(table, this.text_area);

            //Set width
            AJS.setHeight(table, spell_container, r_height);
            AJS.setWidth(table, spell_container, '100%');  // modified by roundcube (old: r_width)

            spell_container.style.textAlign = "right";
        }

        this.checkSpellingState();
    }
    else 
        if(this.report_ta_not_found)
            alert("Text area not found");
}

//////
// API Functions (the ones that you can call)
/////
GoogieSpell.prototype.setSpellContainer = function(elm) {
    this.spell_container = AJS.$(elm);
}

GoogieSpell.prototype.setLanguages = function(lang_dict) {
    this.lang_to_word = lang_dict;
    this.langlist_codes = AJS.keys(lang_dict);
}

GoogieSpell.prototype.setForceWidthHeight = function(width, height) {
    /***
        Set to null if you want to use one of them
    ***/
    this.force_width = width;
    this.force_height = height;
}

GoogieSpell.prototype.setDecoration = function(bool) {
    this.decoration = bool;
}

GoogieSpell.prototype.dontUseCloseButtons = function() {
    this.use_close_btn = false;
}

GoogieSpell.prototype.appendNewMenuItem = function(name, call_back_fn, checker) {
    this.extra_menu_items.push([name, call_back_fn, checker]);
}

GoogieSpell.prototype.appendCustomMenuBuilder = function(eval, builder) {
    this.custom_menu_builder.push([eval, builder]);
}

GoogieSpell.prototype.setFocus = function() {
    try {
        this.focus_link_b.focus();
        this.focus_link_t.focus();
        return true;
    }
    catch(e) {
        return false;
    }
}

GoogieSpell.prototype.getValue = function(ta) {
    return ta.value;
}

GoogieSpell.prototype.setValue = function(ta, value) {
    ta.value = value;
}


//////
// Set functions (internal)
/////
GoogieSpell.prototype.setStateChanged = function(current_state) {
    this.state = current_state;
    if(this.spelling_state_observer != null && this.report_state_change)
        this.spelling_state_observer(current_state, this);
}

GoogieSpell.prototype.setReportStateChange = function(bool) {
    this.report_state_change = bool;
}


//////
// Request functions
/////
GoogieSpell.prototype.getGoogleUrl = function() {
    return this.server_url + GOOGIE_CUR_LANG;
}

GoogieSpell.escapeSepcial = function(val) {
    return val.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

GoogieSpell.createXMLReq = function (text) {
    return '<?xml version="1.0" encoding="utf-8" ?><spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1"><text>' + text + '</text></spellrequest>';
}

GoogieSpell.prototype.spellCheck = function(ignore) {
    var me = this;

    this.cnt_errors_fixed = 0;
    this.cnt_errors = 0;
    this.setStateChanged("checking_spell");

    if(this.main_controller)
        this.appendIndicator(this.spell_span);

    this.error_links = [];
    this.ta_scroll_top = this.text_area.scrollTop;

    try { this.hideLangWindow(); }
    catch(e) {}

    this.ignore = ignore;

    if(this.getValue(this.text_area) == '' || ignore) {
        if(!me.custom_no_spelling_error)
            me.flashNoSpellingErrorState();
        else
            me.custom_no_spelling_error(me);
        me.removeIndicator();
        return ;
    }
    
    this.createEditLayer(this.text_area.offsetWidth, this.text_area.offsetHeight);

    this.createErrorWindow();
    AJS.getBody().appendChild(this.error_window);

    try { netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead"); } 
    catch (e) { }

    if(this.main_controller)
        this.spell_span.onclick = null;

    this.orginal_text = this.getValue(this.text_area);

    //Create request
    var d = AJS.getRequest(this.getGoogleUrl());
    var reqdone = function(res_txt) {
        var r_text = res_txt;
        me.results = me.parseResult(r_text);

        if(r_text.match(/<c.*>/) != null) {
            //Before parsing be sure that errors were found
            me.showErrorsInIframe();
            me.resumeEditingState();
        }
        else {
            if(!me.custom_no_spelling_error)
                me.flashNoSpellingErrorState();
            else
                me.custom_no_spelling_error(me);
        }
        me.removeIndicator();
    };

    d.addCallback(reqdone);
    reqdone = null;

    var reqfailed = function(res_txt, req) {
        if(me.custom_ajax_error)
            me.custom_ajax_error(req);
        else
            alert("An error was encountered on the server. Please try again later.");

        if(me.main_controller) {
            AJS.removeElement(me.spell_span);
            me.removeIndicator();
        }
        me.checkSpellingState();
    };
    d.addErrback(reqfailed);
    reqfailed = null;

    var req_text = GoogieSpell.escapeSepcial(this.orginal_text);
    d.sendReq(GoogieSpell.createXMLReq(req_text));
}


//////
// Spell checking functions
/////
GoogieSpell.prototype.parseResult = function(r_text) {
    /***
     Retunrs an array
     result[item] -> ['attrs'], ['suggestions']
        ***/
    var re_split_attr_c = /\w+="(\d+|true)"/g;
    var re_split_text = /\t/g;

    var matched_c = r_text.match(/<c[^>]*>[^<]*<\/c>/g);
    var results = new Array();

    if(matched_c == null)
        return results;
    
    for(var i=0; i < matched_c.length; i++) {
        var item = new Array();
        this.errorFound();

        //Get attributes
        item['attrs'] = new Array();
        var split_c = matched_c[i].match(re_split_attr_c);
        for(var j=0; j < split_c.length; j++) {
            var c_attr = split_c[j].split(/=/);
            var val = c_attr[1].replace(/"/g, '');
            if(val != "true")
                item['attrs'][c_attr[0]] = parseInt(val);
            else {
                item['attrs'][c_attr[0]] = val;
            }
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

//////
// Counters
/////
GoogieSpell.prototype.errorFixed = function() { 
    this.cnt_errors_fixed++; 
    if(this.all_errors_fixed_observer)
        if(this.cnt_errors_fixed == this.cnt_errors) {
            this.hideErrorWindow();
            this.all_errors_fixed_observer();
        }
}
GoogieSpell.prototype.errorFound = function() { this.cnt_errors++; }

//////
// Error menu functions
/////
GoogieSpell.prototype.createErrorWindow = function() {
    this.error_window = AJS.DIV();
    this.error_window.className = "googie_window";
    this.error_window.googie_action_btn = "1";
}

GoogieSpell.prototype.isErrorWindowShown = function() {
    return this.error_window != null && this.error_window.style.visibility == "visible";
}

GoogieSpell.prototype.hideErrorWindow = function() {
    try {
        this.error_window.style.visibility = "hidden";
        if(this.error_window_iframe)
            this.error_window_iframe.style.visibility = "hidden";
    }
    catch(e) {}
}

GoogieSpell.prototype.updateOrginalText = function(offset, old_value, new_value, id) {
    var part_1 = this.orginal_text.substring(0, offset);
    var part_2 = this.orginal_text.substring(offset+old_value.length);
    this.orginal_text = part_1 + new_value + part_2;
    this.setValue(this.text_area, this.orginal_text);
    var add_2_offset = new_value.length - old_value.length;
    for(var j=0; j < this.results.length; j++) {
        //Don't edit the offset of the current item
        if(j != id && j > id){
            this.results[j]['attrs']['o'] += add_2_offset;
        }
    }
}

GoogieSpell.prototype.saveOldValue = function(elm, old_value) {
    elm.is_changed = true;
    elm.old_value = old_value;
}

GoogieSpell.prototype.createListSeparator = function() {
    var e_col = AJS.TD(" ");
    e_col.googie_action_btn = "1";
    e_col.style.cursor = "default";
    e_col.style.fontSize = "3px";
    e_col.style.borderTop = "1px solid #ccc";
    e_col.style.paddingTop = "3px";

    return AJS.TR(e_col);
}

GoogieSpell.prototype.correctError = function(id, elm, l_elm, /*optional*/ rm_pre_space) {
    var old_value = elm.innerHTML;
    var new_value = l_elm.innerHTML;
    var offset = this.results[id]['attrs']['o'];

    if(rm_pre_space) {
        var pre_length = elm.previousSibling.innerHTML;
        elm.previousSibling.innerHTML = pre_length.slice(0, pre_length.length-1);
        old_value = " " + old_value;
        offset--;
    }

    this.hideErrorWindow();

    this.updateOrginalText(offset, old_value, new_value, id);

    elm.innerHTML = new_value;
    
    elm.style.color = "green";
    elm.is_corrected = true;

    this.results[id]['attrs']['l'] = new_value.length;

    if(!AJS.isDefined(elm.old_value))
        this.saveOldValue(elm, old_value);
    
    this.errorFixed();
}

GoogieSpell.prototype.showErrorWindow = function(elm, id) {
    if(this.show_menu_observer)
        this.show_menu_observer(this);
    var me = this;

    var abs_pos = AJS.absolutePosition(elm);
    abs_pos.y -= this.edit_layer.scrollTop;
    this.error_window.style.visibility = "visible";

    AJS.setTop(this.error_window, (abs_pos.y+20));
    AJS.setLeft(this.error_window, (abs_pos.x));

    this.error_window.innerHTML = "";

    var table = AJS.TABLE({'class': 'googie_list'});
    table.googie_action_btn = "1";
    var list = AJS.TBODY();

    //Check if we should use custom menu builder, if not we use the default
    var changed = false;
    if(this.custom_menu_builder != []) {
        for(var k=0; k<this.custom_menu_builder.length; k++) {
            var eb = this.custom_menu_builder[k];
            if(eb[0]((this.results[id]))){
                changed = eb[1](this, list, elm);
                break;
            }
        }
    }
    if(!changed) {
        //Build up the result list
        var suggestions = this.results[id]['suggestions'];
        var offset = this.results[id]['attrs']['o'];
        var len = this.results[id]['attrs']['l'];

        if(suggestions.length == 0) {
            var row = AJS.TR();
            var item = AJS.TD({'style': 'cursor: default;'});
            var dummy = AJS.SPAN();
            dummy.innerHTML = this.lang_no_suggestions;
            AJS.ACN(item, AJS.TN(dummy.innerHTML));
            item.googie_action_btn = "1";
            row.appendChild(item);
            list.appendChild(row);
        }

        for(i=0; i < suggestions.length; i++) {
            var row = AJS.TR();
            var item = AJS.TD();
            var dummy = AJS.SPAN();
            dummy.innerHTML = suggestions[i];
            item.appendChild(AJS.TN(dummy.innerHTML));
            
            var fn = function(e) {
                var l_elm = AJS.getEventElm(e);
                this.correctError(id, elm, l_elm);
            };

            AJS.AEV(item, "click", AJS.$b(fn, this));

            item.onmouseover = GoogieSpell.item_onmouseover;
            item.onmouseout = GoogieSpell.item_onmouseout;
            row.appendChild(item);
            list.appendChild(row);
        }

        //The element is changed, append the revert
        if(elm.is_changed && elm.innerHTML != elm.old_value) {
            var old_value = elm.old_value;
            var revert_row = AJS.TR();
            var revert = AJS.TD();

            revert.onmouseover = GoogieSpell.item_onmouseover;
            revert.onmouseout = GoogieSpell.item_onmouseout;
            var rev_span = AJS.SPAN({'class': 'googie_list_revert'});
            rev_span.innerHTML = this.lang_revert + " " + old_value;
            revert.appendChild(rev_span);

            var fn = function(e) { 
                this.updateOrginalText(offset, elm.innerHTML, old_value, id);
                elm.is_corrected = true;
                elm.style.color = "#b91414";
                elm.innerHTML = old_value;
                this.hideErrorWindow();
            };
            AJS.AEV(revert, "click", AJS.$b(fn, this));

            revert_row.appendChild(revert);
            list.appendChild(revert_row);
        }
        
        //Append the edit box
        var edit_row = AJS.TR();
        var edit = AJS.TD({'style': 'cursor: default'});

        var edit_input = AJS.INPUT({'style': 'width: 120px; margin:0; padding:0', 'value': elm.innerHTML});
        edit_input.googie_action_btn = "1";

        var onsub = function () {
            if(edit_input.value != "") {
                if(!AJS.isDefined(elm.old_value))
                    this.saveOldValue(elm, elm.innerHTML);

                this.updateOrginalText(offset, elm.innerHTML, edit_input.value, id);
                elm.style.color = "green"
                elm.is_corrected = true;
                elm.innerHTML = edit_input.value;
                
                this.hideErrorWindow();
            }
            return false;
        };
        onsub = AJS.$b(onsub, this);
        
        var ok_pic = AJS.IMG({'src': this.img_dir + "ok.gif", 'style': 'width: 32px; height: 16px; margin-left: 2px; margin-right: 2px; cursor: pointer;'});
        var edit_form = AJS.FORM({'style': 'margin: 0; padding: 0; cursor: default;'}, edit_input, ok_pic);

        edit_form.googie_action_btn = "1";
        edit.googie_action_btn = "1";

        AJS.AEV(edit_form, "submit", onsub);
        AJS.AEV(ok_pic, "click", onsub);
        
        edit.appendChild(edit_form);
        edit_row.appendChild(edit);
        list.appendChild(edit_row);

        //Append extra menu items
        if(this.extra_menu_items.length > 0)
            AJS.ACN(list, this.createListSeparator());

        var loop = function(i) {
                if(i < me.extra_menu_items.length) {
                    var e_elm = me.extra_menu_items[i];

                    if(!e_elm[2] || e_elm[2](elm, me)) {
                        var e_row = AJS.TR();
                        var e_col = AJS.TD(e_elm[0]);

                        e_col.onmouseover = GoogieSpell.item_onmouseover;
                        e_col.onmouseout = GoogieSpell.item_onmouseout;

                        var fn = function() {
                            return e_elm[1](elm, me);
                        };
                        AJS.AEV(e_col, "click", fn);

                        AJS.ACN(e_row, e_col);
                        AJS.ACN(list, e_row);

                    }
                    loop(i+1);
                }
        }
        loop(0);
        loop = null;

        //Close button
        if(this.use_close_btn) {
            AJS.ACN(list, this.createCloseButton(this.hideErrorWindow));
        }
    }

    table.appendChild(list);
    this.error_window.appendChild(table);

    //Dummy for IE - dropdown bug fix
    if(AJS.isIe() && !this.error_window_iframe) {
        var iframe = AJS.IFRAME({'style': 'position: absolute; z-index: 0;'});
        AJS.ACN(AJS.getBody(), iframe);
        this.error_window_iframe = iframe;
    }
    if(AJS.isIe()) {
        var iframe = this.error_window_iframe;
        AJS.setTop(iframe, this.error_window.offsetTop);
        AJS.setLeft(iframe, this.error_window.offsetLeft);

        AJS.setWidth(iframe, this.error_window.offsetWidth);
        AJS.setHeight(iframe, this.error_window.offsetHeight);

        iframe.style.visibility = "visible";
    }

    //Set focus on the last element
    var link = this.createFocusLink('link');
    list.appendChild(AJS.TR(AJS.TD({'style': 'text-align: right; font-size: 1px; height: 1px; margin: 0; padding: 0;'}, link)));
    link.focus();
}


//////
// Edit layer (the layer where the suggestions are stored)
//////
GoogieSpell.prototype.createEditLayer = function(width, height) {
    this.edit_layer = AJS.DIV({'class': 'googie_edit_layer'});

    //Set the style so it looks like edit areas
    this.edit_layer.className = this.text_area.className;
    this.edit_layer.style.border = "1px solid #999";
    this.edit_layer.style.backgroundColor = "#F1EDFE";  // modified by roundcube
    this.edit_layer.style.padding = "3px";
    this.edit_layer.style.margin = "0px";

    AJS.setWidth(this.edit_layer, (width-8));

    if(AJS.nodeName(this.text_area) != "input" || this.getValue(this.text_area) == "") {
        this.edit_layer.style.overflow = "auto";
        AJS.setHeight(this.edit_layer, (height-6));
    }
    else {
        this.edit_layer.style.overflow = "hidden";
    }

    if(this.edit_layer_dbl_click) {
        var me = this;
        var fn = function(e) {
            if(AJS.getEventElm(e).className != "googie_link" && !me.isErrorWindowShown()) {
                me.resumeEditing();
                var fn1 = function() {
                    me.text_area.focus();
                    fn1 = null;
                };
                AJS.callLater(fn1, 10);
            }
            return false;
        };
        this.edit_layer.ondblclick = fn;
        fn = null;
    }
}

GoogieSpell.prototype.resumeEditing = function() {
    this.setStateChanged("spell_check");
    this.switch_lan_pic.style.display = "inline";

    if(this.edit_layer)
        this.el_scroll_top = this.edit_layer.scrollTop;

    this.hideErrorWindow();

    if(this.main_controller)
        this.spell_span.className = "googie_no_style";

    if(!this.ignore) {
        //Remove the EDIT_LAYER
        try {
            this.edit_layer.parentNode.removeChild(this.edit_layer);
            if(this.use_focus) {
                AJS.removeElement(this.focus_link_t);
                AJS.removeElement(this.focus_link_b);
            }
        }
        catch(e) {
        }

        AJS.showElement(this.text_area);

        if(this.el_scroll_top != undefined)
            this.text_area.scrollTop = this.el_scroll_top;
    }

    this.checkSpellingState(false);
}

GoogieSpell.prototype.createErrorLink = function(text, id) {
    var elm = AJS.SPAN({'class': 'googie_link'});
    var me = this;
    var d = function (e) {
        me.showErrorWindow(elm, id);
        d = null;
        return false;
    };
    AJS.AEV(elm, "click", d);

    elm.googie_action_btn = "1";
    elm.g_id = id;
    elm.is_corrected = false;
    elm.oncontextmenu = d;
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
    txt_part = txt_part.replace(/    /g, " &nbsp;");
    txt_part = txt_part.replace(/^ /g, "&nbsp;");
    txt_part = txt_part.replace(/ $/g, "&nbsp;");
    
    part.innerHTML = txt_part;

    return part;
}

GoogieSpell.prototype.showErrorsInIframe = function() {
    var output = AJS.DIV();
    output.style.textAlign = "left";
    var pointer = 0;
    var results = this.results;

    if(results.length > 0) {
        for(var i=0; i < results.length; i++) {
            var offset = results[i]['attrs']['o'];
            var len = results[i]['attrs']['l'];
            
            var part_1_text = this.orginal_text.substring(pointer, offset);
            var part_1 = GoogieSpell.createPart(part_1_text);
            output.appendChild(part_1);
            pointer += offset - pointer;
            
            //If the last child was an error, then insert some space
            var err_link = this.createErrorLink(this.orginal_text.substr(offset, len), i);
            this.error_links.push(err_link);
            output.appendChild(err_link);
            pointer += len;
        }
        //Insert the rest of the orginal text
        var part_2_text = this.orginal_text.substr(pointer, this.orginal_text.length);

        var part_2 = GoogieSpell.createPart(part_2_text);
        output.appendChild(part_2);
    }
    else
        output.innerHTML = this.orginal_text;

    var me = this;
    if(this.custom_item_evaulator)
        AJS.map(this.error_links, function(elm){me.custom_item_evaulator(me, elm)});
    
    AJS.ACN(this.edit_layer, output);

    //Hide text area
    this.text_area_bottom = this.text_area.offsetTop + this.text_area.offsetHeight;

    AJS.hideElement(this.text_area);

    AJS.insertBefore(this.edit_layer, this.text_area);

    if(this.use_focus) {
        this.focus_link_t = this.createFocusLink('focus_t');
        this.focus_link_b = this.createFocusLink('focus_b');

        AJS.insertBefore(this.focus_link_t, this.edit_layer);
        AJS.insertAfter(this.focus_link_b, this.edit_layer);
    }

    this.edit_layer.scrollTop = this.ta_scroll_top;
}


//////
// Choose language menu
//////
GoogieSpell.prototype.createLangWindow = function() {
    this.language_window = AJS.DIV({'class': 'googie_window'});
    AJS.setWidth(this.language_window, 100);

    this.language_window.googie_action_btn = "1";

    //Build up the result list
    var table = AJS.TABLE({'class': 'googie_list'});
    AJS.setWidth(table, "100%");
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

        var fn = function(e) {
            var elm = AJS.getEventElm(e);
            this.deHighlightCurSel();

            this.setCurrentLanguage(elm.googieId);

            if(this.lang_state_observer != null) {
                this.lang_state_observer();
            }

            this.highlightCurSel();
            this.hideLangWindow();
        };
        AJS.AEV(item, "click", AJS.$b(fn, this));

        item.onmouseover = function(e) { 
            var i_it = AJS.getEventElm(e);
            if(i_it.className != "googie_list_selected")
                i_it.className = "googie_list_onhover";
        };
        item.onmouseout = function(e) { 
            var i_it = AJS.getEventElm(e);
            if(i_it.className != "googie_list_selected")
                i_it.className = "googie_list_onout"; 
        };

        row.appendChild(item);
        list.appendChild(row);
    }

    //Close button
    if(this.use_close_btn) {
        list.appendChild(this.createCloseButton(this.hideLangWindow));
    }

    this.highlightCurSel();

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

GoogieSpell.prototype.isLangWindowShown = function() {
    return this.language_window != null && this.language_window.style.visibility == "visible";
}

GoogieSpell.prototype.hideLangWindow = function() {
    try {
        this.language_window.style.visibility = "hidden";
        this.switch_lan_pic.className = "googie_lang_3d_on";
    }
    catch(e) {}
}

GoogieSpell.prototype.deHighlightCurSel = function() {
    this.lang_cur_elm.className = "googie_list_onout";
}

GoogieSpell.prototype.highlightCurSel = function() {
    if(GOOGIE_CUR_LANG == null)
        GOOGIE_CUR_LANG = GOOGIE_DEFAULT_LANG;
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
    if(this.show_menu_observer)
        this.show_menu_observer(this);
    if(!AJS.isDefined(ofst_top))
        ofst_top = 18;  // modified by roundcube
    if(!AJS.isDefined(ofst_left))
        ofst_left = 22; // modified by roundcube

    this.createLangWindow();
    AJS.getBody().appendChild(this.language_window);

    var abs_pos = AJS.absolutePosition(elm);
    AJS.showElement(this.language_window);
    AJS.setTop(this.language_window, (abs_pos.y+ofst_top));
    AJS.setLeft(this.language_window, (abs_pos.x+ofst_left-this.language_window.offsetWidth));

    this.highlightCurSel();
    this.language_window.style.visibility = "visible";
}

GoogieSpell.prototype.createChangeLangPic = function() {
    var img = AJS.IMG({'src': this.img_dir + 'change_lang.gif', 'alt': "Change language"});
    img.googie_action_btn = "1";
    var switch_lan = AJS.SPAN({'class': 'googie_lang_3d_on', 'style': 'padding-left: 6px;'}, img);

    var fn = function(e) {
        var elm = AJS.getEventElm(e);
        if(AJS.nodeName(elm) == 'img')
            elm = elm.parentNode;
        if(elm.className == "googie_lang_3d_click") {
            elm.className = "googie_lang_3d_on";
            this.hideLangWindow();
        }
        else {
            elm.className = "googie_lang_3d_click";
            this.showLangWindow(switch_lan);
        }
    }

    AJS.AEV(switch_lan, "click", AJS.$b(fn, this));
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


//////
// State functions
/////
GoogieSpell.prototype.flashNoSpellingErrorState = function(on_finish) {
    var no_spell_errors;

    if(on_finish) {
        var fn = function() {
            on_finish();
            this.checkSpellingState();
        };
        no_spell_errors = fn;
    }
    else
        no_spell_errors = this.checkSpellingState;

    this.setStateChanged("no_error_found");

    if(this.main_controller) {
        AJS.hideElement(this.switch_lan_pic);

        var dummy = AJS.IMG({'src': this.img_dir + "blank.gif", 'style': 'height: 16px; width: 1px;'});
        var rsm = AJS.SPAN();
        rsm.innerHTML = this.lang_no_error_found;

        AJS.RCN(this.spell_span, AJS.SPAN(dummy, rsm));

        this.spell_span.className = "googie_check_spelling_ok";
        this.spell_span.style.textDecoration = "none";
        this.spell_span.style.cursor = "default";

        AJS.callLater(AJS.$b(no_spell_errors, this), 1200, [false]);
    }
}

GoogieSpell.prototype.resumeEditingState = function() {
    this.setStateChanged("resume_editing");

    //Change link text to resume
    if(this.main_controller) {
        AJS.hideElement(this.switch_lan_pic);
        var dummy = AJS.IMG({'src': this.img_dir + "blank.gif", 'style': 'height: 16px; width: 1px;'});
        var rsm = AJS.SPAN();
        rsm.innerHTML = this.lang_rsm_edt;
        AJS.RCN(this.spell_span, AJS.SPAN(dummy, rsm));
    
        var fn = function(e) {
            this.resumeEditing();
        }
        this.spell_span.onclick = AJS.$b(fn, this);

        this.spell_span.className = "googie_resume_editing";
    }

    try { this.edit_layer.scrollTop = this.ta_scroll_top; }
    catch(e) { }
}

GoogieSpell.prototype.checkSpellingState = function(fire) {
    if(!AJS.isDefined(fire) || fire)
        this.setStateChanged("spell_check");

    if(this.show_change_lang_pic)
        this.switch_lan_pic = this.createChangeLangPic();
    else
        this.switch_lan_pic = AJS.SPAN();

    var span_chck = this.createSpellDiv();
    var fn = function() {
        this.spellCheck();
    };

    if(this.custom_spellcheck_starter)
        span_chck.onclick = this.custom_spellcheck_starter;
    else {
        span_chck.onclick = AJS.$b(fn, this);
    }

    this.spell_span = span_chck;
    if(this.main_controller) {
        if(this.change_lang_pic_placement == "left")
            AJS.RCN(this.spell_container, span_chck, " ", this.switch_lan_pic);
        else
            AJS.RCN(this.spell_container, this.switch_lan_pic, " ", span_chck);
    }
    // modified by roundcube
    this.check_link = span_chck;
}


//////
// Misc. functions
/////
GoogieSpell.item_onmouseover = function(e) {
    var elm = AJS.getEventElm(e);
    if(elm.className != "googie_list_revert" && elm.className != "googie_list_close")
        elm.className = "googie_list_onhover";
    else
        elm.parentNode.className = "googie_list_onhover";
}
GoogieSpell.item_onmouseout = function(e) {
    var elm = AJS.getEventElm(e);
    if(elm.className != "googie_list_revert" && elm.className != "googie_list_close")
        elm.className = "googie_list_onout";
    else
        elm.parentNode.className = "googie_list_onout";
}

GoogieSpell.prototype.createCloseButton = function(c_fn) {
    return this.createButton(this.lang_close, 'googie_list_close', AJS.$b(c_fn, this));
}

GoogieSpell.prototype.createButton = function(name, css_class, c_fn) {
    var btn_row = AJS.TR();
    var btn = AJS.TD();

    btn.onmouseover = GoogieSpell.item_onmouseover;
    btn.onmouseout = GoogieSpell.item_onmouseout;

    var spn_btn;
    if(css_class != "") {
        spn_btn = AJS.SPAN({'class': css_class});
        spn_btn.innerHTML = name;
    }
    else {
        spn_btn = AJS.TN(name);
    }
    btn.appendChild(spn_btn);
    AJS.AEV(btn, "click", c_fn);
    btn_row.appendChild(btn);

    return btn_row;
}

GoogieSpell.prototype.removeIndicator = function(elm) {
    // modified by roundcube
    if (window.rcmail)
        rcmail.set_busy(false);
    //try { AJS.removeElement(this.indicator); }
    //catch(e) {}
}

GoogieSpell.prototype.appendIndicator = function(elm) {
    // modified by roundcube
    if (window.rcmail)
        rcmail.set_busy(true, 'checking');
  /*
    var img = AJS.IMG({'src': this.img_dir + 'indicator.gif', 'style': 'margin-right: 5px;'});
    AJS.setWidth(img, 16);
    AJS.setHeight(img, 16);
    this.indicator = img;
    img.style.textDecoration = "none";
    try {
        AJS.insertBefore(img, elm);
    }
    catch(e) {}
  */
}

GoogieSpell.prototype.createFocusLink = function(name) {
    return AJS.A({'href': 'javascript:;', name: name});
}
