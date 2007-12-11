
var rc_client = tinyMCEPopup.windowOpener.rcube_webmail_client;
if (rc_client.gui_objects.attachmentlist)
{
   var tinyMCEImageList = new Array();
   var attachElems = rc_client.gui_objects.attachmentlist.getElementsByTagName("li");
   for (i = 0; i < attachElems.length; i++)
   {
      var liElem = attachElems[i];
      var fname = attachElems[i].id;
      for (j = 0; j < liElem.childNodes.length; j++)
      {
         if (liElem.childNodes[j].nodeName == "#text")
         {
            fname = liElem.childNodes[j].nodeValue;
         }
      }
      tinyMCEImageList.push([fname, rc_client.env.comm_path+'&_action=display-attachment&_file='+attachElems[i].id]);
   }
};
