require('./app.js');

// Data attached to window object via the blade template
var epicsOptions = window.epicsOptions;
let jlab = window.jlab;
let epicsCon = window.epicsCon;

// console.log(jlab.metersData);


/**
 * And now we pull in Vue as the basis for our front-end components
 *   -- vue-axios to simplify working with back-end json API
 */
import Vue from 'vue';
import VueAxios from 'vue-axios';

import {BootstrapVue, BootstrapVueIcons} from 'bootstrap-vue';
import MeterStatus from "./components/MeterStatus";
import BuildingCharts from "./components/BuildingCharts";
import MeterMonitor from "./components/MeterMonitor";
import BuildingAlerts from "./components/BuildingAlerts";

Vue.use(VueAxios, axios);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);


/**
 * Create the Vue Component that will show epics status for a building's overall meter alarms status
 */
if (document.getElementById("building-alerts") !== null) {
    const buildingVue = new Vue({
        el: '#building-alerts',
        components: {BuildingAlerts},
        data: {
            epicsCon,
            metersData: jlab.buildingsData,
            withCommErrs: false
        },
        template: `
            <building-alerts
                :meters="metersData"
                :epics-con="epicsCon"
            >
            </building-alerts>`
    });
}


/**
 * Create the Vue Component that will show epics status for a building's meters data via epicsWeb
 */
if (document.getElementById("building-status") !== null) {
    const buildingVue = new Vue({
        el: '#building-status',
        components: {MeterStatus},
        data: {
            epicsCon,
            metersData: jlab.metersData,
        },
        template: `
            <meter-status
                :meters="metersData"
                :epics-con="epicsCon"
            >
            </meter-status>`
    });
}
/**
 * Create the Vue Component that will let user plot charts of consumption data
 */
if (document.getElementById("building-charts") !== null){
    const meterChartTabsVue = new Vue({
        el: '#building-charts',
        components: {BuildingCharts},
        data: {
            metersData: jlab.metersData,
            building: jlab.currentModel
        },
        template: `<building-charts
                        :building="building"
                        :meters="metersData"
                       >
                </building-charts>`
    });
}


/**
 * Create the Vue Component that will let user monitor a set of meters via epics2web
 */
if (document.getElementById("meter-monitor") !== null) {
    const meterMontiorVue = new Vue({
        el: '#meter-monitor',
        components: {MeterMonitor},
        data: {
            epicsCon,
            metersData: jlab.metersData
        },
        template: `
            <meter-monitor
                :meters="metersData"
                :epics-con="epicsCon"
            >
            </meter-monitor>`
    });
}
