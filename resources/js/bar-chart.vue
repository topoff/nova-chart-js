<template>
  <Bar
      :chart-data="chartData"
      :chart-options="options"
      :height="height"
      :styles="styles"
      :plugins="plugins"
  />
</template>

<script>
import { Bar } from 'vue-chartjs'
import HandlesDataLabelsPlugin from './mixins/HandlesDataLabelsPlugin';

export default {
  components: {Bar},
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
