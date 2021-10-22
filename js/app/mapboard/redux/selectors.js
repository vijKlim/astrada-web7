import { createSelector } from 'reselect'
import { filter, forEach, find, reduce, map, differenceWith, includes, mapValues } from 'lodash'

import { listingAdapter } from './adapters'

const listingSelectors = listingAdapter.getSelectors((state) => state.entities.listings)

export const selectAllListings = listingSelectors.selectAll