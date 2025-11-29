import { ref } from "vue";

// Reactive login state
export const isLoggedIn = ref(!!localStorage.getItem("admin_token"));
export const currentUser = ref(
  JSON.parse(localStorage.getItem("user") || "null")
);