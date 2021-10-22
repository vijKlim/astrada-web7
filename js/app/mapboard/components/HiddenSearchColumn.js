import React, { Component } from 'react'
import { Link, withRouter } from 'react-router-dom'
import { connect } from 'react-redux'
import { Form, Field } from "react-final-form";
import Select from 'react-select'

import AddressAutosuggest from "../../site/components/AddressAutosuggest";
import {selectAllListings} from "../redux/selectors";
import store from "../../search/address-storage";

const ReactSelectAdapter = ({ input, ...rest }) => {
    console.log(input);
    return <Select {...input} {...rest} />
}



class HiddenSearchColumn extends Component {
    constructor (props) {
        super(props)



        this.state = {
        }


    }

    _onSubmit(values) {
        var url = new URL(window.location.href)
        if(values.sort_by.hasOwnProperty('value')){
            url.searchParams.set('sort_by',values.sort_by.value);
            window.location.href = url.toString()
        }

    }

    render () {
        const { searchForm } = this.props
        let formData = searchForm.value



        const sortByData = searchForm.schema.properties.sort_by.enum.map((value, index)  => ({
            value:value,
            label: searchForm.schema.properties.sort_by.enumTitles[index]
        }));
        var sortKey = 0;
        var sortEnum = searchForm.schema.properties.sort_by.enum;
        sortEnum.forEach(function(item, i, arr) {
            if(item === formData.sort_by){
                sortKey = i;
            }
        })
        const sortByDefaultValue = { 'label':searchForm.schema.properties.sort_by.enumTitles[sortKey], 'value':formData.sort_by}
        formData.sort_by = sortByDefaultValue

        return (
            <Form
                onSubmit={this._onSubmit}
                initialValues={{
                    ...formData,
                }}
                render={({ handleSubmit, form, submitting, pristine, values }) => (
                    <form onSubmit={handleSubmit} >
                        <div className="hidden-search-column">
                            <div className="hidden-search-column-container fl-wrap full-height tabs-act">
                                <div className="filter-sidebar-header fl-wrap">
                                    <ul className="tabs-menu fl-wrap no-list-style">
                                        <li className="current"><a href="#filters-search"> <i
                                            className="fal fa-sliders-h"></i> Filters </a></li>
                                        <li><a href="#category-search"> <i className="fal fa-image"></i>Categories </a></li>
                                    </ul>
                                </div>
                                <div className="scrl-content filter-sidebar  full-height">

                                    <div className="tabs-container fl-wrap">

                                        <div className="tab">
                                            <div id="filters-search" className="tab-content  first-tab ">
                                                <div className="listsearch-input-item">

                                                    <AddressAutosuggest
                                                        geohash={ formData.geohash }
                                                        onAddressSelected={ (value, address, type) => {

                                                            const addressInput = form.querySelector('input[name="address"]')
                                                            const geohashInput = form.querySelector('input[name="geohash"]')

                                                            if (address.geohash !== geohashInput.value) {

                                                                if (type === 'address') {
                                                                    if (!addressInput) {
                                                                        const newAddressInput = document.createElement('input')
                                                                        newAddressInput.setAttribute('type', 'hidden')
                                                                        newAddressInput.setAttribute('name', 'address')
                                                                        newAddressInput.value = btoa(address['@id'])
                                                                        form.appendChild(newAddressInput)
                                                                    }
                                                                }

                                                                if (type === 'prediction') {
                                                                    if (addressInput) {
                                                                        addressInput.parentNode.removeChild(addressInput)
                                                                    }
                                                                }

                                                                store.set('search_geohash', address.geohash)
                                                                store.set('search_address', address)

                                                                const trackingCategory = container.dataset.trackingCategory
                                                                if (trackingCategory) {
                                                                    window._paq.push(['trackEvent', trackingCategory, 'searchAddress', value])
                                                                }

                                                                geohashInput.value = address.geohash

                                                                form.submit()

                                                            }

                                                        }}
                                                        required={ false }
                                                        preciseOnly={ false }
                                                        reportValidity={ false } />


                                                </div>


                                                <div className="listsearch-input-item">

                                                    <Field
                                                        placeholder="Вибрати..."
                                                        name="sort_by"
                                                        component={ReactSelectAdapter}
                                                        defaultValue={sortByDefaultValue}
                                                        options={sortByData}
                                                    />


                                                </div>

                                                <div className="listsearch-input-item">
                                                    <button type="submit" value="Search"  className="header-search-button color-bg">
                                                        <i className="far fa-search"></i><span>Пошук</span>
                                                    </button>
                                                </div>


                                                <div className="clear-filter-btn" onClick={form.reset}><i className="far fa-redo"></i> Скинути фільтри
                                                </div>
                                            </div>
                                        </div>

                                        <div className="tab">
                                            <div id="category-search" className="tab-content">
                                                <div className="fl-wrap hc-item">
                                                    <a className="category-carousel-item fl-wrap full-height checket-cat" href="#">
                                                        <img src="/images/all/1.jpg" alt=""></img>
                                                        <div className="category-carousel-item-icon red-bg">
                                                            <i className="fal fa-cheeseburger"></i></div>
                                                        <div className="category-carousel-item-container">
                                                            <div className="category-carousel-item-title">Restaurants / Cafe</div>
                                                            <div className="category-carousel-item-counter">6 listings</div>
                                                        </div>
                                                    </a>

                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                            <div className="close_sbfilters"><i className="fal fa-long-arrow-right"></i></div>
                        </div>
                    </form>
                )}
            />


        )
    }
}

const mapStateToProps = (state) => {
    return {
        searchForm: state.searchForm
    }
}

const mapDispatchToProps = (dispatch) => {
    return {

    }
}

export default connect(mapStateToProps, mapDispatchToProps)(HiddenSearchColumn)
