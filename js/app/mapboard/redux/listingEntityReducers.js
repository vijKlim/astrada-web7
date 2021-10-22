import { listingAdapter } from './adapters'

const initialState = listingAdapter.getInitialState()
const selectors = listingAdapter.getSelectors((state) => state)

export default (state = initialState, action) => {

    return state
}
