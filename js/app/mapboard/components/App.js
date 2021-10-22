import React, { Component } from 'react'
import { Route, Redirect } from 'react-router'
import { connect } from 'react-redux'

import MapColumn from './MapColumn'
import ListingColumn from './ListingColumn'




const watchOptions = {
    enableHighAccuracy: true,
    timeout: 60000,
    maximumAge: 0
}

class App extends Component {
    constructor (props) {
        super(props)
        this.state = {
            error: null
        }

        Notification.requestPermission()
    }



    renderListingPage (props) {
        let id = parseInt(props.match.params.id)

        if (this.props.user.data && id === parseInt(this.props.user.data.id)) {
            return <UserProfile {...props} />
        } else {
            console.log('You cannot view this profile.')
            return <Redirect to='/' />
        }
    }



    render () {
        return (
            <div id='mapcolumnView'>
                <MapColumn/>
                <ListingColumn/>
                <div className="limit-box fl-wrap"></div>
            </div>
        )
    }
}


const mapStateToProps = (state) => {
    return {
    }
}

const mapDispatchToProps = dispatch => {
    return {
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(App)