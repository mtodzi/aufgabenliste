/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';
const bootstrap = require('bootstrap');

import $ from 'jquery';
// Делаем jQuery доступным глобально, так как многие старые плагины этого требуют
global.$ = global.jQuery = $;

import 'select2';
import 'select2/dist/css/select2.min.css';

$(document).ready(function() {
    $('.select2').select2({
        width: '100%', // Адаптируем ширину под родительский контейнер (Bootstrap)
    });
});
