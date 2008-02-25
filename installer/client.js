
function toggleblock(id, link)
{
  var block = document.getElementById(id);
  
  return false;
}


function addhostfield()
{
  var container = document.getElementById('defaulthostlist');
  var row = document.createElement('div');
  var input = document.createElement('input');
  var link = document.createElement('a');
  
  input.name = '_default_host[]';
  input.size = '30';
  link.href = '#';
  link.onclick = function() { removehostfield(this.parentNode); return false };
  link.className = 'removelink';
  link.innerHTML = 'remove';
  
  row.appendChild(input);
  row.appendChild(link);
  container.appendChild(row);
}


function removehostfield(row)
{
  var container = document.getElementById('defaulthostlist');
  container.removeChild(row);
}


