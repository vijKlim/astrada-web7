import { createEntityAdapter } from '@reduxjs/toolkit'

export const listingAdapter = createEntityAdapter({
    selectId: (o) => o['@id'],
    // sortComparer: (a, b) => a.title.localeCompare(b.title),
})
