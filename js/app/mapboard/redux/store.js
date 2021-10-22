import {createStore, applyMiddleware, compose, combineReducers} from 'redux'
import thunk from 'redux-thunk'
import reduceReducers from 'reduce-reducers';

import listingEntityReducers from './listingEntityReducers'
import settingsReducers from './settingsReducers'
import searchFormReducers from "./searchFormReducers";



const middlewares = [ thunk ]

// we maye want enhancing redux dev tools only  in dev ?
// also if server side render is made later, it is
// better to add a guard here
const composeEnhancers = (typeof window !== 'undefined' &&
    window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__) || compose

const reducer = combineReducers({
    entities: combineReducers({
        listings: reduceReducers(listingEntityReducers),
    }),
    settings: settingsReducers,
    searchForm: searchFormReducers
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