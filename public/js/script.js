
import { createApp } from './vendor/vue.esm-browser.js'
import { TypesList } from "./components/TypesList.js";

import Fetch from "./mixins/Fetch.js";


const Redis = {
  data() {
    return {
      connected: true,
      error: false,
      loader: true,

      info: [],
      data: [],

      key: null,
      values: [],
      countValues: 2,
      type: 'string',
      expired: null,
      rewrite: true,
      types: [
        {'type': 'string', 'name': 'String'},
        {'type': 'list', 'name': 'List'},
        {'type': 'hash', 'name': 'Hash'},
        {'type': 'unique', 'name': 'Set'},
      ]
    }
  },
  mixins: [
    Fetch
  ],
  methods: {
    async save () {
      let data = await this.queryPost('action=save', this.collectBody())
      this.refresh(data)
    },
    collectBody: function () {
      let values = []
      for (let value of this.values) {
        values.push(value)
      }
      return {
        type: this.type,
        key: this.key,
        expired: this.expired,
        values: values
      }
    },
    async refresh() {
      this.data = await this.queryJson('action=data');
    }
  },
  beforeUpdate() {
    if (this.type === 'string') {
      this.countValues = 1;
    }
  },
  async mounted() {
    let data = await this.queryJson('action=check');
    if (data.error) {
      this.connected = false;
      return;
    }
    this.info = await this.queryJson('action=info');
    this.refresh()
  },
  components: {
    TypesList
  }
}


let app = createApp(Redis);

app.mount('#app')
