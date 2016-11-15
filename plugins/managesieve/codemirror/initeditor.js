var cmeditor;

function createErrorElem(msg) 
{
  var marker = document.createElement("div");
  marker.style.color = "#822";
  marker.innerHTML = "●";
  marker.title = msg;
  return marker;
}

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    var textArea = document.getElementById('rawfiltersettxt');
    if (textArea) {
      cmeditor = CodeMirror.fromTextArea(textArea, {
        mode: 'sieve',
        lineNumbers: true,
        gutters: ["CodeMirror-linenumbers", "errorGutter"],
        styleActiveLine: true
      });
      
      // fetching errors from environment and setting the line background 
      // and a gutter element with the error message accordingly
      var errors = rcmail.env.sieve_errors; 
      if (errors !== undefined) {
        errors.forEach(function(err) {
          var lineNo = Number(err.line) - 1;
          cmeditor.addLineClass(lineNo, 'background', 'line-error');
          cmeditor.setGutterMarker(lineNo, 'errorGutter', createErrorElem(err.msg));
        });
      }
    }
  });
}
