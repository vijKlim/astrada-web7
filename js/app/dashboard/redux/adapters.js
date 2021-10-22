import { createEntityAdapter } from '@reduxjs/toolkit'

export const listingAdapter = createEntityAdapter({
  selectId: (o) => o['@id'],
  // sortComparer: (a, b) => a.title.localeCompare(b.title),
})

export const listingListAdapter = createEntityAdapter({
  selectId: (o) => o.business['@id'],
  sortComparer: (a, b) => a.business['@id'].localeCompare(b.business['@id']),
})
