import "./assets/main.css";

import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./Router/routes.js";
import useAuth from '@/composables/useAuth'
import { useAuthStore } from "@/stores/auth";

const app = createApp(App);
const pinia = createPinia();


app.use(router);
app.use(pinia);

const { attempt } = useAuth()
attempt().finally(() => {
  app.use(router)
  app.use(pinia)
  app.mount('#app')
})

