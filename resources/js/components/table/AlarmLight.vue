<template>
    <b-icon-Square-fill scale="1" :variant="variant"></b-icon-Square-fill>
</template>

<script>
import {BIcon, BIconSquareFill} from "bootstrap-vue";

export default {
    name: "AlarmLight",
    props: ['status'],
    components: {BIcon, BIconSquareFill},
    computed: {
        variant() {
            // First check for the uninitialized status
            if (this.status.alarmState === null || this.status.alarmState === ""){
                return 'light'   // gray for indeterminate
            }
            if (this.status.alarmState === 'HIHI' || Math.floor(this.status.value) === 2){
                return 'danger'
            }
            if (this.status.alarmState === 'HIGH' || Math.floor(this.status.value) === 1){
                return 'warning'
            }
            if (this.status.alarmState === 'NO_ALARM' || Math.floor(this.status.value) === 0){
                return 'success'
            }
            return 'light'  // white for no data
        }
    }
}
</script>

<style scoped>

</style>
