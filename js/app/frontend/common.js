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

import 'select2';

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

document.addEventListener('DOMContentLoaded', function() {

    initTheme();

    // Set global timezone used in Moment.js
    const timezone = document.querySelector('body').dataset.timezone
    setTimezone(timezone)



    const inputs = document.querySelectorAll('[data-widget="address-input"]')
    if (inputs.length > 0) {

        const addressElements = {
            latitude: '$1ddress_latitude',
            longitude: '$1ddress_longitude',
            postalCode: '$1ddress_postalCode',
            addressLocality: '$1ddress_addressLocality',
        }

        inputs.forEach(el => {

            // Try to build an address object
            let address = {
                streetAddress: el.value
            }
            for (const addressProp in addressElements) {
                const addressEl = document.getElementById(
                    el.getAttribute('id').replace(/([aA])ddress_streetAddress/, addressElements[addressProp])
                )
                if (addressEl) {
                    address = {
                        ...address,
                        [addressProp]: addressEl.value
                    }
                }
            }

            address = {
                ...address,
                geo: {
                    latitude: address.latitude,
                    longitude: address.longitude,
                }
            }

            new AddressAutosuggest(
                el.closest('.form-group'),
                {
                    required: el.required,
                    address,
                    inputProps: {
                        id: el.getAttribute('id'),
                        name: el.getAttribute('name'),
                    },
                    onAddressSelected: (text, address) => {
                        for (const addressProp in addressElements) {
                            const addressEl = document.getElementById(
                                el.getAttribute('id').replace(/([aA])ddress_streetAddress/, addressElements[addressProp])
                            )
                            if (addressEl) {
                                addressEl.value = address[addressProp]
                            }
                        }
                    }
                }
            )
        })
    }

})