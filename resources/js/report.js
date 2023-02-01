import BuildingAlerts from "./components/BuildingAlerts";

require('./app.js');

console.log(jlab.metersData);

/**
 * And now we pull in Vue as the basis for our front-end components
 *   -- vue-axios to simplify working with back-end json API
 */
import Vue from 'vue';
import VueAxios from 'vue-axios';

import {BootstrapVue, BootstrapVueIcons} from 'bootstrap-vue';
import ConsumptionReportFilters from "./components/ConsumptionReportFilters";
import CoolingTowerReportFilters from "./components/CoolingTowerReportFilters.vue";
import vSelect from 'vue-select'

Vue.use(VueAxios, axios);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.component('v-select', vSelect)

/**
 * Create the Vue Component that will show report data
 */
if (document.getElementById("consumption-report-filters") !== null) {
    const consumptionReportVue = new Vue({
        el: '#consumption-report-filters',
        components: {ConsumptionReportFilters},
        data: {
            jlab,
        },
        template: `
            <consumption-report-filters
                :request="jlab.request"
                :title="jlab.reportTitle"
                :meters="jlab.metersData"
                :buildings="jlab.buildingsData"
            >
            </consumption-report-filters>`
    });
}

if (document.getElementById("cooling-tower-report-filters") !== null) {
    const consumptionReportVue = new Vue({
        el: '#cooling-tower-report-filters',
        components: {CoolingTowerReportFilters},
        data: {
            jlab,
        },
        template: `
            <cooling-tower-report-filters
                :request="jlab.request"
                :title="jlab.reportTitle"
            >
            </cooling-tower-report-filters>`
    });
}
