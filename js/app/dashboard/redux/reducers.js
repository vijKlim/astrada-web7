import _ from 'lodash'

import {
  TOGGLE_LISTING,
  SELECT_LISTING,
  SELECT_LISTINGS,
  SELECT_LISTINGS_BY_IDS,
  TOKEN_REFRESH_SUCCESS,
  OPEN_FILTERS_MODAL,
  CLOSE_FILTERS_MODAL,
  TOGGLE_SEARCH,
  OPEN_SEARCH,
  CLOSE_SEARCH,
  OPEN_SETTINGS,
  CLOSE_SETTINGS,
  CLEAR_SELECTED_LISTINGS,
  RIGHT_PANEL_MORE_THAN_HALF,
  RIGHT_PANEL_LESS_THAN_HALF,
} from './actions'


const initialState = {
  addModalIsOpen: false,
  polylineEnabled: {},
  selectedListings: [],
  jwt: '',
  taskModalIsOpen: false,
  isTaskModalLoading: false,
  completeTaskErrorMessage: null,
  filtersModalIsOpen: false,
  settingsModalIsOpen: false,
  searchIsOn: false,
  isLoadingTaskEvents: false,
  taskEvents: {},
  imports: {},
  importModalIsOpen: false,
  rightPanelSplitDirection: 'vertical',
  recurrenceRuleModalIsOpen: false,
  currentRecurrenceRule: null,
  recurrenceRulesLoading: false,
  recurrenceRulesErrorMessage: '',
  exportModalIsOpen: false,
}

export const selectedListings = (state = [], action) => {
  switch (action.type) {
    case TOGGLE_LISTING:

      if (-1 !== state.indexOf(action.listing['@id'])) {
        if (!action.multiple) {
          return []
        }
        return _.filter(state, listing => listing !== action.listing['@id'])
      }

      const newState = action.multiple ? state.slice(0) : []
      newState.push(action.listing['@id'])

      return newState

    case SELECT_LISTING:

      if (-1 !== state.indexOf(action.listing['@id'])) {

        return state
      }

      return [ action.listing['@id'] ]

    case SELECT_LISTINGS:

      return action.listings.map(listing => listing['@id'])

    case SELECT_LISTINGS_BY_IDS:

      return action.listingIds

    case CLEAR_SELECTED_LISTINGS:

      // OPTIMIZATION
      // Make sure the array if not already empty
      // before returning a new reference
      if (state.length > 0) {
        return []
      }
      break
  }

  return state
}

export const jwt = (state = '', action) => {
  switch (action.type) {
    case TOKEN_REFRESH_SUCCESS:

      return action.token

    default:

      return state
  }
}

export const filtersModalIsOpen = (state = initialState.filtersModalIsOpen, action) => {
  switch (action.type) {
    case OPEN_FILTERS_MODAL:
      return true
    case CLOSE_FILTERS_MODAL:
      return false
    default:
      return state
  }
}

export const searchIsOn = (state = initialState.searchIsOn, action) => {
  switch (action.type) {
    case TOGGLE_SEARCH:

      return !state
    case OPEN_SEARCH:

      return true
    case CLOSE_SEARCH:

      return false
    default:
      return state
  }
}

export const settingsModalIsOpen = (state = initialState.settingsModalIsOpen, action) => {
  switch (action.type) {
    case OPEN_SETTINGS:

      return true
    case CLOSE_SETTINGS:

      return false
    default:
      return state
  }
}

export const rightPanelSplitDirection = (state = initialState.rightPanelSplitDirection, action) => {
  switch (action.type) {
    case RIGHT_PANEL_MORE_THAN_HALF:

      return 'horizontal'
    case RIGHT_PANEL_LESS_THAN_HALF:

      return 'vertical'
  }

  return state
}