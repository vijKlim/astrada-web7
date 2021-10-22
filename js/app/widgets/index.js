import DatePicker from './DatePicker'
import './sylius-form-collection';

window.Astrada = window.Astrada || {}

window.Astrada.DatePicker = DatePicker

$(document).ready(() => {
    $('[data-form-type="collection"]').CollectionForm();

});

