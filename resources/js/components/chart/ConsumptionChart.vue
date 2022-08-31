<template>
    <div :id="id" style="height: 360px; width: 100%;"></div>
</template>

<script>

var CanvasJS = require('../../canvasjs-3.6.6.min');
CanvasJS = CanvasJS.Chart ? CanvasJS : window.CanvasJS;

export default {
    name: "ConsumptionChart",
    props: {
      id: {required: true}, // A unique ID for the chart DOM element
      data: { type: Array, required: true},  // Chart data
      title: { type: Object, required: true},  // Chart title
    },
    watch:{
        // When the data changes, we need to re-render the chart.
        data(v) {
            this.chart = new CanvasJS.Chart(this.id, this.chartOptions);
            this.chart.render()
        },
    },
    computed: {
        chartOptions() {
            return {
                title: this.title,
                data: this.data,
                options: {
                    responsive: true
                }
            }
        }
    },
    data() {
        return {
            chart: null
        }
    },
    mounted: function () {
        this.chart = new CanvasJS.Chart(this.id, this.chartOptions);
        this.chart.render()
        // Code below is so that a graph that was initially hidden in a tab can be drawn to correct size
        // relative to its container once it becomes visible.
        // @see https://forum.vuejs.org/t/detect-when-component-is-shown-after-being-hidden/110827/2
        this.$nextTick(() => {
            this.intersectionObserver = new IntersectionObserver(this.onWindowResized, { threshold: 0 });
            this.intersectionObserver.observe(this.$el);
        });
    },
    destroyed(){
        this.intersectionObserver.disconnect();
    },
    methods: {
        onWindowResized(){
            console.log('howdy from onWindowResize '+this.id)
            if (this.chart) {
                this.chart.render()
            }
        }
    }
}
</script>

<style scoped>

</style>
