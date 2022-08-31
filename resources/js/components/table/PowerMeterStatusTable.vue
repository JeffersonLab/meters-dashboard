<template>
<b-card>
    <template #header>
        <h4 class="mb-0">
            <i class="fas fa-fw fa-bolt text-red"></i>
            Power Meters
            <epics-web-status-icon style="float:right" :epics-web-status="epicsWebStatus"></epics-web-status-icon>
        </h4>
    </template>
    <b-table class="meter-data" small :items="meterItems" :fields="fields">
        <!-- A custom formatted column -->
        <template #cell(comms)="data">
            <comms-light :status="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(volt)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(power)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

    </b-table>

</b-card>
</template>

<script>

import meterStatusTableMixin from "../mixin/meter-status-table-mixin";

export default {
    name: "PowerMeterStatusTable",
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
                    key: 'volt',
                    class: 'readout',
                },
                {
                    key: 'power',
                    class: 'readout',
                    label: 'Power (kW)'
                }
            ]
        }
    },
    computed: {
        meterItems() {
            return this.meters.map(item => {return {
                meter: item.epics_name,
                comms: this.commErr(item.epics_name),
                volt: this.voltage(item.epics_name),
                power: this.power(item.epics_name),
            }})
        }
    },
    methods: {
        voltage(meterName){
            return this.pvState(meterName+'_llVolt')
        },
        power(meterName){
            return this.pvState(meterName+'_totkW')
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
