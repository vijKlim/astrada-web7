import React, { Component } from 'react'
import { connect } from 'react-redux'
import HiddenSearchColumn from "./HiddenSearchColumn";
import SubFooter from "./SubFooter";
import ListingList from "./ListingList";
import ListingHeader from "./ListingHeader";


class ListingColumn extends Component {
    constructor (props) {
        super(props)
        this.state = {

        }


    }



    renderListingList () {
        if (this.props.listings.data.length === 0 && this.props.currentLocation.location) {
            return (
                <Segment raised textAlign='center'>
                    <Header as='h3'>There are no listings around you.</Header>
                    <Header as='h3'>😞</Header>
                    <Header as='h3'>Post a question you want answered!</Header>
                </Segment>
            )
        } else if (this.props.listings.data.length === 0) {
            return (
                <Segment raised>
                    <Dimmer active inverted>
                        <Loader size='massive' inline='centered'>Loading Listings</Loader>
                    </Dimmer>
                    <Image src='https://react.semantic-ui.com/assets/images/wireframe/paragraph.png' />
                    <Divider />
                    <Image src='https://react.semantic-ui.com/assets/images/wireframe/paragraph.png' />
                </Segment>
            )
        } else {
            console.log(this.props.listings);
            return (
                <div id='listinglist' className='listing-list'>

                    <Visibility onBottomVisible={() => { alert('ok');this.props.listings.data.push({'id':28,'title':'title 9','username':'test_user_LOAD','avatar':'/images/onPointLogo-Green-Blue.png','message':'How do this?LOAD','location':''});console.log(this.props.listings.data) }} once={false}>
                        {this.props.listings.data.map(listing =>  <ListEntry key={listing.id} listing={listing} />)}
                    </Visibility>
                </div>
            )
        }
    }

    render () {
        return (
            <div>
                <HiddenSearchColumn/>
                <div className="col-list-wrap anim_clw  ">
                    <ListingHeader/>
                    <ListingList/>
                    <SubFooter/>
                </div>
            </div>

        )
    }
}

const mapStateToProps = (state) => {
    return {


    }
}

const mapDispatchToProps = (dispatch) => {
    return {
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ListingColumn)
