
export default {
  methods: {
    queryJson: async function (query) {
      let response = await fetch(`action.php?${query}`, {
        headers: {
          "Content-Type": "application/json"
        }
      })
      return this.queryResponse(response)
    },

    queryPost: async function (json) {
      let response = await fetch('action.php', {
        method: 'POST',
        body: JSON.stringify(json),
        headers: {
          "Content-Type": "application/json"
        }
      })
      return this.queryResponse(response)
    },

    queryResponse: async function (response, type = 'json') {
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
          //showError(`${error.message} <span class="text-black-50">${error.file}</span>`);
        } catch (e) {
          let message = `${response.status} ${response.statusText}`;
          //showError(message);
          error = {error: true, message}
        }
        return error;
      }
    }
  }
}