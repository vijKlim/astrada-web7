import React from 'react'
import { render } from 'react-dom'
import { Provider } from 'react-redux'
import { Router, Route } from 'react-router'
import { createMemoryHistory } from "history";
import App from './components/App'
import { createStoreFromPreloadedState } from './redux/store'

import './mapboard.scss'
import {listingAdapter, listingListAdapter} from "../dashboard/redux";

function start() {

    const dashboardEl = document.getElementById('mapboard')

    let listings = JSON.parse(dashboardEl.dataset.listings)
    let listingsPagination = JSON.parse(dashboardEl.dataset.pagination)
    let searchformSchema = JSON.parse(dashboardEl.dataset.searchformSchema)


    let preloadedState = {
        entities: {
            listings: listingAdapter.upsertMany(
                listingAdapter.getInitialState(),
                listings
            ),

        },
        searchForm: searchformSchema,
        settings:{
            listingsPagination: listingsPagination
        }
    }

    const store = createStoreFromPreloadedState(preloadedState)

    const history = createMemoryHistory();

    render(
        <Provider store={store}>
            <Router history={history}>
                <Route path='/' component={App} />
            </Router>
        </Provider>,
        document.getElementById('mapboard'),

    )

}

start()

