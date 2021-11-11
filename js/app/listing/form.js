import MapHelper from '../MapHelper'
import _ from 'lodash'

require('gasparesganga-jquery-loading-overlay')

import ListingForm from '../forms/listing'

import DropzoneWidget from "../widgets/Dropzone";
import PricePreview from './PricePreview'


let map
let form
let pricePreview

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

function removeMarker(addressType) {

    if (!map) {
        return
    }

    const marker = markers[addressType]

    if (!marker) {
        return
    }

    marker.removeFrom(map)

    MapHelper.fitToLayers(map, _.filter(markers))
}

if (document.getElementById('map')) {
    map = MapHelper.init('map')
}

form = new ListingForm('listing', {
    onReady: function(listing) {

        if (listing.address) {
            createMarker({
                latitude: listing.address.geo.latitude,
                longitude: listing.address.geo.longitude
            }, 'listing')
        }
    },
    onChange: function(listing) {

        if (listing.address) {
            createMarker({
                latitude: listing.address.geo.latitude,
                longitude: listing.address.geo.longitude
            }, 'listing')
        }else{
            removeMarker('listing')
        }

        this.disable()

        const updatePrice = new Promise((resolve) => {
            if (listing && pricePreview) {

                const listingAsPayload = {
                    ...listing
                }

                pricePreview.update(listingAsPayload).then(() => resolve())
            } else {
                resolve()
            }
        })

        Promise.all([
            updatePrice,
        ])
            .then(() => {
                form.enable()
            })
            // eslint-disable-next-line no-console
            .catch(e => console.error(e))
    }
})

$.getJSON(window.Routing.generate('profile_jwt'))
    .then(result => {
        $('form[name="listing"]').LoadingOverlay('hide')
        pricePreview = new PricePreview( { token: result.jwt })
    })

$(function() {

    const formData = document.querySelector('#listing-form-data')

    $('#listing_imageFile_delete').closest('.form-group').remove()

    const $formGroup = $('#listing_imageFile_file').closest('.form-group')

    $formGroup.empty()

    new DropzoneWidget($formGroup, {
        dropzone: {
            url: formData.dataset.actionUrl,
            params: {
                type: 'listing',
                id: formData.dataset.listingId
            }
        },
        image: formData.dataset.listingImage,
        size: [ 512, 512 ]
    })

})