var aircraft = [],
    flightpaths = [],
    map;

const mapElem = document.querySelector('#content');
const apiUrl = 'http://www.vbbmf.co.uk/api/';

mapElem.style.height = "900px";

function initMap() {

    $('.site-info').append(' - VATSIM Map by <a href="https://adameastwood.com" target="_blank">AdamEastwood.com</a>');

    map = new google.maps.Map(document.getElementById('content'), {
        center: {
            lat: 53.1005294,
            lng: -0.1707672
        },
        disableDefaultUI: false,
        streetViewControl: false,
        fullscreenControl: false,
        mapTypeControl: false,
        mapTypeControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER
        },
        zoom: 8
    });

    const baseLatLng = {
        lat: 53.1005294,
        lng: -0.1707672
    };

    var base = new google.maps.Marker({
        position: baseLatLng,
        map: map,
        zIndex: 1
    });

    setInterval(() => {
        updateAircraft();
    }, 15000);

}

function updateAircraft() {
    let response = null;

    aircraft.forEach(p => {
        p.setMap(null);
    });

    SendGETRequest(apiUrl).then((success, error) => {
            const apiData = JSON.parse(success);

            apiData.users.forEach(p => {
                if(!p.callsign) 
                    return;

                aircraftMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(p.latitude, p.longitude),
                    content: parent,
                    icon: {
                        url: ProcessIcon(p),
                        label: p.callsign,
                        anchor: new google.maps.Point(10, 10)
                    },
                    map: map,
                    zIndex: ProcessZIndex(p)
                });

                aircraft.push(aircraftMarker);

                google.maps.event.addDomListener(aircraftMarker, 'click', function () {
                    console.table(p);
                    if (p.clienttype === 'ATC') {
                        alert('Air Traffic Controller: ' + p.callsign);
                    }
                    else {
                        
                        flightpaths.forEach(f => {
                            f.setMap(null);
                        });

                        const depAirport = apiData.airports.find(airport => airport.iata == p.planned_depairport);
                        const arrAirport = apiData.airports.find(airport => airport.iata == p.planned_destairport);

                        if(depAirport.latitude && depAirport.longitude && arrAirport.latitude && arrAirport.longitude) {

                            const flightPlanCoordinates = [
                                { lat: parseFloat(depAirport.latitude), lng: parseFloat(depAirport.longitude) },
                                { lat: parseFloat(p.latitude), lng: parseFloat(p.longitude) },
                                { lat: parseFloat(arrAirport.latitude), lng: parseFloat(arrAirport.longitude) },
                            ];
                            
                            const flightPath = new google.maps.Polyline({
                                map: map,
                                path: flightPlanCoordinates,
                                geodesic: true,
                                strokeColor: "#FF0000",
                                strokeOpacity: 1.0,
                                strokeWeight: 2
                            });

                            flightpaths.push(flightPath);
                        }
                        else {
                            alert('No route planned');
                        }
                    }
                });
            });
    });
}

function ProcessIcon(p) {
    const assetUrl = 'https://adameastwood.com/.vbbmf.co.uk/imgs/';

    var icon = (p.clienttype == 'PILOT') ? p.iconHeading + '.png' : 'air-traffic-control-airplane-airport-control-tower-airplane-thumb.jpg';

    if (p.callsign.substring(0, 3) === 'MEM' && p.clienttype === 'PILOT') {
        icon = 'red.png';
    }

    return assetUrl + icon;
}

function ProcessZIndex(p) {
    var zIndex = (p.callsign.substring(0, 3) === 'MEM') ? 2 : 1;
    return zIndex;
}

async function SendGETRequest(URL) {
    return new Promise((resolve, reject) => {
        $.ajax({
            type: "GET",
            url: URL,
            complete: function (response) {

                switch (response.status) {
                    case 200:
                        resolve(response.responseText);
                        break;

                    default:
                        reject('GET request failed');
                        break;
                }
            }
        });
    });
}

$(document).ready(function () {
    initMap();
    updateAircraft();
});