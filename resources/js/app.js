"use strict";

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// window.flatpickr = require("flatpickr");




/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

window._ = require('lodash');

// To use adminLTE w/bootstrap
window.$ = window.jQuery = require('jquery');
require('overlayscrollbars');
require('bootstrap');
require('../../vendor/almasaeed2010/adminlte/dist/js/adminlte');

// Enable tooltips globally
$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
