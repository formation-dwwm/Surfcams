/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
window.setInterval(function() {
  reloadImg()
}, 500);

function reloadImg() {
let ref = document.getElementById('brutal-beach-webcam-link');

ref.src = 'http://nexpa.ddns.net:8080/tmpfs/auto.jpg?rnd='+ Math.random();
}






  
    var map = L.map("mapSpots").setView([43.1, 5.95], 11);
    L.esri.basemapLayer("Topographic").addTo(map);
    //Brutal Beach
    var _circle = new L.marker([43.10889,5.81300]).addTo(map);
    _circle.bindPopup('<button type="button" class="btn btn-info" data-toggle="modal" data-target="#mod-1">Brutal Beach</button>');
    //Cap Saint-Louis
    var _circle1 = new L.marker([43.17795,5.67529]).addTo(map);
    _circle1.bindPopup('<button type="button" class="btn btn-info" data-toggle="modal" data-target="#mod-2">Cap Saint-Louis</button>');
    //Pin Ro
    var _circle2 = new L.marker([43.07015,5.90687]).addTo(map);
    _circle2.bindPopup('<button type="button" class="btn btn-info" data-toggle="modal" data-target="#mod-3">Pin Rolland</button>');
    //La Verne
    var _circle3 = new L.marker([43.07244,5.88033]).addTo(map);
    _circle3.bindPopup('<button type="button" class="btn btn-info" data-toggle="modal" data-target="#mod-4">La Verne</button>');
    //Fabregas
    var _circle4 = new L.marker([43.06926,5.87220]).addTo(map);
     _circle4.bindPopup('<button type="button" class="btn btn-info" data-toggle="modal" data-target="#mod-5">Fabreg</button>');


    var toggle = document.getElementById('fs');
    let cards = document.querySelector("#cards-container");
    let mymap = document.getElementById('map-spots-container');

    function isChecked(){
      if (toggle.checked){
        mymap.style.setProperty('display', 'none', 'important');
        cards.style.setProperty('display', 'flex', 'important');
      } 
      else {
        mymap.style.setProperty('display', 'flex', 'important');
        cards.style.setProperty('display', 'none', 'important');
      }
    };
    isChecked();
    toggle.addEventListener('click', isChecked);
      


      