<template>
  <Line
      :chart-data="chartData"
      :chart-options="options"
      :height="height"
      :styles="styles"
      :plugins="plugins"
  />
</template>
<script>
  import { Line } from 'vue-chartjs'
  import HandlesDataLabelsPlugin from './mixins/HandlesDataLabelsPlugin';

  export default {
    components: {Line},
    props: {
      height: {
        default: 120,
        type: Number
      }
    ,
    styles: {
      default: () => ({}),
      type: Object
    }
  },
    mixins: [HandlesDataLabelsPlugin],
  }
</script>
