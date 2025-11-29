import { reactive } from "vue";

export const authState = reactive({
  user: JSON.parse(localStorage.getItem("user")) || null,
  token: localStorage.getItem("admin_token") || null,
  isLoggedIn: !!localStorage.getItem("admin_token"),
});

export function login(userData, token) {
  localStorage.setItem("user", JSON.stringify(userData));
  localStorage.setItem("admin_token", token);

  authState.user = userData;
  authState.token = token;
  authState.isLoggedIn = true;
}

export function logout() {
  localStorage.removeItem("user");
  localStorage.removeItem("admin_token");

  authState.user = null;
  authState.token = null;
  authState.isLoggedIn = false;
}