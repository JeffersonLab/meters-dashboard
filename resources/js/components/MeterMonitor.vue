<template>
    <div>
        <b-form-group
            label="Filter"
            label-for="filter-input"
            label-cols-sm="6"
            label-align-sm="right"
            label-size="sm"
            class="mb-0"
        >
            <b-input-group size="sm">
                <b-form-input
                    id="filter-input"
                    v-model="filter"
                    type="search"
                    placeholder="Type to Filter the Table"
                ></b-form-input>
            </b-input-group>
        </b-form-group>
        <power-meter-monitor-table v-if="hasPowerMeters" :meters="this.powerMeters" :epics-data="this.values" :filter="filter"/>
    </div>
</template>

<script>
import MetersEpicsconMixin from "./mixin/meters-epicscon-mixin";
import PowerMeterMonitorTable from "./table/PowerMeterMonitorTable";

export default {
    name: "MeterMonitor",
    mixins: [MetersEpicsconMixin],
    components: {PowerMeterMonitorTable},
    data() {
        return {
            filter: '',
        }
    }
}
</script>

<style scoped>

</style>
