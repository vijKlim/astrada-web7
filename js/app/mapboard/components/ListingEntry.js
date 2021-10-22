import React from 'react'
import { connect } from 'react-redux'
import { withTranslation, useTranslation } from 'react-i18next'
import moment from 'moment'
import { useContextMenu } from 'react-contexify'
import _ from 'lodash'
import { addressAsText } from '../../dashboard/utils'



moment.locale($('html').attr('lang'))

const ListingRatting = ({ listing }) => {
    if (listing.averageRating){
        return (
            <div className="listing-rating-count-wrap">
                <div className="review-score">{listing.averageRating}</div>
                <div className="listing-rating card-popup-rainingvis"
                     data-starrating2="{ listing.averageRating }"></div>
                <br/>
                <div className="reviews-count">{listing.commentCount} reviews</div>
            </div>

        )
    }
    return <div className="listing-rating-count-wrap"></div>
}

const ListingImageBlock = ({ listing }) => {

    const favoriteId = "favourite-"+listing.id;
    return (
        <div className="geodir-category-img">
            <div className="geodir-js-favorite_btn" id={ favoriteId }>
                <i className="fal fa-heart"></i><span>Save</span>
            </div>
            <a href={listing.href}
               className="geodir-category-img-wrap fl-wrap">
                <img src={ listing.image }
                     alt={ listing.title }></img>
            </a>
            <div className="listing-avatar">
                <a href="author-single.html">
                    <img src={ listing.image } alt={ listing.business.name }></img>
                </a>
                <span className="avatar-tooltip">Added By  <strong>{listing.business.name}</strong></span>
            </div>
            <div className="geodir_status_date gsd_open"><i className="fal fa-lock-open"></i>Open Now</div>
            <div className="geodir-category-opt">
                <ListingRatting listing={ listing }/>

            </div>
        </div>
    )
}

const ListingContentBlock = ({ listing }) => {

    const coordinates_str = listing.address.geo.latitude+'-'+listing.address.geo.longitude;
    return (
        <div className="geodir-category-content fl-wrap">
            <div className="geodir-category-content-title fl-wrap">
                <div className="geodir-category-content-title-item">
                    <h3 className="title-sin_map">
                        <a href={listing.href}>
                            {listing.title}
                        </a>
                        <span className="verified-badge"><i className="fal fa-check"></i></span></h3>
                    <div className="geodir-category-location fl-wrap">
                        <a href="#1" className="map-item" data-cpt-marker={coordinates_str}><i
                            className="fas fa-map-marker-alt"></i> {listing.address.streetAddress}</a></div>
                </div>
            </div>
            <div className="geodir-category-text fl-wrap">
                <p className="small-text">{listing.description}</p>
                <div className="facilities-list fl-wrap">
                    <div className="facilities-list-title">Facilities :</div>
                    <ul className="no-list-style">
                        <li className="tolt" data-microtip-position="top" data-tooltip="Free WiFi"><i
                            className="fal fa-wifi"></i></li>
                        <li className="tolt" data-microtip-position="top" data-tooltip="Parking"><i
                            className="fal fa-parking"></i></li>
                        <li className="tolt" data-microtip-position="top" data-tooltip="Non-smoking Rooms"><i
                            className="fal fa-smoking-ban"></i></li>
                        <li className="tolt" data-microtip-position="top" data-tooltip="Pets Friendly"><i
                            className="fal fa-dog-leashed"></i></li>
                    </ul>
                </div>
            </div>
            <div className="geodir-category-footer fl-wrap">
                <a className="listing-item-category-wrap">
                    <div className="listing-item-category red-bg"><i className="fal fa-cheeseburger"></i></div>
                    <span>Here listing categories</span>

                </a>
                <div className="geodir-opt-list">
                    <ul className="no-list-style">
                        <li><a href="#" className="show_gcc"><i className="fal fa-envelope"></i><span
                            className="geodir-opt-tooltip">Contact Info</span></a></li>
                        <li><a href="#1" className="map-item" data-cpt-marker={coordinates_str}><i
                            className="fal fa-map-marker-alt"></i><span
                            className="geodir-opt-tooltip">On the map <strong>1</strong></span> </a></li>
                        <li>
                            <div className="dynamic-gal gdop-list-link">
                                <i className="fal fa-search-plus"></i><span
                                className="geodir-opt-tooltip">Gallery</span></div>
                        </li>
                    </ul>
                </div>
                <div className="price-level geodir-category_price">
                        <span className="price-level-item">

                            <strong>100-listing_price </strong>
                        </span>
                    <span className="price-name-tooltip">Pricey</span>
                </div>
                <div className="geodir-category_contacts">
                    <div className="close_gcc"><i className="fal fa-times-circle"></i></div>
                    <ul className="no-list-style">
                        <li><span><i className="fal fa-phone"></i> Call : </span><a
                            href="#">{listing.business.telephone}</a></li>

                        <li><span><i className="fal fa-envelope"></i> Write : </span><a href="#">business.email </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    )
}


class ListingEntry extends React.Component {

    constructor(props) {
        super(props);

        // this.onClick = this.onClick.bind(this)
        // this.onDoubleClick = this.onDoubleClick.bind(this)
        this.prevent = false
    }

    // @see https://css-tricks.com/snippets/javascript/bind-different-events-to-click-and-double-click/

    // onClick(e) {
    //     const multiple = (e.ctrlKey || e.metaKey)
    //     this.timer = setTimeout(() => {
    //         if(!this.prevent) {
    //             const { toggleListing, listing } = this.props
    //             toggleListing(listing, multiple)
    //         }
    //         this.prevent = false
    //     }, 250)
    // }
    //
    // onDoubleClick() {
    //     clearTimeout(this.timer)
    //     this.prevent = true
    //
    //     const { listing } = this.props
    //     this.props.setCurrentListing(listing)
    // }

    render() {

        const { listing } = this.props
        return (
            <div className="listing-item has_one_column">
                <article className="geodir-category-listing fl-wrap" data-lid="{{ listing.id }}">
                    <ListingImageBlock listing={ listing }/>
                    <ListingContentBlock listing={ listing }/>
                </article>
            </div>
        )
    }
}

function mapStateToProps(state, ownProps) {


    return {
    }
}

function mapDispatchToProps (dispatch) {
    return {
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(withTranslation()(ListingEntry))
