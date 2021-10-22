import MapHelper from '../MapHelper'

import '../mapboard/mapboard.scss'

let map
let form
let markers = {
    listing: null,
}

const markerIcons = {
    listing:  { icon: 'podcast', color: '#E74C3C' },
}

function createMarker(location, addressType) {

    if (!map) {
        return
    }

    const { icon, color } = markerIcons[addressType]
    if (markers[addressType]) {
        map.removeLayer(markers[addressType])
    }
    markers[addressType] = MapHelper.createMarker({
        lat: location.latitude,
        lng: location.longitude
    }, icon, 'marker', color)
    markers[addressType].addTo(map)

    MapHelper.fitToLayers(map, _.filter(markers))
}

$(function() {

    const listingEl = document.querySelector('#map-listing')
    let listing = JSON.parse(listingEl.dataset.listing)

    if (listing.address) {
        createMarker({
            latitude: listing.address.geo.latitude,
            longitude: listing.address.geo.longitude
        }, 'listing')
    }
    if (document.getElementById('map-listing')) {
        map = MapHelper.init('map-listing')
    }

})