import "./assets/main.css";
import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./Router/routes.js";
import useAuth from "@/composables/useAuth";
import { useAuthStore } from "@/stores/auth";
import * as Cally from "cally";
import axios from "axios";

// Add this interceptor
axios.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const app = createApp(App);
const pinia = createPinia();

app.use(router);
app.use(pinia);
app.use(Cally.default || Cally);

const { attempt } = useAuth();

attempt().finally(() => {
  app.mount("#app");
});