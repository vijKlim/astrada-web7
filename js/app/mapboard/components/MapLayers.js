import React from 'react'
import { connect } from 'react-redux'
import _ from 'lodash'

import { useMap } from './LeafletMap'

import { selectAllListings } from '../redux/selectors'


const ListingLayer = ({ listings, selectedListings }) => {

    const map = useMap()

    React.useEffect( () => {

        listings.forEach(listing => {
            const selected = -1 !== selectedListings.indexOf(listing)
            map.addListing(listing, selected)
        })
    }, [ listings, selectedListings ])

    return null
}

const ClustersToggle = ({ clustersEnabled }) => {

  const map = useMap()

  React.useEffect(() => {

    if (clustersEnabled) {
      map.showClusters()
    } else {
      map.hideClusters()
    }

  }, [ clustersEnabled ])

  return null
}

function mapStateToPropsListing(state) {

    return {
        listings: selectAllListings(state),
        selectedListings: [],
    }
}

function mapStateToPropsClusters(state) {

  return {
    clustersEnabled: state.settings.clustersEnabled,
  }
}

export const ListingMapLayer = connect(mapStateToPropsListing)(ListingLayer)
export const ClustersMapToggle = connect(mapStateToPropsClusters)(ClustersToggle)
