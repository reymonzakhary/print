export const useMessagesRepository = () => {
  const api = useAPI();

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
    const response = await api.post(`tenant/messages`, {
      ...data,
      to: data.to || "supplier",
      type: data.type || "contract",
      title: data.title,
      subject: data.subject,
      body: data.body,
      recipient_hostname: data.recipient_hostname,
      recipient_email: data.recipient_email,
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
    const response = await api.post(`tenant/messages/${data.id}/reply`, {
      title: data.title,
      body: data.body,
      st: data.st,
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
    const response = await api.put(`tenant/messages/${id}`, {
      read: data.read,
    });
    return response;
  }

  /**
   * Fetch all handshakes
   * @return {*} The handshakes
   * @throws {Error} If the request fails
   */
  async function fetchMessages(type) {
    const url = type ? `tenant/messages/?type=${type}` : `tenant/messages`;
    const response = await api.get(url);
    return response.data;
  }

  return {
    sendMessage,
    replyMessage,
    updateMessage,
    fetchMessages,
  };
};
