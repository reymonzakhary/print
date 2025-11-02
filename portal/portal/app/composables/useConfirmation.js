import { render } from "vue";
import ConfirmationDialog from "../components/global/ConfirmationDialog.vue";

export function useConfirmation({ to = "body" } = {}) {
  const self = getCurrentInstance();
  const resolvePromise = ref(null);
  const rejectPromise = ref(null);

  const confirm = ({ title, message, confirmOptions }) => {
    return new Promise((resolve, reject) => {
      const vnode = h(ConfirmationDialog, {
        title,
        message,
        confirmOptions,
        onConfirm: handleConfirm,
        onCancel: handleCancel,
      });
      vnode.key = Math.random();
      vnode.appContext = self.appContext;
      render(vnode, document.querySelector(to));
      resolvePromise.value = resolve;
      rejectPromise.value = reject;
    });
  };

  const handleConfirm = () => {
    render(null, document.querySelector(to));
    resolvePromise.value?.();
  };

  const handleCancel = () => {
    render(null, document.querySelector(to));
    rejectPromise.value?.({ cancelled: true });
  };

  return {
    confirm,
  };
}
