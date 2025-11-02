export function useMessageHandler() {
  const { addToast } = useToastStore();

  function handleError(error) {
    if (!error?.data) {
      if (error?.statusMessage) {
        addMessage(error.statusMessage, "warning");
        return console.error(error);
      }
      addMessage(error, "warning");
      return console.error(error);
    }

    if (error?.data?.errors) {
      Object.keys(error.data.errors).forEach((key) => {
        error.data.errors[key].forEach((err) => {
          addMessage(err, "warning");
          console.error(err);
        });
      });
      return;
    }

    if (error?.data?.message) {
      addMessage(error.data.message, "warning");
      return console.error(error.message);
    }

    if (error?.message) {
      addMessage(error.message, "warning");
      return console.error(error.message);
    }

    addMessage(error, "warning");
    return console.error(error);
  }

  function handleSuccess(response) {
    if (response) {
      if (response.messages) {
        for (const message in response.messages) {
          if (Object.prototype.hasOwnProperty.call(response.messages, message)) {
            const successmessage = response.messages[message];
            addMessage(successmessage[0], "success");
          }
        }
      } else {
        addMessage(response.message, "success");
      }
    } else {
      addMessage(response, "success");
    }
  }

  function addMessage(text, status) {
    addToast({ message: text, type: status });
  }

  return {
    handleError,
    handleSuccess,
  };
}
