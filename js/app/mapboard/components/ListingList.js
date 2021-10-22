import React, { Component } from 'react'
import { connect } from 'react-redux'
import _ from 'lodash'


import {selectAllListings} from "../redux/selectors";
import Pagination from "./Pagination";
import ListingEntry from "./ListingEntry";



// OPTIMIZATION
// Avoid useless re-rendering when starting to drag
// @see https://egghead.io/lessons/react-optimize-performance-in-react-beautiful-dnd-with-shouldcomponentupdate-and-purecomponent
class InnerList extends React.Component {

    shouldComponentUpdate(nextProps) {
        if (nextProps.listings === this.props.listings) {
            return false
        }

        return true
    }

    render() {
        return _.map(this.props.listings, (listing, index) => {
            return (
                <ListingEntry key={index}
                    listing={ listing } />
            )
        })
    }
}

class ListingList extends Component {
    constructor (props) {
        super(props)
        this.state = {
        }

    }


    render () {

        const { listings, pagination } = this.props

        return (
            <div className="listing-item-container init-grid-items fl-wrap" id="lisfw">
                <div className="container">

                    <InnerList
                        listings={ listings } />
                    <Pagination pagination={pagination}/>
                </div>
            </div>
        )
    }
}

const mapStateToProps = (state) => {
    return {
        listings: selectAllListings(state),
        pagination: state.settings.listingsPagination,
    }
}

const mapDispatchToProps = (dispatch) => {
    return {

    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ListingList)
