/*
Mixin with code common to the *MeterStatusTable components
*/

import AlarmedReadback from "./AlarmedReadback";
import CommsLight from "./CommsLight";

export default {
    props: ['meters', 'epicsData'],
    components: {AlarmedReadback, CommsLight},
    computed: {
      fieldList(){
          return Object.values(this.fields)
      }
    },
    methods: {
        pvState(pvKey) {
            if (this.epicsData[pvKey]) {
                return {
                    value: this.round(this.epicsData[pvKey].value).toFixed(1),
                    alarmState: this.alarmState(pvKey + '.STAT')
                }
            }
            return 'N/A'
        },
        alarmState(stat) {
            let alarmData = this.epicsData[stat]
            if (alarmData) {
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
        commErr(meterName) {
            return this.epicsData[meterName + '_commErr'] ? this.epicsData[meterName + '_commErr'].value : 'NA'
        },
        round(value, precision = 1) {
            // from https://stackoverflow.com/questions/7342957/how-do-you-round-to-1-decimal-place-in-javascript
            let multiplier = Math.pow(10, precision || 0);
            return Math.round(value * multiplier) / multiplier;
        }
    }
}
