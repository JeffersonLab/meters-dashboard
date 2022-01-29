<template>
    <a :href="meter.url" target="_blank">
    <div class="info-box">
        <span class="info-box-icon" :class="alarmStateClass"><i class="fa" :class="alarmStateIcon"></i></span>
        <div class="info-box-content">
            <span class="info-box-text info-box-header">{{meter.label}}</span>
            <span v-if="hasData" class="info-box-number">{{currentValue}} Volts</span>

            <!-- The footer-->
            <span v-if ="hasCommErr" class="info-box-text info-box-footer">
                Comm Error Count: {{commErr.value}}
            </span>
            <span v-else class="info-box-text info-box-footer">{{lastUpdated}}</span>

        </div><!-- /.info-box-content -->
    </div><!-- /.info-box -->
    </a>
</template>

<script>
    import moment from "moment";

    export default {
        name: "ReadbackWidget",
        props: ['meter', 'epicsData','commErr','clock'],
        data: function () {
            return {
                updateTimestamp: '',
                alarmStateBgClass: {
                    comm: 'bg-orange',
                    nodata: '',
                    lolo: 'bg-red',
                    low: 'bg-yellow',
                    normal: 'bg-green',
                    high: 'bg-yellow',
                    hihi: 'bg-red',
                },
                alarmStateFaIcon: {
                    comm: 'fa-heartbeat',
                    nodata: 'fa-question',
                    lolo: 'fa-exclamation-triangle',
                    low: 'fa-exclamation-triangle',
                    normal: 'fa-check',
                    high: 'fa-exclamation-triangle',
                    hihi: 'fa-exclamation-triangle',
                }
            }
        },
        computed:{
            currentValue() {
                if (this.hasData){
                    return Math.round( this.epicsData.value)
                }
                return '';
            },


            hasAlarmLimits() { return this.meter.alarm_limits !== undefined },
            hasLoLo() { return (this.hasAlarmLimits && this.meter.alarm_limits.lolo)},
            hasLow() { return this.hasAlarmLimits && this.meter.alarm_limits.low },
            hasHiHi() { return this.hasAlarmLimits && this.meter.alarm_limits.hihi },
            hasHigh() { return this.hasAlarmLimits && this.meter.alarm_limits.high },
            alarmState(){
                if (this.hasCommErr){
                    return 'comm'
                }
                if (! this.hasData){
                    return 'nodata';
                }
                if (this.hasLoLo && this.currentValue <= this.meter.alarm_limits.lolo){
                    return 'lolo';
                }
                if (this.hasLow && this.currentValue <= this.meter.alarm_limits.low){
                    return 'low';
                }
                if (this.hasHiHi && this.currentValue >= this.meter.alarm_limits.hihi){
                    return 'hihi';
                }
                if (this.hasHigh && this.currentValue >= this.meter.alarm_limits.high){
                    return 'high';
                }
                return 'normal';
            },
            alarmStateClass(){
                return this.alarmStateBgClass[this.alarmState];
            },
            alarmStateIcon(){
                return this.alarmStateFaIcon[this.alarmState];
            },
            hasCommErr(){
                return this.commErr.type != 'init' && this.commErr.value > 0;

            },
            hasData(){
                return this.epicsData.type != 'init';
            },
            lastUpdated(){
                if (this.hasData){
                    // try to prevent local clock skew from reporting
                    // "in a few seconds" by ensuring epicsDate is in the past.
                    //console.log('moment', moment(this.epicsData.date).isBefore(this.clock));
                    if ( moment(this.epicsData.date).isBefore(this.clock, 'minute')){
                        return moment(this.epicsData.date).from(this.clock);
                    }
                }
                return '';
            }
        },
        watch: {
            alarmState(newState, oldState){
                this.$emit('alarm-state-change', {meter: this.meter, newState});
                //console.log(this.meter.label + 'from ' + oldState + ' to ' + newState);
            }
        },
    }
</script>

<style scoped>
.info-box-header {
    font-size: 125%;
    font-weight: bold;
}
.info-box-footer {
    font-size: 80%;
    font-weight: lighter;
}
.info-box-number {
    padding: 0.25em 0;
}
.info-box-content {
    text-align: center;
}
</style>
