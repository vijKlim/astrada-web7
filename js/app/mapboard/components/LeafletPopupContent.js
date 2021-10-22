import React, { Component } from 'react'
import moment from 'moment'
import _ from 'lodash'

import i18n from '../../i18n'

import Avatar from '../../components/Avatar'

export default class extends Component {

  constructor (props) {
    super(props)
    this.state = {
        listing: this.props.listing
    }
  }

  updateListing(listing) {
    this.setState({ listing })
  }

  render() {

    const { listing } = this.state

    return (
        <div className="map-popup-wrap">
            <div className="map-popup">

                <a href="#" className="listing-img-content fl-wrap">
                    <div className="infobox-status open">open</div>
                    <img src={listing.image} alt="" />
                        <div className="card-popup-raining map-card-rainting"
                             data-starrating="5"><span className="map-popup-reviews-count">( 12 reviews )</span>
                        </div></a>
                <div className="listing-content">
                    <div className="listing-content-item fl-wrap">
                        <div className="map-popup-location-category"></div>
                        <div className="listing-title fl-wrap"><h4><a href="">{listing.title}</a>
                        </h4>
                            <div className="map-popup-location-info"><i className="fas fa-map-marker-alt"></i>
                                locationAddress
                            </div>
                        </div>
                        <div className="map-popup-footer"><a href="#" className="main-link">Details <i
                            className="fal fa-long-arrow-right"></i></a><a href="#" className="infowindow_wishlist-btn"><i
                            className="fal fa-heart"></i></a></div>
                    </div>
                </div>
            </div>
        </div>
    )
  }
}
