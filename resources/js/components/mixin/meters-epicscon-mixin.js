export default {
    props: {
        meters: {type: Array, required: true},
        epicsCon: {type: Object, required: true},
    },
    data() {
        return {
            pvs: [],
            values: {},
        }
    },
    mounted(){
        this.initPvs();
        this.initValues();
        this.initEpics();
        this.epicsCon.monitorPvs(this.pvs);
    },
    computed: {
        gasMeters()   { return this.meters.filter(meter => meter.type === 'gas') },
        hasGasMeters() { return this.gasMeters && this.gasMeters.length > 0},
        powerMeters() { return this.meters.filter(meter => meter.type === 'power') },
        hasPowerMeters() { return this.powerMeters && this.powerMeters.length > 0},
        waterMeters() { return this.meters.filter(meter => meter.type === 'water') },
        hasWaterMeters() { return this.waterMeters && this.waterMeters.length > 0},
    },
    methods: {
        // Build the list of PVs to be monitored including the
        // .STAT alarm status fields and per-meter commErr signal
        initPvs() {
            this.pvs = [];
            this.meters.forEach(item => {
                this.pvs.push(item.epics_name + ':commErr')
                item.pvs.forEach(pv => {
                    let pvName = item.epics_name + pv
                    this.pvs.push(pvName)
                    this.pvs.push(pvName + '.STAT')
                })
            })
            console.log('initPvs', this.pvs);
        },
        // Initialize the values array before handing it to epicsCon to start
        // receiving updates.
        initValues() {
            _.each(this.pvs, (pv) => {
                this.$set(this.values, this.pvKey(pv), {
                    type: 'init',
                    pv: pv,
                    value: null,
                    date: Date.now()
                });
            })
        },
        // Tell the epicsCon to starting monitoring our list of PVs and supplying updates to values
        initEpics() {
            // console.log(epicsCon);

            let pvs = this.pvs;     // for access inside the onopen closure
            // console.log('inside initEPCS', pvs);
            // epicsCon.onopen = function () {
            //     // console.log('onopen');
            //     epicsCon.monitorPvs(pvs);
            // };
            epicsCon.onupdate = this.updateValues;
            epicsCon.onclose = function (e) {
                console.log('epicsCon closed', e);
            };
            // It appears redundant to make the call below.
            // epicsCon.monitorPvs(this.pvs);
        },
        // The callback handler invoked when a monitored PV receives a value change.
        updateValues(epicsData) {
            if (epicsData.detail.type === 'update') {
                Object.assign(this.values[this.pvKey(epicsData.detail.pv)], epicsData.detail);
            }
            //this.values[this.pvKey(epicsData.detail.pv)] = epicsData.detail;
            console.log(epicsData.detail);
        },
        // Replaces problematic characters found in epics PV names to make them usable as
        // javascript variable names.
        pvKey(pv) {
            return pv.replace(':', '_');
        },
    }
}
