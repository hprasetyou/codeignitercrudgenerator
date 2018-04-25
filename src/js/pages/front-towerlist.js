import 'jquery'
import kudus from '../../data/kudus.json'
import {setBound, placeMarker} from '../map_func'


function geocodePlaceId(geocoder, map, latlng) {
  geocoder.geocode({
    'location': latlng
  }, function(results, status) {
    if (status === 'OK') {
      if (results[0]) {
        var loc = results[0];
        $('#Location,#District,#Village').val('')
        $('#Location').val(loc.formatted_address)
        $('#District').val(loc.address_components[2].long_name)
        $('#Village').val(loc.address_components[1].long_name)
        $('input').each(function(){
          if(!(this.value == '' || this.value == this.defaultValue)){
            $(this).attr('readonly','readonly')
          }
        })
      } else {
        window.alert('No results found');
      }
    } else {
      window.alert('Geocoder failed due to: ' + status);
    }
  });
}


function initMap() {
console.log('test')
  $.ajax({
    'url': '/index.php/get_all_cellplan?with_tower=1',
    'dataType': 'JSON'
  }).done(function(data) {
  console.log(data)
		var o = data.cellplan
		var rad = data.radius
    var towers = data.tower
    // Create the map.
    var geocoder = new google.maps.Geocoder;
    var map = new google.maps.Map(document.getElementById('map_area'), {
      zoom: 12,
      center: {
        lat: -6.801039,
        lng: 110.843246
      },
      streetViewControl: false,
      fullscreenControl: false,
      showRoadLabels: true,
      mapTypeId: 'terrain'
    });

    setBound(map,true)

    for (var i in towers){
      var tower = new google.maps.Marker({
      map:map,
      position: towers[i].LatLng,
      icon: '/public/assets/img/communication-tower-icon.png'
    });
    }
  })

}
window.initMap = initMap;
