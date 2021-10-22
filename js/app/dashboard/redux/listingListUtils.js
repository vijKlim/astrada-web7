import _ from 'lodash';
import moment from 'moment';
import { listingsToIds } from './listingUtils'

export function replaceListingsWithIds(listingList) {
    let entity = {
        ...listingList,
    }

    entity.itemIds = listingsToIds(listingList.items)
    delete entity.items

    return entity
}
