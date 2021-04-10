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
        width: 60%;
    }

    td, th {
    border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        width: 50%;
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

        testWindow.document.write('<h3>' + name + '</h3>')
        testWindow.document.write('<h4>Očakávaný návratový kód</h4>');
        testWindow.document.write('<p>' + exp_rc + '</p>');
        testWindow.document.write('<h4>Získaný návratový kód</h4>');
        testWindow.document.write('<p>' + act_rc + '</p>');
        testWindow.document.write('<h4>Očakávaný výstup</h4>');
        testWindow.document.write('<p>' + exp_out + '</p>');
        testWindow.document.write('<h4>Získaný výstup</h4>');
        testWindow.document.write('<p>' + act_out + '</p>');
    }
";

?>