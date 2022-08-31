<template>
    <div>
        <b-icon icon="link" variant="success" v-show="isConnected" :font-scale="scale"
                title="Connection established to epicsweb server"/>
        <b-icon icon="lightbulb-off" variant="danger" v-show="isDisconnected" :font-scale="scale"
                title="No connection  to epicsweb server"/>
        <b-icon icon="stopwatch" variant="secondary" v-show="isConnecting" :font-scale="scale"
                title="Attempting to connect to epicsweb server"/>
    </div>
</template>

<script>
export default {
    name: "EpicsWebStatusIcon",
    props: {
        epicsWebStatus: {type: String, required: true},
        scale: {type: Number, default: 1.0},
    },
    computed: {
        isConnected() { return this.epicsWebStatus === 'connected'},
        isConnecting() { return this.epicsWebStatus === 'connecting'},
        isDisconnected() { return this.epicsWebStatus === 'disconnected'},
    }
}
</script>

<style scoped>

</style>
