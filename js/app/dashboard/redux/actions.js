import {createAction} from 'redux-actions';

export const TOKEN_REFRESH_SUCCESS = 'TOKEN_REFRESH_SUCCESS'

export const SET_FILTER_VALUE = 'SET_FILTER_VALUE'
export const RESET_FILTERS = 'RESET_FILTERS'

export const TOGGLE_SEARCH = 'TOGGLE_SEARCH'
export const OPEN_SEARCH = 'OPEN_SEARCH'
export const CLOSE_SEARCH = 'CLOSE_SEARCH'

export const SET_POLYLINE_STYLE = 'SET_POLYLINE_STYLE'
export const SET_CLUSTERS_ENABLED = 'SET_CLUSTERS_ENABLED'
export const OPEN_SETTINGS = 'OPEN_SETTINGS'
export const CLOSE_SETTINGS = 'CLOSE_SETTINGS'
export const OPEN_FILTERS_MODAL = 'OPEN_FILTERS_MODAL'
export const CLOSE_FILTERS_MODAL = 'CLOSE_FILTERS_MODAL'

export const RIGHT_PANEL_MORE_THAN_HALF = 'LEFT_PANEL_MORE_THAN_HALF'
export const RIGHT_PANEL_LESS_THAN_HALF = 'LEFT_PANEL_LESS_THAN_HALF'

export const CREATE_LISTING_LIST_SUCCESS = 'CREATE_LISTING_LIST_SUCCESS'
export const UPDATE_LISTING = 'UPDATE_LISTING'
export const MODIFY_LISTING_LIST_REQUEST = 'MODIFY_LISTING_LIST_REQUEST'
export const MODIFY_LISTING_LIST_REQUEST_SUCCESS = 'MODIFY_LISTING_LIST_REQUEST_SUCCESS'
export const DELETE_GROUP_SUCCESS = 'DELETE_GROUP_SUCCESS'
export const REMOVE_LISTING = 'REMOVE_LISTING'
export const TOGGLE_LISTING = 'TOGGLE_LISTING'
export const SELECT_LISTING = 'SELECT_LISTING'
export const SELECT_LISTINGS = 'SELECT_LISTINGS'
export const SET_CURRENT_LISTING = 'SET_CURRENT_LISTING'
export const SELECT_LISTINGS_BY_IDS = 'SELECT_LISTINGS_BY_IDS'
export const CLEAR_SELECTED_LISTINGS = 'CLEAR_SELECTED_LISTINGS'
export const OPEN_NEW_LISTING_MODAL = 'OPEN_NEW_LISTING_MODAL'
export const CLOSE_NEW_LISTING_MODAL = 'CLOSE_NEW_LISTING_MODAL'

export const createTaskListSuccess = createAction(CREATE_LISTING_LIST_SUCCESS)


export function updateRightPanelSize(size) {
    return { type: size > 40 ? RIGHT_PANEL_MORE_THAN_HALF : RIGHT_PANEL_LESS_THAN_HALF }
}

export function updateLeftPanelSize(size) {
    return { type: size > 40 ? LEFT_PANEL_MORE_THAN_HALF : LEFT_PANEL_LESS_THAN_HALF }
}

export function setFilterValue(key, value) {
    return { type: SET_FILTER_VALUE, key, value }
}

export function resetFilters() {
    return { type: RESET_FILTERS }
}

export function openFiltersModal() {
    return { type: OPEN_FILTERS_MODAL }
}

export function closeFiltersModal() {
    return { type: CLOSE_FILTERS_MODAL }
}

export function toggleSearch() {
    return { type: TOGGLE_SEARCH }
}

export function openSearch() {
    return { type: OPEN_SEARCH }
}

export function closeSearch() {
    return { type: CLOSE_SEARCH }
}

export function openSettings() {
    return { type: OPEN_SETTINGS }
}

export function closeSettings() {
    return { type: CLOSE_SETTINGS }
}

export function setPolylineStyle(style) {
    return {type: SET_POLYLINE_STYLE, style}
}
export function setClustersEnabled(enabled) {
    return {type: SET_CLUSTERS_ENABLED, enabled}
}


export function setCurrentListing(listing) {
    return { type: SET_CURRENT_LISTING, listing }
}

export function toggleListing(listing, multiple = false) {
    return { type: TOGGLE_LISTING, listing, multiple }
}

export function selectListing(listing) {
    return { type: SELECT_LISTING, listing }
}

export function clearSelectedListings() {
    return { type: CLEAR_SELECTED_LISTINGS }
}

export function openNewListingModal() {
    return { type: OPEN_NEW_LISTING_MODAL }
}

export function closeNewListingModal() {
    return { type: CLOSE_NEW_LISTING_MODAL }
}

export function handleDragStart(result) {

    return function(dispatch, getState) {

        const selectedListings = getState().selectedListings

        // If the user is starting to drag something that is not selected then we need to clear the selection.
        // https://github.com/atlassian/react-beautiful-dnd/blob/master/docs/patterns/multi-drag.md#dragging
        const isDraggableSelected = selectedListings.includes(result.draggableId)

        if (!isDraggableSelected) {
            dispatch(clearSelectedListings())
        }
    }
}

export function handleDragEnd(result) {

    return function(dispatch, getState) {

        // dropped nowhere
        // if (!result.destination) {
        //     return;
        // }
        //
        // const source = result.source;
        // const destination = result.destination;
        //
        // // reodered inside the unassigned list, do nothing
        // if (
        //     source.droppableId === destination.droppableId &&
        //     source.droppableId === 'unassigned'
        // ) {
        //     return;
        // }
        //
        // // did not move anywhere - can bail early
        // if (
        //     source.droppableId === destination.droppableId &&
        //     source.index === destination.index
        // ) {
        //     return;
        // }
        //
        // // cannot unassign by drag'n'drop atm
        // if (source.droppableId.startsWith('assigned:') && destination.droppableId === 'unassigned') {
        //     return
        // }
        //
        // const allListings = selectAllListings(getState())
        // const listingLists = selectListingLists(getState())
        // const selectedListings = selectSelectedListings(getState())
        //
        // const username = destination.droppableId.replace('assigned:', '')
        // const listingList = _.find(listingLists, tl => tl.username === username)
        // const newListings = [ ...listingList.items ]
        //
        // if (selectedListings.length > 1) {
        //
        //     // FIXME Manage linked tasks
        //     // FIXME
        //     // The tasks are dropped in the order they were selected
        //     // Instead, we should respect the order of the unassigned tasks
        //
        //     Array.prototype.splice.apply(newListings,
        //         Array.prototype.concat([ result.destination.index, 0 ], selectedListings))
        //
        // } else if (result.draggableId.startsWith('group:')) {
        //
        //     const groupEl = document.querySelector(`[data-rbd-draggable-id="${result.draggableId}"]`)
        //
        //     const tasksFromGroup = Array
        //         .from(groupEl.querySelectorAll('[data-task-id]'))
        //         .map(el => _.find(allTasks, t => t['@id'] === el.getAttribute('data-task-id')))
        //
        //     Array.prototype.splice.apply(newTasks,
        //         Array.prototype.concat([ result.destination.index, 0 ], tasksFromGroup))
        //
        // } else {
        //
        //     // Reorder inside same list
        //     if (source.droppableId === destination.droppableId) {
        //         const [ removed ] = newTasks.splice(result.source.index, 1);
        //         newTasks.splice(result.destination.index, 0, removed)
        //     } else {
        //         const task = _.find(allTasks, t => t['@id'] === result.draggableId)
        //         if (task) {
        //             const linkedTasks = withLinkedTasks(task, allTasks)
        //             Array.prototype.splice.apply(newTasks,
        //                 Array.prototype.concat([ result.destination.index, 0 ], linkedTasks))
        //         }
        //     }
        //
        // }
        //
        // dispatch(modifyTaskList(username, newTasks))
    }
}