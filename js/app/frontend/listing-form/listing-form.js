import { createStore } from 'redux'
import AddressBook from '../../listing/AddressBook'

let store

function showAddressOptions(telephone, recipient, isTelephoneRequired, isRecipientRequired) {
    if (telephone) {
        telephone.setAttribute('required', isTelephoneRequired)
        telephone.closest('.form-group').classList.remove('hidden')
    }
    if (recipient) {
        recipient.setAttribute('required', isRecipientRequired)
        recipient.closest('.form-group').classList.remove('hidden')
    }
}
function hideRememberAddress(name) {
    const rememberAddr = document.querySelector(`#${name}_address_rememberAddress`)
    if (rememberAddr) {
        rememberAddr.closest('.checkbox').classList.add('invisible')
    }
}

function showRememberAddress(name) {
    const rememberAddr = document.querySelector(`#${name}_address_rememberAddress`)
    if (rememberAddr) {
        rememberAddr.closest('.checkbox').classList.remove('invisible')
    }
}

function createAddressWidget(name, cb) {

    const telephone = document.querySelector(`#${name}_telephone`)
    const recipient = document.querySelector(`#${name}_recipient`)

    const isTelephoneRequired = telephone && telephone.hasAttribute('required')
    const isRecipientRequired = recipient && recipient.hasAttribute('required')

    new AddressBook(document.querySelector(`#${name}_address`), {
        existingAddressControl: document.querySelector(`#${name}_address_existingAddress`),
        newAddressControl: document.querySelector(`#${name}_address_newAddress_streetAddress`),
        isNewAddressControl: document.querySelector(`#${name}_address_isNewAddress`),
        onReady: address => {
            cb(address)
        },
        onChange: address => {

            if (Object.prototype.hasOwnProperty.call(address, '@id')) {
                if (telephone) {
                    telephone.value = ''
                    telephone.removeAttribute('required')
                    telephone.closest('.form-group').classList.add('hidden')
                }
                if (recipient) {
                    recipient.value = ''
                    recipient.removeAttribute('required')
                    recipient.closest('.form-group').classList.add('hidden')
                }
                hideRememberAddress(name)
            } else {
                showAddressOptions(telephone, recipient, isTelephoneRequired, isRecipientRequired)
                showRememberAddress(name)
            }

            store.dispatch({
                type: 'SET_ADDRESS',
                value: address
            })
        },
        onClear: () => {
            showAddressOptions(telephone, recipient, isTelephoneRequired, isRecipientRequired)
            showRememberAddress(name)
            store.dispatch({
                type: 'CLEAR_ADDRESS',
            })
        }
    })
}

function replaceWeldesign(state, key, value) {

    const welldesignState = {
        ...state.welldesign,
        [key]: value
    }
    console.log(welldesignState,  key, value)
    return welldesignState
}

function reducer(state = {}, action) {
    switch (action.type) {
        case 'SET_ADDRESS':
            return {
                ...state,
                address: action.value,
            }
        case 'CLEAR_ADDRESS':
            return {
                ...state,
                address: null,
            }
        case 'SET_WELLDESIGN':
            return {
                ...state,
                welldesign: replaceWeldesign(state,  action.key, action.value)
            }
        default:
            return state
    }
}


class ListingForm {
    disable() {
        $('#listing-submit').attr('disabled', true)
        $('#loader').removeClass('hidden')
    }
    enable() {
        $('#listing-submit').attr('disabled', false)
        $('#loader').addClass('hidden')
    }
}


export default function(name,subname, options) {

    const el = document.querySelector(`form[name="${name}"]`)
    console.log(el, `form[name="${name}"]`)

    const form = new ListingForm()

    const onChange = options.onChange.bind(form)
    const onReady = options.onReady.bind(form)

    if (el) {
        const addressEl = document.querySelector(`#${name}${subname}_weight`)


        const welldesignPipeDiameterEl = document.querySelector(`#${name}${subname}_welldesign_pipeDiameter`)
        const welldesignDrillingKitEl = document.querySelector(`#${name}${subname}_welldesign_drillingKit`)
        const welldesignDepthFromEl = document.querySelector(`#${name}${subname}_welldesign_depthFrom`)
        const welldesignDepthToEl = document.querySelector(`#${name}${subname}_welldesign_depthTo`)

        // Intialize Redux store
        let preloadedState = {
            address: addressEl ? addressEl.value : null,
            welldesign: {
                pipeDiameter: welldesignPipeDiameterEl ? welldesignPipeDiameterEl.value : null,
                drillingKit: welldesignDrillingKitEl ? welldesignDrillingKitEl.value : null,
                depthFrom: welldesignDepthFromEl ? welldesignDepthFromEl.value : null,
                depthTo: welldesignDepthToEl ? welldesignDepthToEl.value : null,
            }
        }

        if (el.dataset.business) {
            preloadedState = {
                ...preloadedState,
                business: el.dataset.business
            }
        }

        createAddressWidget(name+subname,  address => {
            preloadedState.address = address
        })

        store = createStore(reducer, preloadedState)

        onReady(preloadedState)
        store.subscribe(() => onChange(store.getState()))



        if (welldesignPipeDiameterEl) {
            welldesignPipeDiameterEl.addEventListener('input', _.debounce(e => {
                store.dispatch({
                    type: 'SET_WELLDESIGN',
                    key: 'pipeDiameter',
                    value: e.target.value
                })
            }, 350))
        }

        if (welldesignDrillingKitEl) {
            welldesignDrillingKitEl.addEventListener('input', _.debounce(e => {
                store.dispatch({
                    type: 'SET_WELLDESIGN',
                    key: 'drillingKit',
                    value: e.target.value
                })
            }, 350))
        }

        if (welldesignDepthFromEl) {
            welldesignDepthFromEl.addEventListener('input', _.debounce(e => {
                store.dispatch({
                    type: 'SET_WELLDESIGN',
                    key: 'depthFrom',
                    value: e.target.value
                })
            }, 350))
        }

        if (welldesignDepthToEl) {
            welldesignDepthToEl.addEventListener('input', _.debounce(e => {
                store.dispatch({
                    type: 'SET_WELLDESIGN',
                    key: 'depthTo',
                    value: e.target.value
                })
            }, 350))
        }
    }

    return form
}