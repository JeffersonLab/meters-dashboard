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
        <power-meter-monitor-table v-if="hasPowerMeters" :meters="this.powerMeters"
                                   :epics-data="this.values" :epics-web-status="status"
                                   :filter="filter"/>
        <water-meter-monitor-table v-if="hasWaterMeters" :meters="this.waterMeters"
                                   :epics-data="this.values" :epics-web-status="status"
                                   :filter="filter"/>
        <gas-meter-monitor-table v-if="hasGasMeters" :meters="this.gasMeters"
                                 :epics-data="this.values" :epics-web-status="status"
                                 :filter="filter"/>
    </div>
</template>

<script>
import MetersEpicsconMixin from "./mixin/meters-epicscon-mixin";
import PowerMeterMonitorTable from "./table/PowerMeterMonitorTable";
import WaterMeterMonitorTable from "./table/WaterMeterMonitorTable";
import GasMeterMonitorTable from "./table/GasMeterMonitorTable";

export default {
    name: "MeterMonitor",
    mixins: [MetersEpicsconMixin],
    components: {GasMeterMonitorTable, PowerMeterMonitorTable, WaterMeterMonitorTable},
    data() {
        return {
            filter: '',
        }
    }
}
</script>

<style scoped>

</style>
