import _ from 'lodash'
import L from 'leaflet'
import 'leaflet-polylinedecorator'
import 'leaflet.markercluster'
import 'leaflet-area-select'
import 'leaflet-swoopy'
import React from 'react'
import { render } from 'react-dom'

import MapHelper from '../../MapHelper'
import LeafletPopupContent from "./LeafletPopupContent";


const listingColor = (task, selected) => {

  if (selected) {
    return '#EEB516'
  }

  return '#777'
}

const listingIcon = listing => {

    return 'arrow-down'
}

const polylineOptions = {
  color: '#3498DB',
  opacity: 0.7
}

const createIcon = username => {
  const iconUrl = window.Routing.generate('user_avatar', { username })

  return L.icon({
    iconUrl: iconUrl,
    iconSize:    [20, 20], // size of the icon
    iconAnchor:  [10, 10], // point of the icon which will correspond to marker's location
    popupAnchor: [-2, -72], // point from which the popup should open relative to the iconAnchor,
  })
}

export default class MapProxy {

  constructor(map, options) {
    this.map = map
    this.polylineLayerGroups = new Map()
    this.polylineAsTheCrowFliesLayerGroups = new Map()

    this.listingMarkers = new Map()
    this.listingPopups = new Map()

    this.listingsLayerGroup = new L.LayerGroup()
    this.listingsLayerGroup.addTo(this.map)

    this.clusterGroup = L.markerClusterGroup({
      showCoverageOnHover: false,
    })


  }

  addListing(listing, selected) {

      let marker = this.listingMarkers.get(listing['@id'])

      const color = listingColor(listing, selected)
      const iconName = listingIcon(listing)
      const coords = [listing.address.geo.latitude, listing.address.geo.longitude]
      const latLng = L.latLng(listing.address.geo.latitude, listing.address.geo.longitude)

      let popupComponent = this.listingPopups.get(listing['@id'])

      if(!marker){

          marker = MapHelper.createMarker(coords, iconName, 'marker', color)

          const el = document.createElement('div')

          popupComponent = React.createRef()

          const cb = () => {
              this.listingMarkers.set(listing['@id'], marker)
              this.listingPopups.set(listing['@id'], popupComponent)
          }

          render(<LeafletPopupContent
            listing={ listing }
            ref={ popupComponent }/>, el, cb)

          const popup = L.popup()
              .setContent(el)

          marker.bindPopup(popup)

      } else {
          // OPTIMIZATION
          // Do *NOT* recreate an icon each time, it's expensive

          const newOpts = {
              icon: iconName,
              textColor: color,
              borderColor: color,
          }
          const currentOpts = _.pick(marker.options.icon.options, [
              'icon',
              'textColor',
              'borderColor',
          ])
          if (!_.isEqual(currentOpts, newOpts)) {
              L.Util.setOptions(marker.options.icon, newOpts)
              marker.setIcon(marker.options.icon)
          }

          if (!marker.getLatLng().equals(latLng)) {
              marker.setLatLng(latLng).update()
          }

          popupComponent.current.updateListing(listing)
      }

      L.Util.setOptions(marker, { listing })

      // marker.off('mouseover').on('mouseover', () => this.onTaskMouseOver(task))
      // marker.off('mouseout').on('mouseout', () => this.onTaskMouseOut(task))
      // marker.off('mousedown').on('mousedown', e => {
      //     // Make sure the element is not dragged
      //     // @see https://javascript.info/mouse-drag-and-drop
      //     e.originalEvent.target.ondragstart = () => false
      //     this.onTaskMouseDown(task)
      // })

      this.listingsLayerGroup.addLayer(marker)
      this.clusterGroup.addLayer(marker)
  }

    showClusters() {
        this.listingsLayerGroup.removeFrom(this.map)
        this.clusterGroup.addTo(this.map)
    }

    hideClusters() {
        this.clusterGroup.removeFrom(this.map)
        this.listingsLayerGroup.addTo(this.map)
    }

}
