import React from 'react'
import { render } from 'react-dom'
import _ from 'lodash'

import store from './address-storage'
import AddressAutosuggest from '../frontend/components/AddressAutosuggest'
import UserAddress from "../UserAddress";

// We used to store a string in "search_address", but now, we want objects
// This function will cleanup legacy behavior
function resolveAddress() {

    const address = store.get('search_address')

    if (_.isObject(address)) {

        return address
    }

    store.remove('search_address')
}

window._paq = window._paq || []

document.querySelectorAll('[data-search="address"]').forEach((container) => {

    const el   = container.querySelector('[data-element]')
    const form = container.querySelector('[data-form]')

    if (el) {

        const addresses =
            container.dataset.addresses ? JSON.parse(container.dataset.addresses) : []

        const businesses =
            container.dataset.businesses ? JSON.parse(container.dataset.businesses) : []

        const view_type =
            container.dataset.view ? container.dataset.view : 'header_search'

        let inputProps = {className: 'form-control-sm border-0 shadow-0 bg-gray-200'}

        if(view_type == 'home_search'){
            inputProps = {className: 'form-control border-0 shadow-0'}
        }

        if(view_type == 'map_search'){
            inputProps = {className: 'form-control'}
        }

        render(
            <AddressAutosuggest
                inputProps={inputProps}
                address={ resolveAddress() }
                addresses={ addresses }
                businesses={ businesses }
                geohash={ store.get('search_geohash', '') }
                onAddressSelected={ (value, address, type) => {

                    const addressInput = form.querySelector('input[name="address"]')
                    const geohashInput = form.querySelector('input[name="geohash"]')

                    if (address.geohash !== geohashInput.value) {

                        if (type === 'address') {
                            if (!addressInput) {
                                const newAddressInput = document.createElement('input')
                                newAddressInput.setAttribute('type', 'hidden')
                                newAddressInput.setAttribute('name', 'address')
                                newAddressInput.value = btoa(address['@id'])
                                form.appendChild(newAddressInput)
                            }
                        }

                        if (type === 'prediction') {
                            if (addressInput) {
                                addressInput.parentNode.removeChild(addressInput)
                            }
                        }

                        store.set('search_geohash', address.geohash)
                        store.set('search_address', address)

                        const trackingCategory = container.dataset.trackingCategory
                        if (trackingCategory) {
                            window._paq.push(['trackEvent', trackingCategory, 'searchAddress', value])
                        }

                        geohashInput.value = address.geohash;


                        (new UserAddress()).addNew(address).then(value1 => {
                            form.submit()
                        })



                    }

                }}
                required={ false }
                preciseOnly={ false }
                reportValidity={ false } />, el)
    }

})
