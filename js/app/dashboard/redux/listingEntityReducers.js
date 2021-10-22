import {
    CREATE_LISTING_LIST_SUCCESS
} from './actions';
import { listingAdapter } from './adapters'

const initialState = listingAdapter.getInitialState()
const selectors = listingAdapter.getSelectors((state) => state)

export default (state = initialState, action) => {
    switch (action.type) {
        case CREATE_LISTING_LIST_SUCCESS:
            return listingAdapter.upsertMany(state, action.payload.items)

        // case MODIFY_LISTING_LIST_REQUEST:
        //     return listingAdapter.upsertMany(state, action.tasks)
        //
        // case MODIFY_LISTING_LIST_REQUEST_SUCCESS:
        //     const entities = action.taskList.items.map(item => ({
        //         '@id': item.task,
        //         position: item.position
        //     }))
        //     return listingAdapter.upsertMany(state, entities)
        //
        // case UPDATE_LISTING:
        //     return listingAdapter.upsertOne(state, action.task)
        //
        // case DELETE_GROUP_SUCCESS:
        //     const lsitingMatchingGroup = _.filter(
        //         selectors.selectAll(state),
        //         t => t.group && t.group['@id'] === action.group
        //     )
        //
        //     if (lsitingMatchingGroup.length === 0) {
        //         return state
        //     }
        //
        //     return listingAdapter.removeMany(state, tasksMatchingGroup.map(t => t['@id']))
        //
        // case REMOVE_LISTING:
        //     return listingAdapter.removeOne(state, action.task['@id'])
    }

    return state
}
