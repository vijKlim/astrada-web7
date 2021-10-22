import React from 'react'
import { render } from 'react-dom'

import Autocomplete from '../components/Autocomplete'

const search = document.getElementById('search-businesses')

if (search) {

    render(
        <Autocomplete
            baseURL="/admin/businesses/search?format=json"
            placeholder="Search businesses…"
            onSuggestionSelected={ (selected) => {
                window.location.href = window.Routing.generate('admin_business', {
                    id: selected.id
                })
            }}
            clearOnSelect={ true } />
        , search)
}
