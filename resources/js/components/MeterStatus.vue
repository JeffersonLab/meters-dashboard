<template>
<div>
    <power-meter-status-table v-if="hasPowerMeters" :meters="this.powerMeters"
                              :epics-data="this.values" :epics-web-status="status"/>
    <water-meter-status-table v-if="hasWaterMeters" :meters="this.waterMeters"
                              :epics-data="this.values" :epics-web-status="status"/>
    <gas-meter-status-table v-if="hasGasMeters" :meters="this.gasMeters"
                            :epics-data="this.values" :epics-web-status="status"/>
</div>
</template>

<script>
import PowerMeterStatusTable from "./table/PowerMeterStatusTable";
import WaterMeterStatusTable from "./table/WaterMeterStatusTable";
import GasMeterStatusTable from "./table/GasMeterStatusTable";
import MetersEpicsconMixin from "./mixin/meters-epicscon-mixin";
export default {
    name: "MeterStatus",
    mixins: [MetersEpicsconMixin],
    components: {PowerMeterStatusTable, WaterMeterStatusTable, GasMeterStatusTable},
}
</script>

<style scoped>

</style>
