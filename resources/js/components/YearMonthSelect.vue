<template>
<b-row class="m-0 p-0">
    <b-col class="mb-0 p-0">
    <b-form-select  :options="monthOptions" v-model="selectMonth"></b-form-select>
    </b-col>
    <b-col class="mb-0 p-0">
    <b-form-select  :options="yearOptions" v-model="selectYear"></b-form-select>
    </b-col>
</b-row>
</template>

<script>
export default {
    name: "YearMonthSelect",
    props: ['year','month','minYear'],
    created() {
        // Populate the monthOptions
        this.monthOptions = Array.from({length: 12}, (e, i) => {
            let date = new Date(null, i + 1, null).toLocaleDateString("en", {month: "short"});
            return {text: date, value: i}
        })
        // Populate the yearOptions
        let maxYear = new Date().getFullYear()
        for (let i=this.minYear; i <= maxYear; i++){
            this.yearOptions.push(i)
        }
    },
    data() {
        return {
            yearOptions: [],
            monthOptions: [],
        }
    },
    computed: {
        selectYear:{
          get() {return this.year},
          set(v) {this.$emit('selectYear',v)}
        },
        selectMonth:{
            get() {return this.month},
            set(v) {this.$emit('selectMonth',v)}
        },
        maxYear(){
            return (new Date()).getFullYear()
        },

    }
}
</script>

<style scoped>

</style>
