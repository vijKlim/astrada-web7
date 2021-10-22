import React, { Component } from 'react'
import { Link, withRouter } from 'react-router-dom'
import { connect } from 'react-redux'

const range = (from, to, step = 1) => {
    let i = from;
    const range = [];

    while (i <= to) {
        range.push(i);
        i += step;
    }

    return range;
}

class Pagination extends Component {
    constructor (props) {
        super(props)

        this.state = {

        }


    }

    getPaginationUrl(page){
        var url = new URL(window.location.href)
        url.searchParams.set('page',page)
        return url.toString()
    }

    renderPrev(){
        const {
            pagination,
        } = this.props
        if (pagination.page > 1)
        {

            return <a href={this.getPaginationUrl(parseInt(pagination.page) -1)}
               className="prevposts-link"><i className="fas fa-caret-left"></i><span>Prev</span></a>
        }
        return null
    }

    renderNext(){
        const {
            pagination,
        } = this.props
        if(pagination.page < pagination.pages_count)
        {

            return <a href={this.getPaginationUrl(parseInt(pagination.page) +1)}
               className="nextposts-link"><span>Next</span><i className="fas fa-caret-right"></i></a>
        }
        return null
    }


    render () {

        const {
            pagination,
        } = this.props

        if(pagination.pages_count > 1){

            return (
                <div className="pagination">

                    {this.renderPrev()}

                    {range(Math.max(pagination.page-4, 1), Math.min(parseInt(pagination.page)+4, pagination.pages_count)).map((p, index) => (
                        <a key={index} className={p == pagination.page ? "current-page" : ""}
                          href={this.getPaginationUrl(parseInt(p))}>
                            {p}
                        </a>
                    ))}


                    {this.renderNext()}

                </div>
            )
        }else{
            return (
                <div className="pagination"></div>
            )

        }

    }
}

export default Pagination
