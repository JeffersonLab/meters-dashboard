<template>
    <b-icon-circle-fill scale="1" :variant="variant"></b-icon-circle-fill>
</template>

<script>
import {BIcon, BIconCircleFill} from "bootstrap-vue";

export default {
    name: "AlarmLight",
    props: ['status'],
    components: {BIcon, BIconCircleFill},
    computed: {
        variant() {
            // First check for the uninitialized status
            if (this.status.alarmState === null || this.status.alarmState === ""){
                return 'secondary'   // gray for indeterminate
            }
            if (this.status.alarmState === 'HIHI' || this.status.alarmState === 'LOLO'){
                return 'danger'
            }
            if (this.status.alarmState === 'HIGH' || this.status.alarmState === 'LOW'){
                return 'warning'
            }
            if (this.status.alarmState === 'NO_ALARM'){
                return 'success'
            }
            // Otherwise a count of zero comm errors is good
            return Math.floor(this.status.value) === 0 ? 'success' : 'secondary'
        }
    }
}
</script>

<style scoped>

</style>
