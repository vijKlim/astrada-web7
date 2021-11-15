import React from 'react'
import { render } from 'react-dom'

import Autocomplete from '../components/Autocomplete'

const search = document.getElementById('search-listings')

if (search) {

    render(
        <Autocomplete
            baseURL="/admin/listings/search?format=json"
            placeholder="Search listings…"
            onSuggestionSelected={ (selected) => {
                window.location.href = window.Routing.generate('admin_business_listing', {
                    businessId: selected.businessId,
                    listingId: selected.id,
                })
            }}
            clearOnSelect={ true } />
        , search)
}
