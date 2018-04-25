import kudus from '../data/kudus.json'
import bae from '../data/bae.json'
import dawe from '../data/dawe.json'
import gebog from '../data/gebog.json'
import jati from '../data/jati.json'
import jekulo from '../data/jekulo.json'
import kaliwungu from '../data/kaliwungu.json'
import kota from '../data/kota.json'
import mejobo from '../data/mejobo.json'
import undaan from '../data/undaan.json'
var marker
var nc

export function placeMarker(map, location) {
  if (marker) {
    marker.setPosition(location);
  } else {
    marker = new google.maps.Marker({
      position: location,
      map: map
    });


  }
}
export function setBound(map, circleSelector=false){
  var areas = [bae,dawe,gebog,jati,jekulo,kaliwungu,kota,mejobo,undaan,kudus]
  for (var i in areas) {
    // Construct the polygon.
    var area = new google.maps.Polygon({
      paths: areas[i],
      strokeColor: '#0000FF',
      strokeOpacity: 0.3,
      strokeWeight: 1,
      fillColor: '#0000FF',
      fillOpacity: 0.1
    });
    area.setMap(map);
  }
  if(circleSelector){
    google.maps.event.addListener(area, "click", function(event) {
      placeMarker(map,event.latLng)
    })
  }

}
