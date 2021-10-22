import _, { mapValues } from 'lodash'

export function listingsToIds(listings) {
    return listings.map((item) =>  item['@id'])
}
