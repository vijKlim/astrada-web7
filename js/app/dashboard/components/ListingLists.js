import React from 'react'
import { connect } from 'react-redux'
import _ from 'lodash'
import { withTranslation } from 'react-i18next'
import { Accordion } from 'react-accessible-accordion'

// import { openAddUserModal } from '../redux/actions'
import ListingList from './ListingList'

import { selectListingLists } from '../redux/selectors'

class ListingLists extends React.Component {

    render() {

        const { listingLists, listingListsLoading } = this.props

        return (
            <div className="dashboard__panel dashboard__panel--assignees">
                <h4>
                    <span>{ this.props.t('DASHBOARD_ASSIGNED') }</span>

                </h4>
                <Accordion
                    allowZeroExpanded
                    id="accordion"
                    className="dashboard__panel__scroll"
                    style={{ opacity: listingListsLoading ? 0.7 : 1, pointerEvents: listingListsLoading ? 'none' : 'initial' }}>
                    {
                        _.map(listingLists, (listingList) => {

                            // if (this.props.hiddenCouriers.includes(listingList.username)) {
                            //     return null
                            // }

                            return (
                                <ListingList
                                    key={ listingList['@id'] }
                                    businessName={ listingList.business.name }
                                    businessId={ listingList.business['@id'] }
                                    image={ listingList.image }
                                    uri={ listingList['@id'] } />
                            )
                        })
                    }
                </Accordion>
            </div>
        )
    }
}

function mapStateToProps (state) {

    return {
        listingLists: selectListingLists(state),
        listingListsLoading: false,
        hiddenCouriers: state.settings.filters.hiddenCouriers,
    }
}

function mapDispatchToProps (dispatch) {

    return {
        // openAddUserModal: () => dispatch(openAddUserModal()),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withTranslation()(ListingLists))
