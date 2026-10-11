/**
 * Range selector shared by all chart cards.
 *
 * Nova 5's SelectControl is driven by v-model + an options array. The cards used the older
 * `:selected` + `<option>` slot + `@change` API: the pre-selection stayed empty, the control
 * reset itself to empty after a change and the handler received the native event instead of
 * the value (so every choice fell back to the first range).
 *
 * Ranges switch in three ways:
 *  - options.rangeSeries (custom data, all ranges precomputed): locally, no request;
 *  - options.rangeKey (custom data, rangeSelector()): the card is reloaded through Nova's own,
 *    authorised dashboard cards route with ?chartRanges[rangeKey]=key and the matching card of
 *    the response replaces labels + series;
 *  - model mode: the existing endpoint request with options.advanceFilterSelected.
 * Only the response to the latest choice is applied; on failure the selector returns to the
 * range that is currently shown.
 */
const DEFAULT_FILTERS = [
  ['YTD', 'Year to Date'],
  ['QTD', 'Quarter to Date'],
  ['MTD', 'Month to Date'],
  ['30', '30 Days'],
  ['60', '60 Days'],
  ['365', '365 Days'],
];

/**
 * Filter entries as [key, label] pairs. btnFilterList is an ordered list of pairs
 * (rangeSelector()) or a key => label object (rangeSeries(), older cards).
 */
export function rangeFilterList(options) {
  const list = options ? options.btnFilterList : undefined;
  const entries = list == undefined ? DEFAULT_FILTERS : Array.isArray(list) ? list : Object.entries(list);

  return entries.map(([key, label]) => ({ value: String(key), text: String(label) }));
}

/** Pre-selected key: btnFilterDefault, else the first precomputed range, else QTD (as before). */
export function initialRangeKey(options) {
  if (options && options.btnFilterDefault != undefined) {
    return String(options.btnFilterDefault);
  }
  if (options && options.rangeSeries != undefined) {
    const first = Object.keys(options.rangeSeries)[0];
    if (first != undefined) {
      return String(first);
    }
  }

  return 'QTD';
}

export default {
  props: ['card', 'dashboard'],
  data() {
    return {
      // Labels + series of a reloaded range (rangeKey cards); null = the card's own data.
      rangeData: null,
      // Title of the reloaded card (some titles name the range); null = the card's own title.
      rangeTitle: null,
      rangeRequestSeq: 0,
      // Range whose data is currently shown — the selector returns here when a load fails.
      appliedFilter: null,
    };
  },
  computed: {
    rangeOptions() {
      return this.advanceFilter.map((filter) => ({ value: String(filter.value), label: filter.text }));
    },
  },
  created() {
    this.appliedFilter = this.advanceFilterSelected;
  },
  methods: {
    isCustomData() {
      return this.card.model == 'custom' || this.card.model == undefined;
    },
    onRangeSelected(option) {
      const value = option && option.value != undefined ? String(option.value) : String(this.advanceFilterSelected);
      this.advanceFilterSelected = value;
      const ranges = this.card.options ? this.card.options.rangeSeries : undefined;
      const initial = this.card.options ? String(this.card.options.btnFilterDefault) : undefined;

      // Local switches (precomputed range, or back to the range the card was loaded with) supersede a
      // pending reload and drop reloaded data, so selector and chart cannot diverge.
      if (this.isCustomData() && ranges != undefined && ranges[value] != undefined) {
        this.showOwnData(value);
        return;
      }
      if (this.isCustomData() && this.card.options && this.card.options.rangeKey != undefined) {
        if (value === initial) {
          this.showOwnData(value);
          return;
        }
        this.loadRangeFromDashboard(value);
        return;
      }
      // Model mode: fillData() sends options.advanceFilterSelected to the endpoint.
      this.fillData();
    },
    showOwnData(value) {
      this.startRangeRequest();
      this.rangeData = null;
      this.rangeTitle = null;
      this.loading = false;
      this.appliedFilter = value;
      this.fillData();
    },
    startRangeRequest() {
      this.rangeRequestSeq += 1;

      return this.rangeRequestSeq;
    },
    isCurrentRangeRequest(seq) {
      return seq === this.rangeRequestSeq;
    },
    revertRange(message) {
      if (message) {
        Nova.error(message);
      }
      this.advanceFilterSelected = this.appliedFilter;
    },
    loadRangeFromDashboard(value) {
      const rangeKey = this.card.options.rangeKey;
      if (!this.dashboard) {
        this.revertRange('Zeitraum konnte nicht geladen werden.');
        return;
      }
      const seq = this.startRangeRequest();
      this.loading = true;

      Nova.request()
        .get('/nova-api/dashboards/cards/' + this.dashboard, { params: { chartRanges: { [rangeKey]: value } } })
        .then(({ data }) => {
          if (!this.isCurrentRangeRequest(seq)) {
            return;
          }
          const card = (Array.isArray(data) ? data : []).find(
            (item) => item && item.options && item.options.rangeKey === rangeKey
          );
          if (!card) {
            // The card's identity changed (e.g. the page stayed open over a month change).
            this.loading = false;
            this.revertRange('Das Dashboard ist veraltet — bitte die Seite neu laden.');
            return;
          }
          this.rangeData = {
            labels: card.options.xaxis ? card.options.xaxis.categories : [],
            series: card.series || [],
          };
          this.rangeTitle = card.title || null;
          this.appliedFilter = value;
          this.loading = false;
          this.fillData();
        })
        .catch(() => {
          if (!this.isCurrentRangeRequest(seq)) {
            return;
          }
          this.loading = false;
          this.revertRange('Zeitraum konnte nicht geladen werden.');
        });
    },
  },
};
