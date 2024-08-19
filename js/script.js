const TypesList = {
  props: ['types', 'selected'],
  emits: ['update:selected'],
  template: `
      <div class="mb-3" :style="{fontSize: '14px'}">
          <div class="form-check form-check-inline" v-for="(item, index) in types">
            <input class="form-check-input" type="radio" name="selectedType" v-model="selected" @input="$emit('update:selected', $event.target.value)" :value="item.type" :id="item.type + index">
            <label class="form-check-label" :for="item.type + index">
              {{ item.name }}
            </label>
         </div>
    </div>`
}

const Redis = {
  data() {
    return {
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
  methods: {
    save: function () {
      fetch('save.php', {
        method: 'POST',
        body: JSON.stringify(this.collectBody()),
        headers: {
          "Content-Type": "application/json"
        }
      })
        .then(response => {
          if (!response.ok) {
            return Promise.reject(response);
          }
          return response.json();
        })
        .then(data => this.refresh(data))
        .catch(error => {});
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
    //console.log('beforeUpdate', this.selectedType)
  },
  components: {
    TypesList
  }
  /*beforeCreate() {
    console.log('beforeCreate')
  },
  created() {
    console.log('created')
  },
  beforeMount() {
    console.log('beforeMount')
  },
  mounted() {
    console.log('mounted')
  },
  updated() {
    console.log('updated')
  },
  activated() {
    console.log('activated')
  }*/
}

Vue.createApp(Redis).mount('#app')
