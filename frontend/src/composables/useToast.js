import { ref } from 'vue';

const toasts = ref([]);

export function useToast() {
  const showToast = (message, type = "success", duration = 3000) => {
    const id = Date.now();
    toasts.value.push({ id, message, type });

    setTimeout(() => {
      toasts.value = toasts.value.filter((t) => t.id !== id);
    }, duration);
  };

  const showConfirmToast = (message) => {
    return new Promise((resolve) => {
      const id = Date.now();

      toasts.value.push({
        id,
        message,
        type: "confirm",
        onConfirm: () => {
          toasts.value = toasts.value.filter((t) => t.id !== id);
          resolve(true);
        },
        onCancel: () => {
          toasts.value = toasts.value.filter((t) => t.id !== id);
          resolve(false);
        },
      });
    });
  };

  return {
    toasts,
    showToast,
    showConfirmToast,
  };
}

