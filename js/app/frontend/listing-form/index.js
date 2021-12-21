import { createStore } from 'redux'
import { createAction } from 'redux-actions'
import MapHelper from '../../MapHelper'
import _ from 'lodash'

require('gasparesganga-jquery-loading-overlay')

import ListingForm from './listing-form'

import DropzoneWidget from "../../widgets/Dropzone";

import {openEditor} from "../../product/image-editor";


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

form = new ListingForm('createListing','_listing', {
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

    }
})



// $(function() {
//
//     const formData = document.querySelector('#listing-form-data')
//
//     $('#listing_imageFile_delete').closest('.form-group').remove()
//
//     const $formGroup = $('#listing_imageFile_file').closest('.form-group')
//
//     $formGroup.empty()
//
//     new DropzoneWidget($formGroup, {
//         dropzone: {
//             url: formData.dataset.actionUrl,
//             params: {
//                 type: 'listing',
//                 id: formData.dataset.listingId
//             }
//         },
//         image: formData.dataset.listingImage,
//         size: [ 512, 512 ]
//     })
//
// })


const SET_IMAGES = '@listing/SET_IMAGES'
const setImages = createAction(SET_IMAGES)

const imageEditor = document.getElementById('image-editor')
const formData = document.querySelector('#listing-form-data')

if (imageEditor && formData) {

    const store = createStore((state = {}, action) => {

        switch (action.type) {
            case SET_IMAGES:

                return {
                    ...state,
                    images: action.payload,
                }
        }

        return state
    })

    store.dispatch(
        setImages(JSON.parse(formData.dataset.listingImages))
    )

    imageEditor.addEventListener('click', function(e) {
        e.preventDefault()
        openEditor({
            existingImages: store.getState().images,
            actionUrl: formData.dataset.actionUrl,
            productId: formData.dataset.listingId,
            productType: 'listing',
            onClose: (images) => store.dispatch(setImages(images)),
        })
    })
}