import React from 'react'
import { connect } from 'react-redux'
import { withTranslation, useTranslation } from 'react-i18next'
import moment from 'moment'
import { useContextMenu } from 'react-contexify'
import _ from 'lodash'
import { addressAsText } from '../utils'

import { setCurrentListing, toggleListing, selectListing } from '../redux/actions'
import { selectVisibleListingIds } from '../redux/selectors'
import { selectSelectedDate } from '../redux'

moment.locale($('html').attr('lang'))

const ListingCaption = ({ listing }) => {
    const { t } = useTranslation()

    return (
        <span>
          <span className="mr-1">
            <span className="text-monospace">#{ listing.id }</span>
          </span>
            { (listing.title && !_.isEmpty(listing.title)) && (
              <span>
                <span className="font-weight-bold">{ listing.title }</span>
                <span className="mx-1">›</span>
              </span>
            ) }
            { t('ADMIN_DASHBOARD_TASK_CAPTION', {
                address: addressAsText(listing.address),
                date: moment(listing.before).format('LT')
            }) }
        </span>
    )
}

const ListingAttrs = ({ listing }) => {
    if(listing.images && listing.images.length > 0) {

        return (
            <span className="task__attrs">
                <i className="fa fa-camera"></i>
            </span>
        )
    }

    return null
}

const { show } = useContextMenu({
    id: 'dashboard',
})

class Listing extends React.Component {

    constructor(props) {
        super(props);

        this.onClick = this.onClick.bind(this)
        this.onDoubleClick = this.onDoubleClick.bind(this)
        this.prevent = false
    }

    // @see https://css-tricks.com/snippets/javascript/bind-different-events-to-click-and-double-click/

    onClick(e) {
        const multiple = (e.ctrlKey || e.metaKey)
        this.timer = setTimeout(() => {
            if(!this.prevent) {
                const { toggleListing, listing } = this.props
                toggleListing(listing, multiple)
            }
            this.prevent = false
        }, 250)
    }

    onDoubleClick() {
        clearTimeout(this.timer)
        this.prevent = true

        const { listing } = this.props
        this.props.setCurrentListing(listing)
    }

    render() {

        const { color, listing, selected, isVisible, date, assigned } = this.props

        const classNames = [
            'list-group-item',
        ]

        let listingAttributes = {}
        if (listing.previous) {
            listingAttributes = Object.assign(listingAttributes, { 'data-previous': listing.previous })
        }
        if (listing.next) {
            listingAttributes = Object.assign(listingAttributes, { 'data-next': listing.next })
        }

        if (selected) {
            classNames.push('listing__highlighted')
        }

        const listingProps = {
            ...listingAttributes,
            style: {
                display: isVisible ? 'block' : 'none',
            },
            key: listing['@id'],
            className: classNames.join(' '),
            'data-listing-id': listing['@id'],
            onDoubleClick: this.onDoubleClick,
            onClick: this.onClick,
            onContextMenu: (e) => {
                e.preventDefault()

                this.props.selectListing(listing)
                show(e, {
                    props: { listing }
                })
            }
        }

        return (
            <span { ...listingProps }>
        <span className="list-group-item-color" style={{ backgroundColor: color }}></span>
        <span>
          <i className={ 'listing__icon listing__icon--type fa fa-' + (listing.type === 'PICKUP' ? 'cube' : 'arrow-down') }></i>
          <ListingCaption listing={ listing } />
          <ListingAttrs listing={ listing } />
        </span>
      </span>
        )
    }
}

function mapStateToProps(state, ownProps) {

    // const tasksWithColor = selectTasksWithColor(state)
    //
    // const color = Object.prototype.hasOwnProperty.call(tasksWithColor, ownProps.task['@id']) ?
    //     tasksWithColor[ownProps.task['@id']] : '#ffffff'


    const visibleListingIds = selectVisibleListingIds(state)

    return {
        selected: -1 !== state.selectedListings.indexOf(ownProps.listing['@id']),
        // color,
        date: selectSelectedDate(state),
        isVisible: _.includes(visibleListingIds, ownProps.listing['@id']),
    }
}

function mapDispatchToProps (dispatch) {
    return {
        setCurrentListing: (listing) => dispatch(setCurrentListing(listing)),
        toggleListing: (listing, multiple) => dispatch(toggleListing(listing, multiple)),
        selectListing: (listing) => dispatch(selectListing(listing)),
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withTranslation()(Listing))
