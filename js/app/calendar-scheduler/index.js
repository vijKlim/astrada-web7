import React from 'react'
import {render} from 'react-dom'
import SchedulerApp from "./SchedulerApp";

//https://github.com/StephenChou1017/react-big-scheduler/blob/master/example/Basic.js

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href") // activated tab

    if(target == '#calendar'){
        var root = document.getElementById('calendar-wrap');
        render(
            <SchedulerApp {...(root.dataset)} />, root)
    }
});


