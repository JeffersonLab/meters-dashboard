<template>
<b-card header="Water Meters">
    <b-table class="meter-data" small :items="meterItems" :fields="fields">
        <!-- A custom formatted column -->
        <template #cell(comms)="data">
            <comms-light :status="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(flow)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

        <!-- A custom formatted column -->
        <template #cell(volume)="data">
            <alarmed-readback :pv-data="data.value" />
        </template>

    </b-table>

</b-card>
</template>

<script>
import CommsLight from "./CommsLight";
import AlarmedReadback from "./AlarmedReadback";
export default {
    name: "WaterMeterStatusTable",
    components: {AlarmedReadback, CommsLight},
    props: ['meters', 'epicsData'],
    data() {
        return {
            fields:[
                {
                    key: 'meter',
                },
                {
                    key: 'comms',
                    class: 'comms-status'
                },
                {
                    key: 'flow',
                    class: 'readout',
                    label: 'Flow (gpm)'
                },
                {
                    key: 'volume',
                    class: 'readout',
                    label: 'Volume (gal)'
                }
            ]
        }
    },
    computed: {
        meterItems() {
            return this.meters.map(item => {return {
                meter: item.epics_name,
                comms: this.commErr(item.epics_name),
                flow: this.flow(item.epics_name),
                volume: this.volume(item.epics_name),
            }})
        }
    },
    methods: {
        flow(meterName){
            return this.pvState(meterName+'_galPerMin')
        },
        volume(meterName){
            return this.pvState(meterName+'_gal')
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
