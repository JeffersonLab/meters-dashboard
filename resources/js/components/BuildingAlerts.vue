<template>
<building-alert-table :meters="meters" :epics-data="this.values" :epics-web-status="status">

</building-alert-table>
</template>

<script>
import metersEpicsconMixin from "./mixin/meters-epicscon-mixin";
import BuildingAlertTable from "./table/BuildingAlertTable";

export default {
    name: "BuildingAlerts",
    components: {BuildingAlertTable},
    mixins: [metersEpicsconMixin],

}
</script>

<style scoped>

</style>
