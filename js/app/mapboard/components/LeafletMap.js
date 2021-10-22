import React, { Component } from 'react'
import { render } from 'react-dom'
import { connect } from 'react-redux'
import MapHelper from '../../MapHelper'
import MapProxy from './MapProxy'
import {ClustersMapToggle, ListingMapLayer} from './MapLayers'
import {selectAllListings} from "../redux/selectors";

const MapContext = React.createContext([ null, () => {} ])

const MapProvider = (props) => {

    const [ map, setMap ] = React.useState(null);

    React.useEffect(() => {

        const LMap = MapHelper.init('map-main', {
            onLoad: props.onLoad
        })

        const proxy = new MapProxy(LMap, {

        })

        setMap(proxy)

    }, [])

    return (
        <MapContext.Provider value={ map }>
            <div id="map-main"></div>
            { map && props.children }
        </MapContext.Provider>
    )
}

export const useMap = () => React.useContext(MapContext)

class LeafletMap extends Component{

    render() {

        return (
            <MapProvider
                listings={ this.props.listings }
                onLoad={ this.props.onLoad }
            >
                <ListingMapLayer />
                <ClustersMapToggle />
            </MapProvider>
        )
    }
}

function mapStateToProps(state) {

    return {
        listings: selectAllListings(state),
    }
}

function mapDispatchToProps (dispatch) {

    return {
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(LeafletMap)