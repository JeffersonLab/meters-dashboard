<template>
    <div>
        <div v-for="itemCluster in chunkedItems" class="row">
            <div v-for="item in itemCluster" :key="item.id" class="col-md-4 col-sm-6 col-xs-12">
                <readback-widget
                :meter="item"
                :clock="clock"
                :epics-data="meterValue(item.pv)"
                :comm-err="meterValue(item.comm_err)"
                @alarm-state-change="alarmStateChange">
                </readback-widget>
            </div>
        </div>
    </div>
</template>

<script>

    import ReadbackWidget from './ReadbackWidget';
    const clockTick = 10000;  //milliseconds

    export default {
        name: "ReadbacksList",
        props: ['items','values'],
        data: function(){
            return {
                timer: '',
                clock: moment(),
            }
        },
        components: {ReadbackWidget},
        created(){
            this.timer=setInterval(this.updateClock, clockTick);
        },
        computed: {
            chunkedItems(){
                return _.chunk(this.sortedItems, 3);
            },
            sortedItems(){
                return _.sortBy(this.items, ['alarm_state', 'epics_name']);
            }
        },

        methods: {
            pvKey(pv){
                return pv.replace(':','_');
            },
            meterValue(pv){
                return this.values[this.pvKey(pv)];
            },
            updateClock(){
                this.clock = moment();
            },
            alarmStateChange(data){
                this.$emit('alarm-state-change', data)
            }
        },
        beforeDestroy() {
            clearInterval(this.timer);
        }
    }

</script>

<style scoped>

</style>
