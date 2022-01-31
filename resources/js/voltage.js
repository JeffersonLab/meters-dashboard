require('./app.js');

// Data attached to window object via the blade template
let metersData = window.metersJson ? JSON.parse(window.metersJson) : [];
var epicsOptions = window.epicsOptions;
let jlab = window.jlab;
let epicsCon = window.epicsCon;

/**
 * And now we pull in Vue as the basis for our front-end components
 *   -- vue-axios to simplify working with back-end json API
 */
import Vue from 'vue';
import VueAxios from 'vue-axios';
import ReadbacksList from "./components/ReadbacksList";
import moment from "moment";

Vue.use(VueAxios, axios);

document.addEventListener("DOMContentLoaded", () => {
    // if (document.getElementById("#voltage-readouts")){
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
    // }
});


