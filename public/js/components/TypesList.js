export const TypesList = {
  props: ['types', 'selected'],
  emits: ['update:selected'],
  template: `
      <div class="mb-3 fs-12">
          <div class="form-check form-check-inline" v-for="(item, index) in types">
            <input class="form-check-input" type="radio" name="type" v-model="selected" @input="$emit('update:selected', $event.target.value)" :value="item.type" :id="item.type + index">
            <label class="form-check-label" :for="item.type + index">
              {{ item.name }}
            </label>
         </div>
    </div>`
}