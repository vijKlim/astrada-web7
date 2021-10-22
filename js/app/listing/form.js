import MapHelper from '../MapHelper'
import ListingForm from '../forms/listing'
import DeliveryZonePicker from "../components/DeliveryZonePicker";
import DropzoneWidget from "../widgets/Dropzone";

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

        // if (isValid(delivery)) {
        //
        //     this.disable()
        //
        //     const updateDistance = new Promise((resolve) => {
        //         route(delivery).then((infos) => {
        //             $('#delivery_distance').text(`${infos.kms} Km`)
        //             resolve()
        //         })
        //     })
        //
        //     const updatePrice = new Promise((resolve) => {
        //         if (delivery.store && pricePreview) {
        //
        //             const tasks = delivery.tasks.slice(0)
        //
        //             const deliveryAsPayload = {
        //                 ...delivery,
        //                 tasks: tasks.map(t => ({
        //                     ...t,
        //                     address: serializeAddress(t.address)
        //                 }))
        //             }
        //
        //             pricePreview.update(deliveryAsPayload).then(() => resolve())
        //         } else {
        //             resolve()
        //         }
        //     })
        //
        //     Promise.all([
        //         updateDistance,
        //         updatePrice,
        //     ])
        //         .then(() => {
        //             form.enable()
        //         })
        //         // eslint-disable-next-line no-console
        //         .catch(e => console.error(e))
        // }
    }
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