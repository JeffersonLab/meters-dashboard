<template>
<b-card header="Power Meters">
    <b-table class="meter-data" small :items="meterItems" :fields="fields">
        <!-- A custom formatted column -->
        <template #cell(comms)="data">
            <comms-light :status="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(volt)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(power)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

    </b-table>

</b-card>
</template>

<script>
import CommsLight from "./CommsLight";
import AlarmedReadback from "./AlarmedReadback";
export default {
    name: "PowerMeterStatusTable",
    components: {AlarmedReadback, CommsLight},
    props: ['meters', 'epicsData'],
    data() {
        return {
            fields:[
                {
                    key: 'Meter',
                },
                {
                    key: 'Comms',
                    class: 'comms-status'
                },
                {
                    key: 'Volt',
                    class: 'readout',
                },
                {
                    key: 'Power',
                    class: 'readout',
                    label: 'Power (kW)'
                }
            ]
        }
    },
    computed: {
        meterItems() {
            return this.meters.map(item => {return {
                Meter: item.epics_name,
                Comms: this.commErr(item.epics_name),
                Volt: this.voltage(item.epics_name),
                Power: this.power(item.epics_name),
            }})
        }
    },
    methods: {
        voltage(meterName){
            return this.pvState(meterName+'_llVolt')
        },
        power(meterName){
            return this.pvState(meterName+'_totkW')
        },
        pvState(pvKey){
            if (this.epicsData[pvKey]){
                return {
                    value: this.round(this.epicsData[pvKey].value).toFixed(1),
                    alarmState: this.alarmState(pvKey+'.STAT')
                }
            }
            return 'N/A'
        },
        alarmState(stat){
            let alarmData =  this.epicsData[stat]
            if (alarmData){
                if (alarmData.value === 0) return 'NO_ALARM'
                if (alarmData.value === 3) return 'HIHI'
                if (alarmData.value === 4) return 'HIGH'
                if (alarmData.value === 5) return 'LOLO'
                if (alarmData.value === 6) return 'LOW'
                if (alarmData.value === 17) return 'UDF'
                return alarmData.value
            }
            return null
        },
        commErr(meterName){
            return this.epicsData[meterName+'_commErr'] ? this.epicsData[meterName+'_commErr'].value : 'NA'
        },
        round(value, precision=1) {
            // from https://stackoverflow.com/questions/7342957/how-do-you-round-to-1-decimal-place-in-javascript
            let multiplier = Math.pow(10, precision || 0);
            return Math.round(value * multiplier) / multiplier;
        }
    }
}
</script>
<style>
.meter-data .comms-status{
    text-align: center;
    max-width: 4em;
}
.meter-data .readout{
    text-align: right;
    max-width: 8em;
}
</style>
<style scoped>

</style>
