<template>
    <div>
        <b-card>
            <b-form @submit="onSubmit" @reset="onReset">
                <b-form-row>
                    <b-col>
                        <b-form-group label="Begin" label-cols="2">
                            <b-form-datepicker required id="begin-date" label-cols="3" label="Begin"
                                               v-model="beginDate" :state="validBeginDate"
                                               :date-format-options="{year: 'numeric', month: 'short', day: '2-digit'}"
                                               class="mb-2"/>
                            <b-form-timepicker required id="begin-time" v-model="beginTime"/>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group label="End" label-cols="2">
                            <b-form-datepicker required id="end-date" label-cols="3" label="End"
                                               v-model="endDate" :state="validEndDate"
                                               :date-format-options="{year: 'numeric', month: 'short', day: '2-digit'}"
                                               class="mb-2"/>
                            <b-form-timepicker required id="end-time" v-model="endTime"/>
                        </b-form-group>
                    </b-col>
                </b-form-row>
                <b-form-row>
                    <b-button type="submit" variant="primary">Submit</b-button>
                    <b-button type="reset" variant="secondary">Reset</b-button>
                    <b-button @click="onClear" type="button" variant="danger">Clear</b-button>
                </b-form-row>
            </b-form>
        </b-card>
    </div>
</template>

<script>

export default {
    name: "CoolingTowerReportFilters",
    props: ['request', 'title'],
    data: function () {
        return {
            validated: false,
            beginDate: null,
            beginTime: '00:00',
            endDate: null,
            endTime: '00:00',
        }
    },
    // Upon creating we want to initialize the form fields with data from the request
    created(){
        this.initFromRequest()
    },
    computed: {
        validBeginDate() {
            if (this.validated) {
                return this.beginDate != null
            }
            return null
        },
        validEndDate() {
            if (this.validated) {
                return this.endDate != null
            }
            return null
        },
        hasValidForm() {
            return this.validBeginDate && this.validEndDate
        },
        formData() {
            return {
                begin: this.beginDate + ' ' + this.beginTime,
                end: this.endDate + ' ' + this.endTime,
            }
        }
    },
    methods: {
        initFromRequest() {
            this.beginDate = this.request.begin ? this.dateOnly(this.request.begin) : null
            this.beginTime = this.request.begin ? this.timeOnly(this.request.begin) : null
            this.endDate = this.request.end ? this.dateOnly(this.request.end) : null
            this.endTime = this.request.end ? this.timeOnly(this.request.end) : null
        },
        // Return the date portion of YYYY-MM-DD HH:MM string
        dateOnly(dateTimeStr) {
            return dateTimeStr.split(' ')[0]
        },
        // Return the time portion of YYYY-MM-DD HH:MM string
        timeOnly(dateTimeStr) {
            let time = dateTimeStr.split(' ')[1]
            return time ? time : '00:00'
        },
        onSubmit(event) {
            event.preventDefault()
            this.validated = true
            if (this.hasValidForm) {
                const searchParams = new URLSearchParams(this.formData);
                window.location.search = searchParams.toString();
            }
        },
        onReset(event) {
            event.preventDefault()
            // Reset our form values
            this.validated = false
            this.initFromRequest()
        },
        onClear(event) {
            event.preventDefault()
            // clear our form values
            this.validated = false,
                this.beginDate = null,
                this.beginTime = '00:00',
                this.endDate = null,
                this.endTime = '00:00',
                this.selectedBuildings = []
        }
    }
}
</script>

<style scoped>

</style>
