import React, { useRef, useState, useEffect } from "react";
import PropTypes from 'prop-types';

import axios from 'axios'

import ngeohash from 'ngeohash'
import { usePosition } from '../../usePosition';


// Import Swiper React components
import { Swiper, SwiperSlide } from "swiper/react";

// Import Swiper styles
import "swiper/css";
import "swiper/css/bundle"


// import Swiper core and required modules
import SwiperCore, {
    Pagination
} from 'swiper';

// install Swiper modules
SwiperCore.use([Pagination]);

export const NearbyListingSlides  = ({watch, settings}) => {

    const [listings, setListings] = useState([]);

    const {
        latitude,
        longitude,
        timestamp,
        accuracy,
        speed,
        heading,
        error,
    } = usePosition(watch, settings);

    useEffect(() => {

        console.log(latitude , longitude ,error)
        if (latitude && longitude && !error) {

             const geohash = ngeohash.encode(latitude, longitude, 11)
                 console.log(latitude, longitude, geohash)
             axios
                 .get('/listing/nearby?geohash='+geohash)
                 .then((response) => {
                     if (response.status === 200) {
                         setListings(response.data.listings)
                     }
                 })
                 // eslint-disable-next-line
                 .catch((e) => { /* do nothing */ })

        }
    }, [latitude,longitude,error]);


    const loader = !listings ? (
        <>
            <div>Trying to fetch location...</div>
            <br/>
        </>
    ) : null;




    return (
        <>
            {loader}

            <Swiper slidesPerView={1} spaceBetween={10} grabCursor={true} pagination={{
                "dynamicBullets": true
            }}
                    className="swiper-container swiper-container-mx-negative items-slider-full px-lg-5 pt-3"
                    breakpoints={{
                        "640": {
                            "slidesPerView": 2,
                            "spaceBetween": 20
                        },
                        "768": {
                            "slidesPerView": 4,
                            "spaceBetween": 20
                        },
                        "1024": {
                            "slidesPerView": 5,
                            "spaceBetween": 20
                        },
                    }}>


                {listings.map((item, index) => (
                    <SwiperSlide key={index} className="h-auto px-2">
                        <div className="w-100 h-100 hover-animate" data-marker-id="59c0c8e33b1527bfe2abaf92">
                            <div className="card h-100 border-0 shadow">
                                <div className="card-img-top overflow-hidden gradient-overlay">
                                    <img className="img-fluid"
                                         src={item.image}
                                         alt={item.title}/><a
                                    className="tile-link" href={item.href}></a>

                                </div>
                                <div className="card-body d-flex align-items-center">
                                    <div className="w-100">
                                        <h6 className="card-title">
                                            <a className="text-decoration-none text-dark"
                                                                      href={item.href}>{item.title}</a>
                                        </h6>
                                        <p className="text-sm text-secondary card-subtitle mb-2"><i
                                            className="fa fa-map-marker text-secondary opacity-4 mr-1"></i>{item.address.streetAddress}</p>

                                        <p className="card-text d-flex justify-content-between text-gray-800 text-sm">
                                            <span><i className="fa fa-tag text-primary opacity-4 text-xs mr-1"></i>$150k</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </SwiperSlide>
                ))}


            </Swiper>
        </>
    )


};

NearbyListingSlides.propTypes = {
    watch: PropTypes.bool,
    settings: PropTypes.object,
};

// class NearbyListingSlides extends React.Component {
//
//
//
//      constructor(props) {
//          super(props)
//          this.state = {
//              listings: [],
//          }
//      }
//
//      componentDidMount() {
//
//          // console.log(this.props.coords)
//          // const geohash = ngeohash.encode(this.props.coords.latitude, this.props.coords.longitude, 11)
//          // axios
//          //     .get('/search/geocode?')
//          //     .then((response) => {
//          //         if (response.status === 200) {
//          //             this.setState(response.data)
//          //         }
//          //     })
//          //     // eslint-disable-next-line
//          //     .catch((e) => { /* do nothing */ })
//
//      }
//
//      render() {
//          // const { listings } = this.state
//          const  listings  = [1,2,3,4,5,6,7,8,9,10]
//
//          console.log(this.props.coords)
//          return (
//              <>
//                  <Swiper slidesPerView={1} spaceBetween={10} grabCursor={true} pagination={{
//                      "dynamicBullets": true
//                  }}
//                          className="swiper-container swiper-container-mx-negative items-slider-full px-lg-5 pt-3"
//                          breakpoints={{
//                              "640": {
//                                  "slidesPerView": 2,
//                                  "spaceBetween": 20
//                              },
//                              "768": {
//                                  "slidesPerView": 4,
//                                  "spaceBetween": 20
//                              },
//                              "1024": {
//                                  "slidesPerView": 5,
//                                  "spaceBetween": 20
//                              },
//                          }}>
//
//                      {listings.map((item, index) => (
//                          <SwiperSlide key={index} className="h-auto px-2">
//                              <div className="w-100 h-100 hover-animate" data-marker-id="59c0c8e33b1527bfe2abaf92">
//                                  <div className="card h-100 border-0 shadow">
//                                      <div className="card-img-top overflow-hidden gradient-overlay">
//                                          <img className="img-fluid"
//                                               src="/img/restaurant-1477763858572-cda7deaa9bc5.jpg"
//                                               alt="Modern, Well-Appointed Room"/><a
//                                          className="tile-link" href="detail-rooms.html"></a>
//                                          <div className="card-img-overlay-top text-right"><a
//                                              className="card-fav-icon position-relative z-index-40">
//                                              <svg className="svg-icon text-white">
//                                                  <use xlinkHref="#heart-1"></use>
//                                              </svg>
//                                          </a></div>
//                                      </div>
//                                      <div className="card-body d-flex align-items-center">
//                                          <div className="w-100">
//                                              <h6 className="card-title"><a className="text-decoration-none text-dark"
//                                                                            href="detail-rooms.html">Modern, Well-Appointed
//                                                  Room</a></h6>
//                                              <p className="text-sm text-secondary card-subtitle mb-2"><i
//                                                  className="fa fa-map-marker text-secondary opacity-4 mr-1"></i>San Francisco</p>
//                                              <p className="text-sm text-muted text-uppercase">House</p>
//                                              <p className="card-text d-flex justify-content-between text-gray-800 text-sm"><span
//                                                  className="mr-1"><i
//                                                  className="fa fa-ruler-combined text-primary opacity-4 text-xs mr-1"></i>350 m<sup>2</sup>   </span><span
//                                                  className="mr-1"><i
//                                                  className="fa fa-bed text-primary opacity-4 text-xs mr-1"></i>3</span><span
//                                                  className="mr-1"><i
//                                                  className="fa fa-bath text-primary opacity-4 text-xs mr-1"></i>2</span><span><i
//                                                  className="fa fa-tag text-primary opacity-4 text-xs mr-1"></i>$150k</span></p>
//                                          </div>
//                                      </div>
//                                  </div>
//                              </div>
//                          </SwiperSlide>
//                      ))}
//
//
//                  </Swiper>
//              </>
//          )
//      }
//
//
// }

// export default geolocated({
//     positionOptions: {
//         enableHighAccuracy: false,
//     },
//     userDecisionTimeout: 5000,
// })(NearbyListingSlides);
