import React from 'react'
import { connect } from 'react-redux'
import moment from 'moment'
import { Draggable, Droppable } from "react-beautiful-dnd"
import { withTranslation } from 'react-i18next'
import _ from 'lodash'
import { Progress, Tooltip } from 'antd'
import Popconfirm from 'antd/lib/popconfirm'
import {
    AccordionItem,
    AccordionItemHeading,
    AccordionItemButton,
    AccordionItemPanel,
} from 'react-accessible-accordion'
import classNames from 'classnames'

import Listing from './Listing'
// import { unassignTasks, togglePolyline, optimizeTaskList } from '../redux/actions'
import { selectVisibleListingIds } from '../redux/selectors'
import { makeSelectListingListItemsByBusiness } from '../redux/selectors'

moment.locale($('html').attr('lang'))

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
                <Draggable key={ listing['@id'] } draggableId={ listing['@id'] } index={ index }>
                    {(provided) => (
                        <div
                            ref={ provided.innerRef }
                            { ...provided.draggableProps }
                            { ...provided.dragHandleProps }
                        >
                            <Listing
                                listing={ listing }
                                assigned={ true }
                                onRemove={ listing => this.props.onRemove(listing) } />
                        </div>
                    )}
                </Draggable>
            )
        })
    }
}



class ListingList extends React.Component {


    render() {
        const {
            businessId,
            businessName,
            image,
            isEmpty,
        } = this.props

        const { listings } = this.props


        return (
            <AccordionItem>
                <AccordionItemHeading>
                    <AccordionItemButton>
                        <span>
                          <img src={ image } width="24" height="24" />
                          <small className="text-monospace ml-2">
                            <strong className="mr-2">{ businessName }</strong>
                            <span className="text-muted">{ `(${listings.length})` }</span>
                          </small>
                        </span>
                    </AccordionItemButton>
                </AccordionItemHeading>
                <AccordionItemPanel>

                    <Droppable droppableId={ `assigned:${businessId}` }>
                        {(provided) => (
                            <div ref={ provided.innerRef }
                                 className={ classNames({
                                     'taskList__tasks': true,
                                     'list-group': true,
                                     'm-0': true,
                                     'taskList__tasks--empty': isEmpty
                                 }) }
                                 { ...provided.droppableProps }
                            >
                                <InnerList
                                    listings={ listings } />
                                { provided.placeholder }
                            </div>
                        )}
                    </Droppable>
                </AccordionItemPanel>
            </AccordionItem>
        )
    }
}

const makeMapStateToProps = () => {

    const selectListingListItemsByBusiness = makeSelectListingListItemsByBusiness()

    const mapStateToProps = (state, ownProps) => {

        const items = selectListingListItemsByBusiness(state, ownProps)

        const visibleListingIds = _.intersectionWith(
            selectVisibleListingIds(state),
            items.map(listing => listing['@id'])
        )

        return {
            // polylineEnabled: state.polylineEnabled[ownProps.username],
            listings: items,
            isEmpty: items.length === 0 || visibleListingIds.length === 0,
            filters: state.settings.filters,
        }
    }

    return mapStateToProps
}

function mapDispatchToProps(dispatch) {
    return {
        // unassignTasks: (username, tasks) => dispatch(unassignTasks(username, tasks)),
        // togglePolyline: (username) => dispatch(togglePolyline(username)),
        // optimizeTaskList: (taskList) => dispatch(optimizeTaskList(taskList)),
    }
}

export default connect(makeMapStateToProps, mapDispatchToProps)(withTranslation()(ListingList))
