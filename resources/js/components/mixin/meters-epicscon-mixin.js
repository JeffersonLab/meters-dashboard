export default {
    props: {
        meters: {type: Array, required: true},
        epicsCon: {type: Object, required: true},
    },
    data() {
        return {
            pvs: [],
            values: {},
            status: 'disconnected'  // [disconnected, connected, connecting]
        }
    },
    async mounted(){
        this.initPvs();
        this.initValues();
        this.initEpics();
        // A pause to let the screen draw before opening the websocket firehose
        await new Promise(resolve => setTimeout(resolve, 1000));
        this.epicsCon.monitorPvs(this.pvs);
    },
    computed: {
        isConnected() { return this.status === 'connected'},
        isConnecting() { return this.status === 'connecting'},
        isDisconnected() { return this.status === 'disconnected'},
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
            //console.log('initPvs', this.pvs);
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
            this.epicsCon.onupdate = this.updateValues;

            this.epicsCon.onpong = function (e) {
                console.log('Ponged', e);
            };

            this.epicsCon.onerror = function (e) {
                console.log('onerror', e);
            };

            this.epicsCon.onclose = function (e) {
                console.log('onclose', e);
            }

            this.epicsCon.onclosing = function (e) {
                console.log('onclosing', e);
            }

            this.epicsCon.addEventListener('error', function (event) {
                console.log('error event')
                this.status = 'disconnected'
            }.bind(this));
            this.epicsCon.addEventListener('connecting', function (event) {
                console.log('connecting event')
               this.status = 'connecting'
            }.bind(this));
            this.epicsCon.addEventListener('open', function (event) {
                console.log('open event')
                this.status = 'connected'
            }.bind(this));
            this.epicsCon.addEventListener('close', function (event) {
                console.log('close event')
                this.status = 'disconnected'
            }.bind(this));
            this.epicsCon.addEventListener('closing', function (event) {
                console.log('closing event')
                this.status = 'disconnected'
            }.bind(this));
            // It appears redundant to make the call below.
            // epicsCon.monitorPvs(this.pvs);
        },
        updateStatus(status){

        },

        // The callback handler invoked when a monitored PV receives a value change.
        updateValues(epicsData) {
            if (epicsData.detail.type === 'update') {
                Object.assign(this.values[this.pvKey(epicsData.detail.pv)], epicsData.detail);
                this.status = 'connected'
            }else{
                console.log(epicsData.detail.type)
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
