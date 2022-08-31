<template>
    <b-card>
        <template #header>
            <h4 class="mb-0">
                <i class="fas fa-fw fa-bolt text-red"></i>
                Power Meters
                <epics-web-status-icon style="float:right" :epics-web-status="epicsWebStatus"></epics-web-status-icon>
            </h4>
        </template>
        <b-table class="meter-data" small
                 :filter="filter"
                 filter-debounce="100"
                 :sort-by="sortBy"
                 :sort-desc="sortDesc"
                 :sort-compare="sortCompare"
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
            <template #cell(volt)="data">
                <alarmed-readback :pv-data="data.value" />
            </template>

            <!-- A custom formatted column -->
            <template #cell(power)="data">
                <alarmed-readback :pv-data="data.value" />
            </template>

            <!-- A custom formatted column -->
            <template #cell(consumed)="data">
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
                    key: 'volt',
                    class: 'readout',
                    sortable: true,
                },
                {
                    key: 'power',
                    class: 'readout',
                    label: 'Power (kW)',
                    sortable: true
                },
                {
                    key: 'consumed',
                    class: 'readout',
                    label: 'Consumed (kWh)',
                    sortable: true
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
                    volt: this.voltage(item.epics_name),
                    power: this.power(item.epics_name),
                    consumed: this.consumed(item.epics_name),
                }
            })
        }
    },
    methods: {
        voltage(meterName) {
            return this.pvState(meterName + '_llVolt')
        },
        power(meterName) {
            return this.pvState(meterName + '_totkW')
        },
        consumed(meterName) {
            return this.pvState(meterName + '_totkWh')
        },
        sortCompare(aRow, bRow, key, sortDesc, formatter, compareOptions, compareLocale) {
            // The default sort algorithm will not properly sort alarm readback fields
            // because it sorts them as stringified objects rather than by their value fields.
            // So here we will sort such fields properly by their numeric values.
            if (key === 'volt' || key === 'power' || key === 'consumed'){
                const a = aRow[key].value // or use Lodash `_.get()`
                const b = bRow[key].value
                return a < b ? -1 : a > b ? 1 : 0
            }
            return null;  // falls back to default sort function
        }
    },
}
</script>

<style>

.meter-data .meter-name{
    max-width: 12em;
}
</style>
