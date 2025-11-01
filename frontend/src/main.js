import "./assets/main.css";
import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./Router/routes.js";
import useAuth from "@/composables/useAuth";
import { useAuthStore } from "@/stores/auth";
import * as Cally from "cally";

const app = createApp(App);
const pinia = createPinia();

app.use(router);
app.use(pinia);
app.use(Cally.default || Cally);

const { attempt } = useAuth();

attempt().finally(() => {
  app.mount("#app");
});
