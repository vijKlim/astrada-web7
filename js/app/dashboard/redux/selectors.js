import { createSelector } from 'reselect'
import { filter, forEach, find, reduce, map, differenceWith, includes, mapValues } from 'lodash'
import Fuse from 'fuse.js'

import { listingAdapter, listingListAdapter } from './adapters'


import { isListingVisible} from './utils'

export const selectSelectedDate = state => state.logistics.date

const listingSelectors = listingAdapter.getSelectors((state) => state.logistics.entities.listings)
const listingListSelectors = listingListAdapter.getSelectors((state) => state.logistics.entities.listingLists)

export const selectSelectedListings = createSelector(
    listingSelectors.selectEntities,
    state => state.selectedListings,
    (listingsById, selectedListings) => selectedListings.map(id => listingsById[id])
)


export const selectAllListings = listingSelectors.selectAll

// FIXME
// This is not optimized
// Each time any task is updated, the tasks lists are looped over
// Also, it generates copies all the time
// Replace this with a selectTaskListItemsByUsername selector, used by the <TaskList> component
// https://redux.js.org/tutorials/essentials/part-6-performance-normalization#memoizing-selector-functions
export const selectListingLists = createSelector(
    listingListSelectors.selectEntities,
    listingSelectors.selectEntities,
    (listingListsById, listingsById) =>
        Object.values(listingListsById).map(listingList => {
            let newListingList = {...listingList}
            delete newListingList.itemIds

            newListingList.items = listingList.itemIds
                .filter(listingId => Object.prototype.hasOwnProperty.call(listingsById, listingId)) // a task with this id may be not loaded yet
                .map(listingId => listingsById[listingId])

            return newListingList
        })
)

const selectListingListByBusiness = (state, props) =>
    listingListSelectors.selectById(state, props.businessId)

// https://github.com/reduxjs/reselect#connecting-a-selector-to-the-redux-store
// https://redux.js.org/recipes/computing-derived-data
export const makeSelectListingListItemsByBusiness = () => {

    return createSelector(
        listingSelectors.selectEntities, // FIXME This is recalculated all the time
        selectListingListByBusiness,
        (listings, listingList) => {

            if (!listingList) {
                return []
            }

            return listingList.itemIds
                .filter(id => Object.prototype.hasOwnProperty.call(listings, id)) // a listing with this id may be not loaded yet
                .map(id => listings[id])
        }
    )
}

export const selectVisibleListingIds = createSelector(
    selectAllListings,
    state => state.settings.filters,
    selectSelectedDate,
    (listings, filters, date) => filter(listings, listing => isListingVisible(listing, filters, date)).map(listing => listing['@id'])
)

const fuseOptions = {
    shouldSort: true,
    includeScore: true,
    keys: [{
        name: 'id',
        weight: 0.7
    }, {
        name: 'address.name',
        weight: 0.1
    }, {
        name: 'address.streetAddress',
        weight: 0.1
    }]
}

export const selectFuseSearch = createSelector(
    selectAllListings,
    (listings) => new Fuse(listings, fuseOptions)
)