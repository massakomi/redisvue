
import { createApp } from './vendor/vue.esm-browser.js'
import { TypesList } from "./components/TypesList.js";

import Fetch from "./mixins/Fetch.js";


const Redis = {
  data() {
    return {
      connected: false,
      error: false,
      loader: true,
      key: null,
      values: [1, '2,4'],
      countValues: 2,
      selectedType: 'list',
      expired: null,
      rewrite: true,
      types: [
        {'type': 'string', 'name': 'String'},
        {'type': 'list', 'name': 'List'},
        {'type': 'hash', 'name': 'Hash'},
        {'type': 'unique', 'name': 'Unique list'},
      ]
    }
  },
  mixins: [
    Fetch
  ],
  methods: {
    async save () {
      let data = await this.queryPost(this.collectBody())
      this.refresh(data)
    },
    collectBody: function () {
      let values = []
      for (let value of this.values) {
        values.push(value)
      }
      return {
        selectedType: this.selectedType,
        key: this.key,
        values: values
      }
    },
    refresh: function(data) {
      console.log(data)
    }
  },
  beforeUpdate() {
    if (this.selectedType === 'string') {
      this.countValues = 1;
    }
  },
  async mounted() {
    await this.queryJson('action=check')
  },
  components: {
    TypesList
  }
}


let app = createApp(Redis);

app.component('types-list', TypesList)

app.mount('#app')
