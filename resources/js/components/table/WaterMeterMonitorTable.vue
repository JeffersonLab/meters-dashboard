<template>
    <b-card>
        <template #header>
            <h4 class="mb-0"><i class="fas fa-fw fa-tint text-aqua"></i>Water Meters</h4>
        </template>
        <b-table class="meter-data" small
                 :filter="filter"
                 filter-debounce="100"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 :items="meterItems"
                 :fields="fields">
            <!-- A custom formatted column -->
            <template #cell(comms)="data">
                <comms-light :status="data.value" />
            </template>

            <!-- A custom formatted column -->
            <template #cell(meter)="data">
                <b-link target="_blank" :href="meterLink(data.item.id)">{{data.value}}</b-link>
            </template>

            <!-- A custom formatted column -->
            <template #cell(building)="data">
                <b-link target="_blank" :href="buildingLink(data.value)">{{data.value}}</b-link>
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
    name: "PowerMeterMonitorTable",
    props: {filter: {type: String, default:''}},
    mixins: [meterStatusTableMixin],
    data() {
        return {
            sortBy: 'meter',
            sortDesc: false,
            fields: [
                {
                    key: 'meter',
                    class:'meter-name',
                    sortable: true,
                },
                {
                    key: 'building',
                    sortable: true,
                },
                {
                    key: 'comms',
                    class :'comms-status',
                    sortable: true,
                },
                {
                    key: 'flow',
                    class: 'readout',
                    label: 'Flow (gpm)',
                    sortable: true,
                },
                {
                    key: 'volume',
                    class: 'readout',
                    label: 'Volume (gal)',
                    sortable: true,
                }
            ]
        }
    },
    computed: {
        meterItems() {
            return this.meters.map(item => {
                return {
                    id: item.id,
                    meter: item.epics_name,
                    building: item.building,
                    comms: this.commErr(item.epics_name),
                    flow: this.flow(item.epics_name),
                    volume: this.volume(item.epics_name),
                }
            })
        }
    },
    methods: {
        flow(meterName){
            return this.pvState(meterName+'_galPerMin')
        },
        volume(meterName){
            return this.pvState(meterName+'_gal')
        },
    },
}
</script>

<style>

.meter-data .meter-name{
    max-width: 12em;
}
</style>
