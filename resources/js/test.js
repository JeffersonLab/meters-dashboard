require('./app.js');

// Data attached to window object via the blade template
var epicsOptions = window.epicsOptions;
let jlab = window.jlab;
let epicsCon = window.epicsCon;

// let metersData = [
//     {
//         "id": 88,
//         "epics_name": "87-L1",
//         "pvs": [':llVolt', ':totkW'],
//     },
//     {
//         "id":89,
//         "epics_name":"87-L2",
//         "pvs": [':llVolt', ':totkW'],
//     }
// ]

//let metersData = jlab.powerMonitor;

/**
 * And now we pull in Vue as the basis for our front-end components
 *   -- vue-axios to simplify working with back-end json API
 */
import Vue from 'vue';
import VueAxios from 'vue-axios';
import {BootstrapVue, BootstrapVueIcons} from 'bootstrap-vue';
import PowerMeterStatusTable from "./components/PowerMeterStatusTable";
import WaterMeterStatusTable from "./components/WaterMeterStatusTable";
import moment from "moment";

Vue.use(VueAxios, axios);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

document.addEventListener("DOMContentLoaded", () => {
    // if (document.getElementById("#voltage-readouts")){
    const vm = new Vue({
        el: '.water-meter-status-table',
        components: {WaterMeterStatusTable},
        data: {
            pvs: [],
            values: {},
            metersData: jlab.waterMonitor,
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
                console.log(this.metersData);
                this.pvs = [];
                this.metersData.forEach(item => {
                    this.pvs.push(item.epics_name+':commErr')
                    item.pvs.forEach(pv => {
                        let pvName = item.epics_name + pv
                        this.pvs.push(pvName)
                        this.pvs.push(pvName + '.STAT')
                    })
                })
                // this.pvs = this.pvs.concat(metersData.map(meter => meter.comm_err));  //comm Errors
                // this.pvs = this.pvs.concat(metersData.map(meter => meter.stat));     // alarm status
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
        },
        template: `<water-meter-status-table
                            :meters="metersData"
                            :epics-data="values"
                           >
                    </water-meter-status-table>`
    });
    // }
});
