import React, { useState } from 'react'
import _ from 'lodash'
import { connect } from 'react-redux'
import { withTranslation } from 'react-i18next'
import { Draggable, Droppable } from "react-beautiful-dnd"
import { Popover } from 'antd'
import { useTranslation } from 'react-i18next'

import Listing from './Listing'

import { openNewListingModal, toggleSearch } from '../redux/actions'
import {
    selectSelectedListings,
    selectAllListings
} from '../redux/selectors'

class StandaloneListings extends React.Component {

    shouldComponentUpdate(nextProps) {
        if (nextProps.listings === this.props.listings
            && nextProps.offset === this.props.offset) {
            return false
        }

        return true
    }

    render() {
        return _.map(this.props.listings, (listing, index) => {

            return (
                <Draggable key={ listing['@id'] } draggableId={ listing['@id'] } index={ (this.props.offset + index) }>
                    {(provided, snapshot) => {

                        return (
                            <div
                                ref={ provided.innerRef }
                                { ...provided.draggableProps }
                                { ...provided.dragHandleProps }
                            >
                                <Listing listing={ listing } />
                                { (snapshot.isDragging && this.props.selectedListingsLength > 1) && (
                                    <div className="task-dragging-number">
                                        <span>{ this.props.selectedListingsLength }</span>
                                    </div>
                                ) }
                            </div>
                        )
                    }}
                </Draggable>
            )
        })
    }
}

const StandaloneListingsWithConnect = connect(
    (state) => ({
        selectedListingsLength: selectSelectedListings(state).length,
    })
)(StandaloneListings)

const Buttons = connect(
    (state) => ({
    }),
    (dispatch) => ({
        openNewListingModal: () => dispatch(openNewListingModal()),
        toggleSearch: () => dispatch(toggleSearch())
    })
)(({  openNewListingModal, toggleSearch }) => {

    const [ visible, setVisible ] = useState(false)
    const { t } = useTranslation()

    return (
        <React.Fragment>

            <a href="#" className="mr-3" onClick={ e => {
                e.preventDefault()
                openNewListingModal()
            }}>
                <i className="fa fa-plus"></i>
            </a>
            <a href="#" className="mr-3" onClick={ e => {
                e.preventDefault()
                toggleSearch()
            }}>
                <i className="fa fa-search"></i>
            </a>

        </React.Fragment>
    )
})

class OwnListings extends React.Component {

    render() {

        return (
            <div className="dashboard__panel">
                <h4 className="d-flex justify-content-between">
                    <span>{ this.props.t('DASHBOARD_UNASSIGNED') }</span>
                    <span>
            <Buttons />
          </span>
                </h4>
                <div className="dashboard__panel__scroll">
                    <Droppable droppableId="unassigned">
                        {(provided) => (
                            <div className="list-group nomargin" ref={ provided.innerRef } { ...provided.droppableProps }>

                                <StandaloneListingsWithConnect
                                    listings={ this.props.standaloneListings }
                                    offset={ this.props.offset } />
                                { provided.placeholder }
                            </div>
                        )}
                    </Droppable>
                </div>
            </div>
        )
    }
}

function mapStateToProps (state) {

    return {
        // groups: selectGroups(state),
        offset: 0,
        standaloneListings: selectAllListings(state),
    }
}



export default connect(mapStateToProps)(withTranslation()(OwnListings))
