<template>
<b-card>
    <template #header>
        <h4 class="mb-0"><i class="fas fa-fw fa-tint text-aqua"></i>Water Meters</h4>
    </template>
    <b-table class="meter-data" small :items="meterItems" :fields="fields">
        <!-- A custom formatted column -->
        <template #cell(comms)="data">
            <comms-light :status="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(flow)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(volume)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

    </b-table>

</b-card>
</template>

<script>

import meterStatusTableMixin from "./meter-status-table-mixin";

export default {
    name: "WaterMeterStatusTable",
    mixins: [meterStatusTableMixin],
    data() {
        return {
            fields: [
                {
                    key: 'meter',
                },
                {
                    key: 'comms',
                        class :'comms-status'
                },
                {
                    key: 'flow',
                    class: 'readout',
                    label: 'Flow (gpm)'
                },
                {
                    key: 'volume',
                    class: 'readout',
                    label: 'Volume (gal)'
                }
            ]
        }
    },
    computed: {
        meterItems() {
            return this.meters.map(item => {return {
                meter: item.epics_name,
                comms: this.commErr(item.epics_name),
                flow: this.flow(item.epics_name),
                volume: this.volume(item.epics_name),
            }})
        }
    },
    methods: {
        flow(meterName){
            return this.pvState(meterName+'_galPerMin')
        },
        volume(meterName){
            return this.pvState(meterName+'_gal')
        },
    }
}
</script>
<style>
.meter-data .comms-status{
    text-align: center;
    max-width: 4em;
}
.meter-data .readout{
    text-align: right;
    max-width: 8em;
}
</style>
<style scoped>

</style>
