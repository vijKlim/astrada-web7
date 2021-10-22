import React, { Component } from 'react'
import { Link, withRouter } from 'react-router-dom'
import { connect } from 'react-redux'
import HiddenSearchColumn from "./HiddenSearchColumn";



class ListingHeader extends Component {
    constructor (props) {
        super(props)



        this.state = {

        }


    }



    render () {

        return (
            <div>
                <div className="list-main-wrap-header fl-wrap anim_clw  ">
                    <div className="container">

                        <div className="list-main-wrap-title">
                            <h2><span>SET_sonata_seo_title </span></h2>
                        </div>

                        <div className="list-main-wrap-opt">



                            <div className="grid-opt">
                                <ul className="no-list-style">
                                    <li className="grid-opt_act"><span className="two-col-grid act-grid-opt tolt"
                                                                       data-microtip-position="bottom"
                                                                       data-tooltip="Grid View"><i
                                        className="fal fa-th"></i></span></li>
                                    <li className="grid-opt_act"><span className="one-col-grid tolt"
                                                                       data-microtip-position="bottom"
                                                                       data-tooltip="List View"><i
                                        className="fal fa-list"></i></span></li>
                                </ul>
                            </div>

                            <div className="show-hidden-sb shsb_btn shsb_btn_act"><i className="fal fa-sliders-h"></i>
                                <span>Show Filters</span></div>
                        </div>

                    </div>
                    <a className="custom-scroll-link back-to-filters clbtg" href="#lisfw"><i
                        className="fas fa-caret-up"></i></a>
                </div>

                <div className="clearfix"></div>
                <div className="container">
                    <div className="mob-nav-content-btn mncb_half color2-bg shsb_btn shsb_btn_act fl-wrap"><i
                        className="fal fa-filter"></i> Filters
                    </div>
                    <div className="mob-nav-content-btn mncb_half color2-bg schm  fl-wrap"><i
                        className="fal fa-map-marker-alt"></i> View on map
                    </div>
                </div>
                <div className="clearfix"></div>
            </div>

        )
    }
}


export default ListingHeader


