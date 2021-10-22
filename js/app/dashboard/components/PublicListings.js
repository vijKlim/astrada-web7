import React, { useState } from 'react'
import _ from 'lodash'
import { connect } from 'react-redux'
import { withTranslation } from 'react-i18next'
import { Draggable, Droppable } from "react-beautiful-dnd"
import { Popover } from 'antd'
import { useTranslation } from 'react-i18next'

import Listing from './Listing'

// import { setCurrentListing, toggleListing, selectListing } from '../redux/actions'
import {selectAllListings} from '../redux/selectors'

class StandaloneListings extends React.Component {

    shouldComponentUpdate(nextProps, nextState, nextContext) {
        if(nextProps.listings === this.props.listings
         && nextProps.offset === this.props.offset) {
            return false
        }

        return true
    }

    render() {
        return _.map(this.props.listings, (listing, index) => {
            return (
                <div key={index}>
                    <Listing listing={ listing } />
                </div>

            )
        })
    }
}


class PublicListings extends React.Component {

    render() {
        return (
            <div className="listing-item-container init-grid-items fl-wrap" id="lisfw">
                <div className="container">

                    <StandaloneListings
                        listings={ this.props.standaloneListings } />



                </div>
            </div>

        )
    }
}

function mapStateToProps (state) {

    return {
        standaloneListings: selectAllListings(state),
    }
}



export default connect(mapStateToProps)(withTranslation()(PublicListings))