/**
 * Copyright (C) 2012-2015 KO GmbH <copyright@kogmbh.com>
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

/*
 * This file is a derivative from a part of Mozilla's PDF.js project. The
 * original license header follows.
 */

/* Copyright 2012 Mozilla Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*global document, window*/

function Viewer(viewerPlugin, parameters) {
    "use strict";

    var self = this,
        kScrollbarPadding = 40,
        kMinScale = 0.25,
        kMaxScale = 4.0,
        kDefaultScaleDelta = 1.1,
        kDefaultScale = 'auto',
        presentationMode = false,
        isFullScreen = false,
        initialized = false,
        isSlideshow = false,
        url,
        viewerElement = document.getElementById('viewer'),
        canvasContainer = document.getElementById('canvasContainer'),
        overlayNavigator = document.getElementById('overlayNavigator'),
        titlebar = document.getElementById('titlebar'),
        toolbar = document.getElementById('toolbarContainer'),
        pageSwitcher = document.getElementById('toolbarLeft'),
        zoomWidget = document.getElementById('toolbarMiddleContainer'),
        scaleSelector = document.getElementById('scaleSelect'),
        dialogOverlay = document.getElementById('dialogOverlay'),
        toolbarRight = document.getElementById('toolbarRight'),
        aboutDialog,
        pages = [],
        currentPage,
        scaleChangeTimer,
        touchTimer,
        toolbarTouchTimer,
        /**@const*/
        UI_FADE_DURATION = 5000;

    function isBlankedOut() {
        return (blanked.style.display === 'block');
    }

    function initializeAboutInformation() {
        var aboutDialogCentererTable, aboutDialogCentererCell, aboutButton, pluginName, pluginVersion, pluginURL;

        if (viewerPlugin) {
            pluginName = viewerPlugin.getPluginName();
            pluginVersion = viewerPlugin.getPluginVersion();
            pluginURL = viewerPlugin.getPluginURL();
        }

        // Create dialog
        aboutDialogCentererTable = document.createElement('div');
        aboutDialogCentererTable.id = "aboutDialogCentererTable";
        aboutDialogCentererCell = document.createElement('div');
        aboutDialogCentererCell.id = "aboutDialogCentererCell";
        aboutDialog = document.createElement('div');
        aboutDialog.id = "aboutDialog";
        aboutDialog.innerHTML =
            "<h1>ViewerJS</h1>" +
            "<p>Open Source document viewer for webpages, built with HTML and JavaScript.</p>" +
            "<p>Learn more and get your own copy on the <a href=\"http://viewerjs.org/\" target=\"_blank\">ViewerJS website</a>.</p>" +
            (viewerPlugin ? ("<p>Using the <a href = \""+ pluginURL + "\" target=\"_blank\">" + pluginName + "</a> " +
                            "(<span id = \"pluginVersion\">" + pluginVersion + "</span>) " +
                            "plugin to show you this document.</p>")
                         : "") +
            "<p>Supported by <a href=\"http://nlnet.nl\" target=\"_blank\"><br><img src=\"images\/nlnet.png\" width=\"160\" height=\"60\" alt=\"NLnet Foundation\"></a></p>" +
            "<p>Made by <a href=\"http://kogmbh.com\" target=\"_blank\"><br><img src=\"images\/kogmbh.png\" width=\"172\" height=\"40\" alt=\"KO GmbH\"></a></p>" +
            "<button id = \"aboutDialogCloseButton\" class = \"toolbarButton textButton\">Close</button>";
        dialogOverlay.appendChild(aboutDialogCentererTable);
        aboutDialogCentererTable.appendChild(aboutDialogCentererCell);
        aboutDialogCentererCell.appendChild(aboutDialog);

        // Create button to open dialog that says "ViewerJS"
        aboutButton = document.createElement('button');
        aboutButton.id = "about";
        aboutButton.className = "toolbarButton textButton about";
        aboutButton.title = "About";
        aboutButton.innerHTML = "ViewerJS"
        toolbarRight.appendChild(aboutButton);

        // Attach events to the above
        aboutButton.addEventListener('click', function () {
                showAboutDialog();
        });
        document.getElementById('aboutDialogCloseButton').addEventListener('click', function () {
                hideAboutDialog();
        });

    }

    function showAboutDialog() {
        dialogOverlay.style.display = "block";
    }

    function hideAboutDialog() {
        dialogOverlay.style.display = "none";
    }

    function selectScaleOption(value) {
        // Retrieve the options from the zoom level <select> element
        var options = scaleSelector.options,
            option,
            predefinedValueFound = false,
            i;

        for (i = 0; i < options.length; i += 1) {
            option = options[i];
            if (option.value !== value) {
                option.selected = false;
                continue;
            }
            option.selected = true;
            predefinedValueFound = true;
        }
        return predefinedValueFound;
    }

    function getPages() {
        return viewerPlugin.getPages();
    }

    function setScale(val, resetAutoSettings, noScroll) {
        if (val === self.getZoomLevel()) {
            return;
        }

        self.setZoomLevel(val);

        var event = document.createEvent('UIEvents');
        event.initUIEvent('scalechange', false, false, window, 0);
        event.scale = val;
        event.resetAutoSettings = resetAutoSettings;
        window.dispatchEvent(event);
    }

    function onScroll() {
        var pageNumber;

        if (viewerPlugin.onScroll) {
            viewerPlugin.onScroll();
        }
        if (viewerPlugin.getPageInView) {
            pageNumber = viewerPlugin.getPageInView();
            if (pageNumber) {
                currentPage = pageNumber;
                document.getElementById('pageNumber').value = pageNumber;
            }
        }
    }

    function delayedRefresh(milliseconds) {
        window.clearTimeout(scaleChangeTimer);
        scaleChangeTimer = window.setTimeout(function () {
            onScroll();
        }, milliseconds);
    }

    function parseScale(value, resetAutoSettings, noScroll) {
        var scale,
            maxWidth,
            maxHeight;

        if (value === 'custom') {
            scale = parseFloat(document.getElementById('customScaleOption').textContent) / 100;
        } else {
            scale = parseFloat(value);
        }

        if (scale) {
            setScale(scale, true, noScroll);
            delayedRefresh(300);
            return;
        }

        maxWidth = canvasContainer.clientWidth - kScrollbarPadding;
        maxHeight = canvasContainer.clientHeight - kScrollbarPadding;

        switch (value) {
        case 'page-actual':
            setScale(1, resetAutoSettings, noScroll);
            break;
        case 'page-width':
            viewerPlugin.fitToWidth(maxWidth);
            break;
        case 'page-height':
            viewerPlugin.fitToHeight(maxHeight);
            break;
        case 'page-fit':
            viewerPlugin.fitToPage(maxWidth, maxHeight);
            break;
        case 'auto':
            if (viewerPlugin.isSlideshow()) {
                viewerPlugin.fitToPage(maxWidth + kScrollbarPadding, maxHeight + kScrollbarPadding);
            } else {
                viewerPlugin.fitSmart(maxWidth);
            }
            break;
        }

        selectScaleOption(value);
        delayedRefresh(300);
    }

    function readZoomParameter(zoom) {
        var validZoomStrings = ["auto", "page-actual", "page-width"],
            number;

        if (validZoomStrings.indexOf(zoom) !== -1) {
            return zoom;
        }
        number = parseFloat(zoom);
        if (number && kMinScale <= number && number <= kMaxScale) {
            return zoom;
        }
        return kDefaultScale;
    }

    this.initialize = function () {
        var initialScale,
            element;

        initialScale = readZoomParameter(parameters.zoom);

        url = parameters.documentUrl;
        document.title = parameters.filename;
        var documentName = document.getElementById('documentName');
        documentName.innerHTML = "";
        documentName.appendChild(documentName.ownerDocument.createTextNode(parameters.filename));

        viewerPlugin.onLoad = function () {
//            document.getElementById('pluginVersion').innerHTML = viewerPlugin.getPluginVersion();
            isSlideshow = viewerPlugin.isSlideshow();
            if (isSlideshow) {
                // Slideshow pages should be centered
                canvasContainer.classList.add("slideshow");
                // Show page nav controls only for presentations
                pageSwitcher.style.visibility = 'visible';
            } else {
                // For text documents, show the zoom widget.
                zoomWidget.style.visibility = 'visible';
                // Only show the page switcher widget if the plugin supports page numbers
                if (viewerPlugin.getPageInView) {
                    pageSwitcher.style.visibility = 'visible';
                }
            }

            initialized = true;
            pages = getPages();
            document.getElementById('numPages').innerHTML = 'of ' + pages.length;

            self.showPage(1);

            // Set default scale
            parseScale(initialScale);

            canvasContainer.onscroll = onScroll;
            delayedRefresh();
        };

        viewerPlugin.initialize(canvasContainer, url);
    };

    /**
     * Shows the 'n'th page. If n is larger than the page count,
     * shows the last page. If n is less than 1, shows the first page.
     * @return {undefined}
     */
    this.showPage = function (n) {
        if (n <= 0) {
            n = 1;
        } else if (n > pages.length) {
            n = pages.length;
        }

        viewerPlugin.showPage(n);

        currentPage = n;
        document.getElementById('pageNumber').value = currentPage;
    };

    /**
     * Shows the next page. If there is no subsequent page, does nothing.
     * @return {undefined}
     */
    this.showNextPage = function () {
        self.showPage(currentPage + 1);
    };

    /**
     * Shows the previous page. If there is no previous page, does nothing.
     * @return {undefined}
     */
    this.showPreviousPage = function () {
        self.showPage(currentPage - 1);
    };

    /**
     * Attempts to 'download' the file.
     * @return {undefined}
     */
    this.download = function () {
        var documentUrl = url.split('#')[0];
        documentUrl += '#viewer.action=download';
        window.open(documentUrl, '_parent');
    };

    /**
     * Toggles the fullscreen state of the viewer
     * @return {undefined}
     */
    this.toggleFullScreen = function () {
        var elem = viewerElement;
        if (!isFullScreen) {
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.mozRequestFullScreen) {
                elem.mozRequestFullScreen();
            } else if (elem.webkitRequestFullscreen) {
                elem.webkitRequestFullscreen();
            } else if (elem.webkitRequestFullScreen) {
                elem.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
            } else if (elem.msRequestFullscreen) {
                elem.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.cancelFullScreen) {
                document.cancelFullScreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    };
 
    /**
     * Toggles the presentation mode of the viewer.
     * Presentation mode involves fullscreen + hidden UI controls
     */
    this.togglePresentationMode = function () {
        var overlayCloseButton = document.getElementById('overlayCloseButton');

        if (!presentationMode) {
            titlebar.style.display = toolbar.style.display = 'none';
            overlayCloseButton.style.display = 'block';
            canvasContainer.classList.add('presentationMode');
            isSlideshow = true;
            canvasContainer.onmousedown = function (event) {
                event.preventDefault();
            };
            canvasContainer.oncontextmenu = function (event) {
                event.preventDefault();
            };
            canvasContainer.onmouseup = function (event) {
                event.preventDefault();
                if (event.which === 1) {
                    self.showNextPage();
                } else {
                    self.showPreviousPage();
                }
            };
            parseScale('page-fit');
        } else {
            if (isBlankedOut()) {
                leaveBlankOut();
            }
            titlebar.style.display = toolbar.style.display = 'block';
            overlayCloseButton.style.display = 'none';
            canvasContainer.classList.remove('presentationMode');
            canvasContainer.onmouseup = function () {};
            canvasContainer.oncontextmenu = function () {};
            canvasContainer.onmousedown = function () {};
            parseScale('auto');
            isSlideshow = viewerPlugin.isSlideshow();
        }

        presentationMode = !presentationMode;
    };

    /**
     * Gets the zoom level of the document
     * @return {!number}
     */
    this.getZoomLevel = function () {
        return viewerPlugin.getZoomLevel();
    };

    /**
     * Set the zoom level of the document
     * @param {!number} value
     * @return {undefined}
     */
    this.setZoomLevel = function (value) {
        viewerPlugin.setZoomLevel(value);
    };

    /**
     * Zoom out by 10 %
     * @return {undefined}
     */
    this.zoomOut = function () {
        // 10 % decrement
        var newScale = (self.getZoomLevel() / kDefaultScaleDelta).toFixed(2);
        newScale = Math.max(kMinScale, newScale);
        parseScale(newScale, true);
    };

    /**
     * Zoom in by 10%
     * @return {undefined}
     */
    this.zoomIn = function () {
        // 10 % increment
        var newScale = (self.getZoomLevel() * kDefaultScaleDelta).toFixed(2);
        newScale = Math.min(kMaxScale, newScale);
        parseScale(newScale, true);
    };

    function cancelPresentationMode() {
        if (presentationMode && !isFullScreen) {
            self.togglePresentationMode();
        }
    }

    function handleFullScreenChange() {
        isFullScreen = !isFullScreen;
        cancelPresentationMode();
    }

    function showOverlayNavigator() {
        if (isSlideshow) {
            overlayNavigator.className = 'viewer-touched';
            window.clearTimeout(touchTimer);
            touchTimer = window.setTimeout(function () {
                overlayNavigator.className = '';
            }, UI_FADE_DURATION);
        }
    }

    /**
     * @param {!boolean} timed Fade after a while
     */
    function showToolbars() {
        titlebar.classList.add('viewer-touched');
        toolbar.classList.add('viewer-touched');
        window.clearTimeout(toolbarTouchTimer);
        toolbarTouchTimer = window.setTimeout(function () {
            hideToolbars();
        }, UI_FADE_DURATION);
    }

    function hideToolbars() {
        titlebar.classList.remove('viewer-touched');
        toolbar.classList.remove('viewer-touched');
    }

    function toggleToolbars() {
        if (titlebar.classList.contains('viewer-touched')) {
            hideToolbars();
        } else {
            showToolbars();
        }
    }

    function blankOut(value) {
        blanked.style.display = 'block';
        blanked.style.backgroundColor = value;
        hideToolbars();
    }

    function leaveBlankOut() {
        blanked.style.display = 'none';
        toggleToolbars();
    }

    function init() {

//        initializeAboutInformation();
        if (viewerPlugin) {

            self.initialize();

            if (!(document.exitFullscreen || document.cancelFullScreen || document.mozCancelFullScreen || document.webkitExitFullscreen || document.webkitCancelFullScreen || document.msExitFullscreen)) {
                document.getElementById('fullscreen').style.visibility = 'hidden';
                document.getElementById('presentation').style.visibility = 'hidden';
            }

            document.getElementById('overlayCloseButton').addEventListener('click', self.toggleFullScreen);
            document.getElementById('fullscreen').addEventListener('click', self.toggleFullScreen);
            document.getElementById('presentation').addEventListener('click', function () {
                if (!isFullScreen) {
                    self.toggleFullScreen();
                }
                self.togglePresentationMode();
            });

            document.addEventListener('fullscreenchange', handleFullScreenChange);
            document.addEventListener('webkitfullscreenchange', handleFullScreenChange);
            document.addEventListener('mozfullscreenchange', handleFullScreenChange);
            document.addEventListener('MSFullscreenChange', handleFullScreenChange);

            document.getElementById('download').addEventListener('click', function () {
                self.download();
            });

            document.getElementById('zoomOut').addEventListener('click', function () {
                self.zoomOut();
            });

            document.getElementById('zoomIn').addEventListener('click', function () {
                self.zoomIn();
            });

            document.getElementById('previous').addEventListener('click', function () {
                self.showPreviousPage();
            });

            document.getElementById('next').addEventListener('click', function () {
                self.showNextPage();
            });

            document.getElementById('previousPage').addEventListener('click', function () {
                self.showPreviousPage();
            });

            document.getElementById('nextPage').addEventListener('click', function () {
                self.showNextPage();
            });

            document.getElementById('pageNumber').addEventListener('change', function () {
                self.showPage(this.value);
            });

            document.getElementById('scaleSelect').addEventListener('change', function () {
                parseScale(this.value);
            });

            canvasContainer.addEventListener('click', showOverlayNavigator);
            overlayNavigator.addEventListener('click', showOverlayNavigator);
            canvasContainer.addEventListener('click', toggleToolbars);
            titlebar.addEventListener('click', showToolbars);
            toolbar.addEventListener('click', showToolbars);

            window.addEventListener('scalechange', function (evt) {
                var customScaleOption = document.getElementById('customScaleOption'),
                    predefinedValueFound = selectScaleOption(String(evt.scale));

                customScaleOption.selected = false;

                if (!predefinedValueFound) {
                    customScaleOption.textContent = Math.round(evt.scale * 10000) / 100 + '%';
                    customScaleOption.selected = true;
                }
            }, true);

            window.addEventListener('resize', function (evt) {
                if (initialized &&
                          (document.getElementById('pageWidthOption').selected ||
                          document.getElementById('pageAutoOption').selected)) {
                    parseScale(document.getElementById('scaleSelect').value);
                }
                showOverlayNavigator();
            });

            window.addEventListener('keydown', function (evt) {
                var key = evt.keyCode,
                    shiftKey = evt.shiftKey;

                // blanked-out mode?
                if (isBlankedOut()) {
                    switch (key) {
                    case 16: // Shift
                    case 17: // Ctrl
                    case 18: // Alt
                    case 91: // LeftMeta
                    case 93: // RightMeta
                    case 224: // MetaInMozilla
                    case 225: // AltGr
                        // ignore modifier keys alone
                        break;
                    default:
                        leaveBlankOut();
                        break;
                    }
                } else {
                    switch (key) {
                    case 8: // backspace
                    case 33: // pageUp
                    case 37: // left arrow
                    case 38: // up arrow
                    case 80: // key 'p'
                        self.showPreviousPage();
                        break;
                    case 13: // enter
                    case 34: // pageDown
                    case 39: // right arrow
                    case 40: // down arrow
                    case 78: // key 'n'
                        self.showNextPage();
                        break;
                    case 32: // space
                        shiftKey ? self.showPreviousPage() : self.showNextPage();
                        break;
                    case 66:  // key 'b' blanks screen (to black) or returns to the document
                    case 190: // and so does the key '.' (dot)
                        if (presentationMode) {
                            blankOut('#000');
                        }
                        break;
                    case 87:  // key 'w' blanks page (to white) or returns to the document
                    case 188: // and so does the key ',' (comma)
                        if (presentationMode) {
                            blankOut('#FFF');
                        }
                        break;
                    case 36: // key 'Home' goes to first page
                        self.showPage(0);
                        break;
                    case 35: // key 'End' goes to last page
                        self.showPage(pages.length);
                        break;
                    }
                }
            });
        }
    }

    init();
}
