import React, { Component } from 'react'
import { Link, withRouter } from 'react-router-dom'
import { connect } from 'react-redux'

class SubFooter extends Component {
    constructor (props) {
        super(props)



        this.state = {

        }


    }



    render () {
        return (
            <div className="sub-footer  fl-wrap">
                <div className="container">
                    <div className="copyright"> &#169; Aquastrada 2021 . All rights reserved.</div>
                    <div className="subfooter-nav">
                        <ul className="no-list-style">
                            <li><a href="#">Terms of use</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Blog</a></li>
                        </ul>
                    </div>
                </div>
            </div>

        )
    }
}

export default SubFooter
