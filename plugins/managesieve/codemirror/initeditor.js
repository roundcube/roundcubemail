var cmeditor;
if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    var textArea = document.getElementById('rawfiltersettxt');
    if (textArea) {
      cmeditor = CodeMirror.fromTextArea(textArea, {
        mode: 'sieve',
        lineNumbers: true,
        styleActiveLine: true
      });
      
      console.log("init done.");
      
      // fetching error line number from environment and setting the line background accordingly
      var errLine = Number(rcmail.env.sieve_error_line); 
      if (errLine !== NaN && errLine > 0) {
        console.log("Fehler in " + rcmail.env.sieve_error_line);
        cmeditor.addLineClass(errLine - 1, 'background', 'line-error');
      }
    }
  });
}