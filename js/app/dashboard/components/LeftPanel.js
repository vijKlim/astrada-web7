import React from 'react'
import { connect } from 'react-redux'
import Split from 'react-split'
import PublicListings from "./PublicListings";

class DashboardApp extends React.Component {

    render() {
        return (
            <div className="dashboard__aside-container">
                <Split
                    sizes={ [ 50, 50 ] }
                    direction={ this.props.splitDirection }
                    style={{ display: 'flex', flexDirection: this.props.splitDirection === 'vertical' ? 'column' : 'row', width: '100%' }}
                    // We need to use a "key" prop,
                    // to force a re-render when the direction has changed
                    key={ this.props.splitDirection }>
                    <PublicListings />
                    <div className="footer">Test</div>
                </Split>


            </div>
        )
    }
}

function mapStateToProps(state) {

    return {
        splitDirection: state.rightPanelSplitDirection,
    }
}
export default connect(mapStateToProps)(DashboardApp)
