require('./app.js');

// Data attached to window object via the blade template
var epicsOptions = window.epicsOptions;
let jlab = window.jlab;
let epicsCon = window.epicsCon;

console.log(jlab.metersData);

//let metersData = jlab.powerMonitor;

/**
 * And now we pull in Vue as the basis for our front-end components
 *   -- vue-axios to simplify working with back-end json API
 */
import Vue from 'vue';
import VueAxios from 'vue-axios';
import {BootstrapVue, BootstrapVueIcons} from 'bootstrap-vue';
import BuildingMonitor from "./components/BuildingMonitor";
import MeterChartTabs from "./components/MeterChartTabs";

Vue.use(VueAxios, axios);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

/**
 * Create the Vue Component that will monitor a building's meter data via epicsWeb
 * @type {Vue | CombinedVueInstance<Vue, {epicsCon: *, metersData: (*|[{epics_name: string, id: number, pvs: string[]},{epics_name: string, id: number, pvs: string[]}]|[])}, object, object, Record<never, any>>}
 */
const buildingVue = new Vue({
    el: '#building-monitor',
    components: {BuildingMonitor},
    data: {
        epicsCon,
        metersData: jlab.metersData,
    },
    template: `<building-monitor
                        :meters="metersData"
                        :epics-con="epicsCon"
                       >
                </building-monitor>`
});

/**
 * Create the Vue Component that will let user plot consumption per-meter
 */
const meterChartTabsVue = new Vue({
    el: '#meter-chart-tabs',
    components: {MeterChartTabs},
    data: {
        metersData: jlab.metersData,
    },
    template: `<meter-chart-tabs
                        :meters="metersData"
                       >
                </meter-chart-tabs>`
});
