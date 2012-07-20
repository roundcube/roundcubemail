var Unity = null;

function unityReady() {
  // Integrate with Unity!
}

rcmail.addEventListener('init', function(evt) {
  Unity = external.getUnityObject(1.0);
  
  title = document.title.split(' ::')[0];
  url = window.location.href;
  end = url.lastIndexOf('/');
  icon = url.slice(0, end)+'/plugins/unity/media/logo.png';
 
  Unity.init({name: title,
              iconUrl: icon,
              onInit: unityReady});
});

