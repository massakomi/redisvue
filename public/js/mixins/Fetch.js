
export default {

  data() {
    return {
      error: false,
      loader: true,
    }
  },

  methods: {
    queryJson: async function (query) {
      this.loader = true
      let response = await fetch(`api.php?${query}`, {
        headers: {
          "Content-Type": "application/json"
        }
      })
      return this.queryResponse(response)
    },

    queryPost: async function (query, json) {
      this.loader = true
      let response = await fetch(`api.php?${query}`, {
        method: 'POST',
        body: JSON.stringify(json),
        headers: {
          "Content-Type": "application/json"
        }
      })
      return this.queryResponse(response)
    },

    queryResponse: async function(response) {
      let data = await this.queryResponseCheck(response)
      if (data.error) {
        this.error = data.message
      }
      this.loader = false
      return data;
    },

    queryResponseCheck: async function (response, type = 'json') {
      if (response.ok) {
        let content;
        if (type === 'text') {
          content = await response.text();
        } else {
          try {
            content = await response.json();
          } catch (e) {
            let message = 'Ошибка ' + e.name + ":" + e.message + "\n" + e.stack;
            return {error: true, message};
          }
        }
        return content;
      } else {
        let error;
        try {
          error = await response.json();
        } catch (e) {
          let message = `${response.status} ${response.statusText}`;
          error = {error: true, message}
        }
        return error;
      }
    }
  }
}