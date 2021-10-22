import {createStore, applyMiddleware, compose, combineReducers} from 'redux'
import thunk from 'redux-thunk'
import reduceReducers from 'reduce-reducers';

import listingEntityReducers from './listingEntityReducers'
import listingListEntityReducers from './listingListEntityReducers'
import * as webReducers from './reducers'

import dateReducer from './dateReducer'
import configReducers from './configReducers'
import settingsReducers from './settingsReducers'

const middlewares = [ thunk ]

// we maye want enhancing redux dev tools only  in dev ?
// also if server side render is made later, it is
// better to add a guard here
const composeEnhancers = (typeof window !== 'undefined' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) || compose

const reducer = combineReducers({
    ...webReducers,
    logistics: combineReducers({
        date: dateReducer,
        entities: combineReducers({
            listings: reduceReducers(listingEntityReducers),
            listingLists: reduceReducers(listingListEntityReducers),
        })
    }),
    config: configReducers,
    settings: settingsReducers,
})

export const createStoreFromPreloadedState = preloadedState => {
    return createStore(
        reducer,
        preloadedState,
        composeEnhancers(
            applyMiddleware(...middlewares)
        )
    )
}
