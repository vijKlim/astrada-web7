import React from 'react'
import { render } from 'react-dom'
import Select2 from 'react-select2-wrapper';


document.querySelectorAll('[data-select="select2"]').forEach((container) => {

    const list =
        container.dataset.addresses ? JSON.parse(container.dataset.list) : []
    const options =
        container.dataset.options ? JSON.parse(container.dataset.options) : []
    const placeholder = ''
    if(options && options.placeholder){
        placeholder = options.placeholder
    }
    render(
        <Select2 data={list} options={{
            placeholder: placeholder,
        }} />, container)

})
