/**
 * Copyright (C) 2012 KO GmbH <copyright@kogmbh.com>
 *
 * @licstart
 * This file is part of WebODF.
 *
 * WebODF is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License (GNU AGPL)
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * WebODF is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with WebODF.  If not, see <http://www.gnu.org/licenses/>.
 * @licend
 *
 * @source: http://www.webodf.org/
 * @source: https://github.com/kogmbh/WebODF/
 */

/*global runtime, document, odf, gui, console, webodf*/

function ODFViewerPlugin() {
    "use strict";

    function init(callback) {
/*
        var lib = document.createElement('script'),
            pluginCSS;

        lib.async = false;
        lib.src = './webodf.js';
        lib.type = 'text/javascript';
        lib.onload = function () {
*/
            runtime.loadClass('gui.HyperlinkClickHandler');
            runtime.loadClass('odf.OdfCanvas');
            runtime.loadClass('ops.Session');
            runtime.loadClass('gui.CaretManager');
            runtime.loadClass("gui.HyperlinkTooltipView");
            runtime.loadClass('gui.SessionController');
            runtime.loadClass('gui.SvgSelectionView');
            runtime.loadClass('gui.SelectionViewManager');
            runtime.loadClass('gui.ShadowCursor');
            runtime.loadClass('gui.SessionView');

            callback();
/*
        };

        document.getElementsByTagName('head')[0].appendChild(lib);

        pluginCSS = document.createElement('link');
        pluginCSS.setAttribute("rel", "stylesheet");
        pluginCSS.setAttribute("type", "text/css");
        pluginCSS.setAttribute("href", "./ODFViewerPlugin.css");
        document.head.appendChild(pluginCSS);
*/
    }

    // that should probably be provided by webodf
    function nsResolver(prefix) {
        var ns = {
            'draw' : "urn:oasis:names:tc:opendocument:xmlns:drawing:1.0",
            'presentation' : "urn:oasis:names:tc:opendocument:xmlns:presentation:1.0",
            'text' : "urn:oasis:names:tc:opendocument:xmlns:text:1.0",
            'office' : "urn:oasis:names:tc:opendocument:xmlns:office:1.0"
        };
        return ns[prefix] || console.log('prefix [' + prefix + '] unknown.');
    }

    var self = this,
        pluginName = "WebODF",
        pluginURL = "http://webodf.org",
        odfCanvas = null,
        odfElement = null,
        initialized = false,
        root = null,
        documentType = null,
        pages = [],
        currentPage = null;

    this.initialize = function (viewerElement, documentUrl) {
        // If the URL has a fragment (#...), try to load the file it represents
        init(function () {
            var session,
                sessionController,
                sessionView,
                odtDocument,
                shadowCursor,
                selectionViewManager,
                caretManager,
                localMemberId = 'localuser',
                hyperlinkTooltipView,
                eventManager;

            odfElement = document.getElementById('canvas');
            odfCanvas = new odf.OdfCanvas(odfElement);
            odfCanvas.load(documentUrl);

            odfCanvas.addListener('statereadychange', function () {
                root = odfCanvas.odfContainer().rootElement;
                initialized = true;
                documentType = odfCanvas.odfContainer().getDocumentType(root);

                if (documentType === 'text') {
                    odfCanvas.enableAnnotations(true, false);

                    session = new ops.Session(odfCanvas);
                    odtDocument = session.getOdtDocument();
                    shadowCursor = new gui.ShadowCursor(odtDocument);
                    sessionController = new gui.SessionController(session, localMemberId, shadowCursor, {});
                    eventManager = sessionController.getEventManager();
                    caretManager = new gui.CaretManager(sessionController, odfCanvas.getViewport());
                    selectionViewManager = new gui.SelectionViewManager(gui.SvgSelectionView);
                    sessionView = new gui.SessionView({
                        caretAvatarsInitiallyVisible: false
                    }, localMemberId, session, sessionController.getSessionConstraints(), caretManager, selectionViewManager);
                    selectionViewManager.registerCursor(shadowCursor);
                    hyperlinkTooltipView = new gui.HyperlinkTooltipView(odfCanvas,
                        sessionController.getHyperlinkClickHandler().getModifier);
                    eventManager.subscribe("mousemove", hyperlinkTooltipView.showTooltip);
                    eventManager.subscribe("mouseout", hyperlinkTooltipView.hideTooltip);

                    var op = new ops.OpAddMember();
                    op.init({
                        memberid: localMemberId,
                        setProperties: {
                            fillName: runtime.tr("Unknown Author"),
                            color: "blue"
                        }
                    });
                    session.enqueue([op]);
                    sessionController.insertLocalCursor();
                }

                self.onLoad();
            });
        });
    };

    this.isSlideshow = function () {
        return documentType === 'presentation';
    };

    this.onLoad = function () {};

    this.getWidth = function () {
        return odfElement.clientWidth;
    };

    this.getHeight = function () {
        return odfElement.clientHeight;
    };

    this.fitToWidth = function (width) {
        odfCanvas.fitToWidth(width);
    };

    this.fitToHeight = function (height) {
        odfCanvas.fitToHeight(height);
    };

    this.fitToPage = function (width, height) {
        odfCanvas.fitToContainingElement(width, height);
    };

    this.fitSmart = function (width) {
        odfCanvas.fitSmart(width);
    };

    this.getZoomLevel = function () {
        return odfCanvas.getZoomLevel();
    };

    this.setZoomLevel = function (value) {
        odfCanvas.setZoomLevel(value);
    };

    // return a list of tuples (pagename, pagenode)
    this.getPages = function () {
        var pageNodes = Array.prototype.slice.call(root.getElementsByTagNameNS(nsResolver('draw'), 'page')),
            pages  = [],
            i,
            tuple;

        for (i = 0; i < pageNodes.length; i += 1) {
            tuple = [
                pageNodes[i].getAttribute('draw:name'),
                pageNodes[i]
            ];
            pages.push(tuple);
        }
        return pages;
    };

    this.showPage = function (n) {
        odfCanvas.showPage(n);
    };

    this.getPluginName = function () {
        return pluginName;
    };

    this.getPluginVersion = function () {
        var version;

        if (String(typeof webodf) !== "undefined") {
            version = webodf.Version;
        } else {
            version = "Unknown";
        }

        return version;
    };

    this.getPluginURL = function () {
        return pluginURL;
    };
}
