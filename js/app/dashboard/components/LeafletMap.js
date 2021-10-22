import React, { Component } from 'react'
import { render } from 'react-dom'
import { connect } from 'react-redux'
import MapHelper from '../../MapHelper'
import MapProxy from './MapProxy'
import { ClustersMapToggle } from './MapLayers'

const MapContext = React.createContext([ null, () => {} ])

const MapProvider = (props) => {

    const [ map, setMap ] = React.useState(null);

    const fromTask = React.useRef(null);
    const toTask   = React.useRef(null);

    React.useEffect(() => {

        const LMap = MapHelper.init('map', {
            onLoad: props.onLoad
        })

        const proxy = new MapProxy(LMap, {
            onEditClick: props.setCurrentTask,
            onTaskMouseDown: task => {
                if (task.isAssigned) {
                    proxy.disableDragging()
                    fromTask.current = task
                }
            },
            onTaskMouseOver: task => {
                if (task.isAssigned) {
                    proxy.enableConnect(task)
                }
                if (fromTask.current && task !== fromTask.current && !task.isAssigned) {
                    toTask.current = task
                    proxy.enableConnect(task, true)
                }
            },
            onTaskMouseOut: (task) => {
                if (task.isAssigned) {
                    proxy.hidePolyline(task.assignedTo)
                }
                toTask.current = null
                proxy.disableConnect(task)
            },
            onMouseMove: (e) => {
                if (fromTask.current) {
                    const targetLatLng = !!toTask.current ? proxy.toLatLng(toTask.current) : e.latlng
                    proxy.setDrawPolyline(proxy.toLatLng(fromTask.current), targetLatLng, !!toTask.current)
                    proxy.enableConnect(fromTask.current, !!toTask.current)
                }
            },
            onMouseUp: () => {

                if (!!fromTask.current && !!toTask.current) {
                    props.assignAfter(fromTask.current.assignedTo, toTask.current, fromTask.current)
                }

                if (!!fromTask.current) {
                    proxy.disableConnect(fromTask.current)
                }
                if (!!toTask.current) {
                    proxy.disableConnect(toTask.current)
                }

                fromTask.current = null
                toTask.current = null

                proxy.clearDrawPolyline()
                proxy.enableDragging()
            },
            onMarkersSelected: markers => {
                const taskIds = markers.map(marker => marker.options.task['@id'])
                props.selectTasksByIds(taskIds)
            },
            onPickupClusterClick: (a) => {

                const childMarkers = a.layer.getAllChildMarkers()
                const tasks = childMarkers.map(m => m.options.task)

                const el = document.createElement('div')

                render(<GroupPopupContent
                    onEditClick={ proxy.onEditClick }
                    clusterTasks={ tasks }
                    onMouseEnter={ task => {
                        proxy.pointToNext(task, a.latlng)
                    }}
                    onMouseLeave={ () => {
                        proxy.hideNext()
                    }}
                />, el)

                return el
            }
        })

        setMap(proxy)

    }, [])

    return (
        <MapContext.Provider value={ map }>
            <div id="map"></div>
            { map && props.children }
        </MapContext.Provider>
    )
}

export const useMap = () => React.useContext(MapContext)

class LeafletMap extends Component{

    render() {

        return (
            <MapProvider
                onLoad={ this.props.onLoad }
            >

                <ClustersMapToggle />
            </MapProvider>
        )
    }
}

export default LeafletMap