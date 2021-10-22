import React from 'react'
import { connect } from 'react-redux'
import _ from 'lodash'

import { useMap } from './LeafletMap'





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


function mapStateToPropsClusters(state) {

  return {
    clustersEnabled: state.settings.clustersEnabled,
  }
}


export const ClustersMapToggle = connect(mapStateToPropsClusters)(ClustersToggle)
