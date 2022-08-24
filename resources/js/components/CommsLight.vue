<template>
    <b-icon-circle-fill scale="1" :variant="variant"></b-icon-circle-fill>
</template>

<script>
import {BIcon, BIconCircleFill} from "bootstrap-vue";
export default {
    name: "CommsLight",
    props: ['status'],
    components: {BIcon, BIconCircleFill},
    computed: {
        variant() {
            // First check for the uninitialized status
            if (this.status === null || this.status === ""){
                return 'secondary'
            }
            // Otherwise a count of zero comm errors is good
            return this.status === 0 ? 'success' : 'danger'
        }
    }
}
</script>

<style scoped>

</style>
