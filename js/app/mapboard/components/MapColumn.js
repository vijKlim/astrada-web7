import React, { Component, createRef } from 'react'
import { Link, withRouter } from 'react-router-dom'
import { connect } from 'react-redux'
import LeafletMap from './LeafletMap'

class MapColumn extends Component {
    constructor (props) {
        super(props)



        this.state = {

        }


    }



    render () {
        const mapRef = createRef()

        return (
            <div className="map-container column-map right-pos-map no-fix-scroll-map hid-mob-map">
                <LeafletMap onLoad={ (e) => {
                    // It seems like a bad way to get a ref to the map,
                    // but we can't use the ref prop
                    mapRef.current = e.target
                }} />

                <ul className="mapnavigation no-list-style">
                    <li><a href="#" className="prevmap-nav mapnavbtn"><span><i className="fas fa-caret-left"></i></span></a>
                    </li>
                    <li><a href="#" className="nextmap-nav mapnavbtn"><span><i
                        className="fas fa-caret-right"></i></span></a></li>
                </ul>
                <div className="scrollContorl mapnavbtn tolt" data-microtip-position="top-left"
                     data-tooltip="Enable Scrolling"><span><i className="fal fa-unlock"></i></span></div>
                <div className="location-btn geoLocation tolt" data-microtip-position="top-left"
                     data-tooltip="Your location"><span><i className="fal fa-location"></i></span></div>
                <div className="map-overlay"></div>
                <div className="map-close"><i className="fas fa-times"></i></div>

            </div>

        )
    }
}

export default MapColumn
