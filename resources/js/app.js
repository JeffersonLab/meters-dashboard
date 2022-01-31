"use strict";

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.flatpickr = require("flatpickr");

/**
 * And now we pull in Vue as the basis for our front-end components
 *   -- vue-axios to simplify working with back-end json API
 */
import Vue from 'vue';

import VueAxios from 'vue-axios';
Vue.use(VueAxios, axios);


/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

// let jlab = window.jlab;

/**
 *  Finally, the app itself
 */


// Data attached to window object via the blade template
let metersData = window.metersJson ? JSON.parse(window.metersJson) : [];
var epicsOptions = window.epicsOptions;
let jlab = window.jlab;
let epicsCon = window.epicsCon;

import ReadbacksList from "./components/ReadbacksList";

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("#voltage-readouts")){
        const vm = new Vue({
            el: '#voltage-readouts',
            components: {ReadbacksList},
            data: {
                pvs: [],
                values: {},
                metersData,
                alarmStateWeights: {
                    'nodata'  : 100,
                    'normal'  : 10,
                    'high'  : 5,
                    'low'  : 5,
                    'hihi' : 1,
                    'lolo' : 1,
                    'comm' : 5,
                }
            },
            created(){
                this.initPvs();
                this.initValues();
                this.initEpics();
            },
            mounted(){
            },
            methods:{
                initPvs(){
                    console.log(metersData);
                    this.pvs = metersData.map(meter => meter.pv);
                    this.pvs = this.pvs.concat(metersData.map(meter => meter.comm_err));  //comm Errors
                    this.pvs = this.pvs.concat(metersData.map(meter => meter.stat));     // alarm status
                    console.log('initPvs', this.pvs);
                },
                initValues(){
                    _.each(this.pvs, (pv)=>{
                        this.$set(this.values, this.pvKey(pv), {
                            type: 'init',
                            pv: pv,
                            value: 0.0,
                            date: Date.now()
                        });
                    })
                },
                initEpics(){
                    console.log(epicsCon);

                    let pvs = this.pvs;     // for access inside the onopen closure
                    console.log('inside initEPCS', pvs);
                    epicsCon.onopen = function () {
                        console.log('onopen');
                        epicsCon.monitorPvs(pvs);
                    };
                    epicsCon.onupdate = this.updateValues;
                    epicsCon.onclose = function(e) {
                        console.log('epicsCon closed', e);
                    };
                    console.log('start monitoring now');
                    epicsCon.monitorPvs(this.pvs);
                },
                updateValues(epicsData){
                    if (epicsData.detail.type == 'update'){
                        Object.assign(this.values[this.pvKey(epicsData.detail.pv)], epicsData.detail);
                    }
                    //this.values[this.pvKey(epicsData.detail.pv)] = epicsData.detail;
                    console.log(epicsData.detail);
                },
                pvKey(pv){
                    return pv.replace(':','_');
                },
                alarmStateChange(data){
                    console.log('app alarmStateChange', data);
                    this.$set(data.meter, 'alarm_state', this.alarmStateWeights[data.newState]);
                }
            },
            template: `<readbacks-list
                            :items="metersData"
                            :values="values"
                            @alarm-state-change="alarmStateChange">
                    </readbacks-list>`
        });
    }
});


