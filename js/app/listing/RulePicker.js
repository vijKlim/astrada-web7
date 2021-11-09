import React from 'react'
import PropTypes from 'prop-types'
import { withTranslation } from 'react-i18next'
import _ from 'lodash'

import RulePickerLine from './RulePickerLine'
import { parseAST } from '../delivery/pricing-rule-parser'

export const numericTypes = [
  'distance',
  'weight',
]

export const isNum = (type) => _.includes(numericTypes, type)

const lineToString = state => {
  /*
  Build the expression line from the user's input stored in state.
  Returns nothing if we can't build the line.
  */

  if (state.operator === 'in' && Array.isArray(state.right) && state.right.length === 2) {
    return `${state.left} in ${state.right[0]}..${state.right[1]}`
  }

  if (state.left === 'drillingKids' && state.operator === 'containsAtLeastOne') {
    return `drillingKids.containsAtLeastOne("${state.right}")`
  }
    if (state.left === 'pipeDiameters' && state.operator === 'containsAtLeastOne') {
        return `pipeDiameters.containsAtLeastOne("${state.right}")`
    }



  switch (state.operator) {
  case '<':
  case '>':
    return `${state.left} ${state.operator} ${state.right}`
  case 'in_zone':
  case 'out_zone':
    return `${state.operator}(${state.left}, "${state.right}")`
  case '==':
    if (state.left === 'dropoff.doorstep' || _.includes(numericTypes, state.left)) {
      return `${state.left} == ${state.right}`
    }
    return `${state.left} == "${state.right}"`
  }
}

const linesToString = lines => lines.map(line => lineToString(line)).join(' and ')

class RulePicker extends React.Component {

  constructor (props) {
    super(props)

    this.state = {
      lines: this.props.expressionAST ? parseAST(this.props.expressionAST) : [],
      // This is used as a "revision counter",
      // to create an accurate React key prop
      rev: 0
    }

    this.addLine = this.addLine.bind(this)
    this.updateLine = this.updateLine.bind(this)
    this.deleteLine = this.deleteLine.bind(this)
  }

  addLine (evt) {
    evt.preventDefault()
    let lines = this.state.lines.slice()
    lines.push({
      type: '',
      operator: '',
      value: ''
    })
    this.setState({
      lines,
      rev: this.state.rev + 1
    })
  }

  deleteLine(index) {
    let lines = this.state.lines.slice()
    lines.splice(index, 1)
    this.setState({
      lines,
      rev: this.state.rev - 1
    })
  }

  updateLine(index, line) {
    let lines = this.state.lines.slice()
    lines.splice(index, 1, line)
    this.setState({ lines })
  }

  componentDidUpdate () {
    this.props.onExpressionChange(linesToString(this.state.lines))
  }

  render () {

    return (
      <div className="rule-picker">
        <table className="table mb-2">
          <tbody>
          { this.state.lines.map((line, index) => (
            <RulePickerLine
              key={ `${index}-${this.state.rev}` }
              index={ index }
              type={ line.left }
              operator={ line.operator }
              value={ line.right }
              drillingKids={ this.props.drillingKids }
              pipeDiameters={ this.props.pipeDiameters }
              onUpdate={ this.updateLine }
              onDelete={ this.deleteLine } />
          )) }
          </tbody>
        </table>
        <div className="text-right">
          <button className="btn btn-xs btn-default" onClick={this.addLine}>
            <i className="fa fa-plus"></i> { this.props.t('RULE_PICKER_ADD_CONDITION') }
          </button>
        </div>
        {/*
        <div className="rule-picker-preview">
          <pre>{ linesToString(this.state.lines) }</pre>
        </div>
        */}
      </div>
    )
  }
}

RulePicker.defaultProps = {
  expression: '',
  onExpressionChange: () => {},
    drillingKids: [],
    pipeDiameters: [],
}

RulePicker.propTypes = {
  expression: PropTypes.string.isRequired,
  onExpressionChange: PropTypes.func.isRequired,
    drillingKids: PropTypes.arrayOf(PropTypes.string),
    pipeDiameters: PropTypes.arrayOf(PropTypes.string),
}

export default withTranslation()(RulePicker)
