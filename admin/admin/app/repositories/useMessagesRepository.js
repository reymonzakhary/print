export const useMessagesRepository = () => {
  const { $api } = useNuxtApp();

  /**
   * Send a handshake request
   * @param {Object} data
   * @param {string} data.title - The title of the message
   * @param {string} data.subject - The subject of the message
   * @param {string} data.body - The body of the message
   * @param {string} data.recipient_hostname - The hostname of the recipient
   * @param {string} data.recipient_email - The email of the recipient
   * @returns {Promise}
   * @throws {Error} If the request fails
   */
  async function sendMessage(data) {
    const body = {
      to: data.to || "supplier",
      type: data.type || "contract",
      title: data.title,
      subject: data.subject,
      body: data.body,
      recipient_hostname: data.recipient_hostname,
      recipient_email: data.recipient_email,
    };
    const response = await $api(`messages`, {
      method: "POST",
      body: body,
    });
    return response;
  }

  /**
   * Reply to a handshake request
   * @param {Object} data
   * @param {string} data.id - The handshake request id
   * @param {string} data.title - The title of the message
   * @param {string} data.body - The body of the message
   * @param {string} data.st - The status of the handshake request
   * @returns {Promise}
   * @throws {Error} If the request fails
   */
  async function replyMessage(data) {
    const body = data;
    const response = await $api(`messages/${data.id}/reply`, {
      method: "PUT",
      body: body,
    });
    return response;
  }

  /**
   * Update a message
   * @param {string} id - The message id
   * @param {Object} data
   * @param {boolean} data.read - The read status of the message
   * @returns {Promise}
   * @throws {Error} If the request fails
   */
  async function updateMessage(id, data) {
    const response = await $api.put(`messages/${id}`, {
      read: data.read,
    });
    return response;
  }

  /**
   * Fetch all handshakes
   * @return {*} The handshakes
   * @throws {Error} If the request fails
   */
  async function fetchMessages() {
    const response = await $api(`messages?type=recipient`, {
      method: "GET",
    });
    return response.data;
  }

  return {
    sendMessage,
    replyMessage,
    updateMessage,
    fetchMessages,
  };
};
