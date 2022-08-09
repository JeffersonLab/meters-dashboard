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
            return this.status === 0 ? 'success' : 'danger'
        }
    }
}
</script>

<style scoped>

</style>
