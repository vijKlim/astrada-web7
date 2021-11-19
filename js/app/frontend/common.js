import React from 'react'
import { render } from 'react-dom'
import numbro from 'numbro'

// @see http://symfony.com/doc/3.4/frontend/encore/legacy-apps.html
const $ = require('jquery')
global.$ = global.jQuery = $



import '../../../assets/styles/frontend/style.scss'

import 'bootstrap';


import '../i18n'
import { setTimezone, getCurrencySymbol } from '../i18n'



import AddressAutosuggest from './components/AddressAutosuggest'

import { Input, ClearButton,Typeahead } from 'react-bootstrap-typeahead'; // ES2015

import 'select2';



import { NearbyListingSlides } from './components/NearbyListingSlides';

function initTheme()
{

    // ------------------------------------------------------- //
    // Adding fade effect to dropdowns
    // ------------------------------------------------------ //

    $.fn.slideDropdownUp = function () {
        $(this).fadeIn().css('transform', 'translateY(0)');
        return this;
    };
    $.fn.slideDropdownDown = function (movementAnimation) {

        if (movementAnimation) {
            $(this).fadeOut().css('transform', 'translateY(30px)');
        } else {
            $(this).hide().css('transform', 'translateY(30px)');
        }
        return this;
    };

    $('.navbar .dropdown').on('show.bs.dropdown', function (e) {
        $(this).find('.dropdown-menu').first().slideDropdownUp();
    });
    $('.navbar .dropdown').on('hide.bs.dropdown', function (e) {

        var movementAnimation = true;

        // if on mobile or navigation to another page
        if ($(window).width() < 992 || (e.clickEvent && e.clickEvent.target.tagName.toLowerCase() === 'a')) {
            movementAnimation = false;
        }

        $(this).find('.dropdown-menu').first().slideDropdownDown(movementAnimation);
    });
}

// ------------------------------------------------------- //
//   Inject SVG Sprite -
//   see more here
//   https://css-tricks.com/ajaxing-svg-sprite/
// ------------------------------------------------------ //
function injectSvgSprite(path) {

    var ajax = new XMLHttpRequest();
    ajax.open("GET", path, true);
    ajax.send();
    ajax.onload = function(e) {
        var div = document.createElement("div");
        div.className = 'd-none';
        div.innerHTML = ajax.responseText;
        document.body.insertBefore(div, document.body.childNodes[0]);
    }
}
// to avoid CORS issues when viewing using file:// protocol, using the demo URL for the SVG sprite
// use your own URL in production, please :)
// https://demo.bootstrapious.com/directory/1-0/icons/orion-svg-sprite.svg
//- injectSvgSprite('${path}icons/orion-svg-sprite.svg');
injectSvgSprite('/img/orion-svg-sprite.svg');




// const WrapUsePosition = (props) => {
//     const watch = true;
//     const {
//         latitude,
//         longitude,
//         speed,
//         timestamp,
//         accuracy,
//         error,
//     } = usePosition(watch);
//
// console.log(latitude,
//     longitude,
//     speed,
//     timestamp,
//     accuracy,
//     error)
//
//     const style = {
//         color: '#868e96'
//     };
//
//     return(
//         <Typeahead
//             id="toggle-example"
//             placeholder="Location"
//             onChange={(selected) => {
//                 // Handle selections...
//             }}
//             searchText={'Пошук'}
//             inputProps={{
//                 className: 'border-0 shadow-0',
//             }}
//             options={[ 'test', 'test2', 'test3' ]}
//
//         >
//             {({ onClear, selected }) => (
//
//                 <div className="rbt-aux">
//                     {!!selected.length && <ClearButton onClick={onClear} />}
//                     {!selected.length && <i className="fa fa-crosshairs" style={style}></i>}
//                 </div>
//             )}
//         </Typeahead>
//     )
// }


document.addEventListener('DOMContentLoaded', function() {

    initTheme();


    // Set global timezone used in Moment.js
    const timezone = document.querySelector('body').dataset.timezone
    setTimezone(timezone)

    //nearbyListingSliders
    const nearbyListingSlidesElement   = document.querySelector('#nearby-listing-slides')

    if(nearbyListingSlidesElement){
        render(

            <NearbyListingSlides />
            , nearbyListingSlidesElement
        )
    }


    // //test typeahead
    // const el   = document.querySelector('[data-element2]')
    //
    // render(
    //
    //     <WrapUsePosition/>
    //     , el
    // )

    // const inputs = document.querySelectorAll('[data-widget="address-input"]')
    // if (inputs.length > 0) {
    //
    //     const addressElements = {
    //         latitude: '$1ddress_latitude',
    //         longitude: '$1ddress_longitude',
    //         postalCode: '$1ddress_postalCode',
    //         addressLocality: '$1ddress_addressLocality',
    //     }
    //
    //     inputs.forEach(el => {
    //
    //         // Try to build an address object
    //         let address = {
    //             streetAddress: el.value
    //         }
    //         for (const addressProp in addressElements) {
    //             const addressEl = document.getElementById(
    //                 el.getAttribute('id').replace(/([aA])ddress_streetAddress/, addressElements[addressProp])
    //             )
    //             if (addressEl) {
    //                 address = {
    //                     ...address,
    //                     [addressProp]: addressEl.value
    //                 }
    //             }
    //         }
    //
    //         address = {
    //             ...address,
    //             geo: {
    //                 latitude: address.latitude,
    //                 longitude: address.longitude,
    //             }
    //         }
    //
    //         new AddressAutosuggest(
    //             el.closest('.form-group'),
    //             {
    //                 required: el.required,
    //                 address,
    //                 inputProps: {
    //                     id: el.getAttribute('id'),
    //                     name: el.getAttribute('name'),
    //                 },
    //                 onAddressSelected: (text, address) => {
    //                     for (const addressProp in addressElements) {
    //                         const addressEl = document.getElementById(
    //                             el.getAttribute('id').replace(/([aA])ddress_streetAddress/, addressElements[addressProp])
    //                         )
    //                         if (addressEl) {
    //                             addressEl.value = address[addressProp]
    //                         }
    //                     }
    //                 }
    //             }
    //         )
    //     })
    // }

})