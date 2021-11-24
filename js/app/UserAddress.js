import axios from 'axios'

const baseURL = location.protocol + '//' + location.hostname

class UserAddress {

    addNew(address){

        const addressPromise = new Promise((resolve) => {
            axios({
                method: 'post',
                url: baseURL + '/user-address',
                data: address,
                headers: {
                    'Accept': 'application/ld+json',
                    'Content-Type': 'application/ld+json',
                }
            })
                .then(response => resolve({ success: true, data: response.data }))
                .catch(e => {
                    let message = ''

                    if (e.response && e.response.status === 400) {
                        if (Object.prototype.hasOwnProperty.call(e.response.data, '@type') && e.response.data['@type'] === 'hydra:Error') {
                            message = e.response.data['hydra:description']
                        }
                    }

                    resolve({ success: false, message })
                })
        })


        return Promise
            .all([ addressPromise ])
    }
}

export default function() {
    return new UserAddress()
}