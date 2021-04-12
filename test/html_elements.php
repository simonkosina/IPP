<?php

$style_string = "
    .text {
        font-size: large;
        text-indent: 30px;
        padding-bottom: 5px;
    }

    table {
        margin-left: 30px;
        border-collapse: collapse;
        width: 40%;
    }

    td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        width: 70%;
    }

    .td {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        transition-duration: 0.2s;
    }

    .td:hover {
        background-color: white;
        cursor: pointer;
    }

    .success {
        background-color: lightgreen;
    }

    .failure {
        background-color: lightcoral;
    }
    
    .folder4 {
        background-color: #f08080;
    }

    .folder3 {
        background-color: #f5aeae;
    }

    .folder2 {
        background-color: #f9d0d0;
    }

    .folder1 {
        background-color: #ffffff;
    }
";

$script_string = "
    function showTest(name, exp_rc, act_rc, exp_out, act_out) {
        var testWindow = window.open('', 'test', 'width=500, height=500');

        if(testWindow == null || testWindow.closed)
        {
            testWindow = window.open('', 'test', 'width=500, height=500');
        }
        else
        {
            testWindow.focus();
            testWindow.document.open();
        };

        testWindow.document.write('<body></body>');

        el = document.createElement('h3');
        text = document.createTextNode(name);
        el.appendChild(text);
        testWindow.document.body.appendChild(el)

        el = document.createElement('h4');
        el.innerHTML = 'Očakávaný návratový kód';
        testWindow.document.body.appendChild(el);

        el = document.createElement('p');
        text = document.createTextNode(exp_rc);
        el.appendChild(text);
        testWindow.document.body.appendChild(el);


        el = document.createElement('h4');
        el.innerHTML = 'Získaný návratový kód';
        testWindow.document.body.appendChild(el);


        el = document.createElement('p');
        text = document.createTextNode(act_rc);
        el.appendChild(text);
        testWindow.document.body.appendChild(el);


        el = document.createElement('h4');
        el.innerHTML = 'Očakávaný výstup';
        testWindow.document.body.appendChild(el);


        el = document.createElement('p');
        text = document.createTextNode(exp_out);
        el.appendChild(text);
        testWindow.document.body.appendChild(el);


        el = document.createElement('h4');
        el.innerHTML = 'Získaný výstup';
        testWindow.document.body.appendChild(el);


        el = document.createElement('p');
        text = document.createTextNode(act_out);
        el.appendChild(text);
        testWindow.document.body.appendChild(el);
    }
    
    function showTable(id) {
        window.location='#' + id;
    }
";

?>