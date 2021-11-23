import L from 'leaflet'
import createDetailMap from "./map/map-detail";
import {tileLayers} from "./map/map-layers";

import 'swiper/css';
import Swiper from 'swiper';



document.addEventListener('DOMContentLoaded', function() {
    const listingEl = document.querySelector('#detailMap')
    let listing = JSON.parse(listingEl.dataset.listing)

    if (listing.address) {

        createDetailMap({
            mapId: 'detailMap',
            mapZoom: 14,
            mapCenter: [listing.address.geo.latitude, listing.address.geo.longitude],
            circleShow: true,
            circlePosition: [listing.address.geo.latitude, listing.address.geo.longitude],
            tileLayer: tileLayers[5]
        })
    }


    var detailSlider = new Swiper('.detail-slider', {
        slidesPerView: 3,
        spaceBetween: 0,
        centeredSlides: true,
        loop: true,
        breakpoints: {
            991: {
                slidesPerView: 4
            },
            565: {
                slidesPerView: 3
            }
        },

        // If we need pagination
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true
        },

        // Navigation arrows
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
});