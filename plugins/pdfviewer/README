Roundcube inline PDF Viewer
===========================
The rendering of PDF documents is based on the pdf.js library
by andreasgal. See http://mozilla.github.com/pdf.js/ for more information.


INSTALLATION
------------

Add 'pdfviewer' to the list of plugins in the config/main.inc.php file
of your Roundcube installation.


BUILD (for developers only)
-----
Clone the git repository into a local directory:

$ git clone https://github.com/mozilla/pdf.js.git pdfjs
$ cd pdfjs

To actually build the viewer, node.js is required!

$ node make generic

The viewer is generated in build/generic/web/ and the pdf.js script in
build/generic/build/pdf.js. Copy build/generic/web/ to the plugin directory
<roundcubedir>/plugins/pdfviewer/viewer/ and also copy pdf.js into the viewer
directory:

$ cd <roundcubedir>/plugins/pdfviewer
$ cp -r <pdfjsdir>/build/generic/web viewer
$ cp <pdfjsdir>/build/generic/build/pdf.js viewer/pdf.js
$ cp <pdfjsdir>/build/generic/build/pdf.worker.js viewer/pdf.worker.js
$ rm viewer/*.pdf

Then apply the pdfjs-viewer.diff patch to adjust the viewer for the use
within Roundcube:

$ patch -p0 < pdfjs-viewer.diff

Optionally, compress the scripts using Google's Closure Compiler [1]
or the YUI Compressor [2].

$ <roundcubedir>/bin/jsshrink.sh viewer/pdf.js ECMASCRIPT5
$ <roundcubedir>/bin/jsshrink.sh viewer/viewer.js ECMASCRIPT5

This will create minimized versions in viewer/*.min.js which are
linked by the viewer.html template.

[1] http://closure-compiler.googlecode.com/
[2] http://developer.yahoo.com/yui/compressor/

