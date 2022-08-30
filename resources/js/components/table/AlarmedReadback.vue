<template>
<span :class="alarmState" >{{readout}}</span>
</template>

<script>
export default {
    name: "AlarmedReadback",
    props: ['pvData'],
    computed: {
        readout(){
          return this.pvData.value !== null ? this.pvData.value : 'N/A'
        },
        alarmState(){
            if (this.pvData.alarmState){
                if(this.pvData.alarmState === 'HIHI') return 'text-danger'
                if(this.pvData.alarmState === 'LOLO') return 'text-danger'
                if(this.pvData.alarmState === 'HIGH') return 'text-warning'
                if(this.pvData.alarmState === 'LOW') return 'text-warning'
            }
            return ''
        }
    }

}
</script>

<style scoped>

.text-danger, .text-warning{
    font-weight: bold;
}


</style>
