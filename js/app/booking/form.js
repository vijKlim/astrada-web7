import React, { useState } from 'react'
import { render } from 'react-dom'
import { DatePicker } from 'antd'
import {setTimezone} from "../i18n";
import AddressAutosuggest from "../widgets/AddressAutosuggest";

document.addEventListener('DOMContentLoaded', function() {



    const bookingFormEl = document.getElementById('booking')

    let listings = JSON.parse(dashboardEl.dataset.listings)
    let listingsPagination = JSON.parse(dashboardEl.dataset.pagination)
    let searchformSchema = JSON.parse(dashboardEl.dataset.searchformSchema)

    // const cartTopElement = document.querySelector('#cart-top')
    // if (cartTopElement) {
    //   render(<CartTop url={ cartTopElement.dataset.url } href={ cartTopElement.dataset.href } />, cartTopElement)
    // }

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