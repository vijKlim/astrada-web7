import React from 'react'
import {render} from 'react-dom'
import CalendarApp from './CalendarApp'

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href") // activated tab

    if(target == '#calendar'){
        var root = document.getElementById('calendar-wrap');
        render(<CalendarApp {...(root.dataset)} />, root)
    }
});


