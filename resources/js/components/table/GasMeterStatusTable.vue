<template>
<b-card>
    <template #header>
        <h4 class="mb-0">
            <i class="fas fa-fw fa-cloud text-yellow"></i>
            Gas Meters
            <epics-web-status-icon style="float:right" :epics-web-status="epicsWebStatus"></epics-web-status-icon>
        </h4>
    </template>
    <b-table class="meter-data" small :items="meterItems" :fields="fields">
        <!-- A custom formatted meter name column -->
        <template #cell(meter)="data">
            <b-link target="_blank" :href="meterLink(data.item.id)">{{data.value}}</b-link>
        </template>

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
import meterStatusTableMixin from "../mixin/meter-status-table-mixin";
export default {
    name: "GasMeterStatusTable",
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
                    label: 'Flow (ccfpm)'
                },
                {
                    key: 'volume',
                    class: 'readout',
                    label: 'Volume (ccf)'
                }
            ]
        }
    },
    computed: {
        meterItems() {
            return this.meters.map(item => {return {
                id: item.id,
                meter: item.epics_name,
                comms: this.commErr(item.epics_name),
                flow: this.flow(item.epics_name),
                volume: this.volume(item.epics_name),
            }})
        }
    },
    methods: {
        flow(meterName){
            return this.pvState(meterName+'_ccfPerMin')
        },
        volume(meterName){
            return this.pvState(meterName+'_ccf')
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
