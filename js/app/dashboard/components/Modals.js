import React from 'react'
import { connect } from 'react-redux'
import Modal from 'react-modal'

import { selectSelectedDate } from '../redux'

import {
  closeFiltersModal,
  openSettings,
  closeSettings } from '../redux/actions'

import FiltersModalContent from './FiltersModalContent'
import SettingsModalContent from './SettingsModalContent'


class Modals extends React.Component {

  render () {
    return (
      <React.Fragment>
        <Modal
          appElement={ document.getElementById('dashboard') }
          isOpen={ this.props.filtersModalIsOpen }
          onRequestClose={ () => this.props.closeFiltersModal() }
          className="ReactModal__Content--filters"
          shouldCloseOnOverlayClick={ true }>
          <FiltersModalContent />
        </Modal>
        <Modal
          appElement={ document.getElementById('dashboard') }
          isOpen={ this.props.settingsModalIsOpen }
          onRequestClose={ () => this.props.closeSettings() }
          className="ReactModal__Content--settings"
          shouldCloseOnOverlayClick={ true }>
          <SettingsModalContent />
        </Modal>
      </React.Fragment>
    )
  }
}

function mapStateToProps(state) {

  return {
    filtersModalIsOpen: state.filtersModalIsOpen,
    settingsModalIsOpen: state.settingsModalIsOpen,
    importModalIsOpen: state.importModalIsOpen,
    addModalIsOpen: state.addModalIsOpen,
    date: selectSelectedDate(state),
  }
}

function mapDispatchToProps (dispatch) {

  return {
    closeFiltersModal: () => dispatch(closeFiltersModal()),
    openSettings: () => dispatch(openSettings()),
    closeSettings: () => dispatch(closeSettings()),
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Modals)
