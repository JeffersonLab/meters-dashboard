<template>
    <div>
        <power-meter-monitor-table v-if="hasPowerMeters" :meters="this.powerMeters" :epics-data="this.values"/>
    </div>
</template>

<script>
import MetersEpicsconMixin from "./mixin/meters-epicscon-mixin";
import PowerMeterMonitorTable from "./table/PowerMeterMonitorTable";

export default {
    name: "MeterMonitor",
    mixins: [MetersEpicsconMixin],
    components: {PowerMeterMonitorTable}
}
</script>

<style scoped>

</style>
