import "./assets/main.css";

import { createApp } from "vue";
import App from "./App.vue";
import router from "./Router/routes.js";

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const app = createApp(App);

const toastOptions = {
  position: 'top-left',
  timeout: 4000,
  closeOnClick: true,
  pauseOnHover: true,
  draggable: true,
  showCloseButtonOnHover: true,
  hideProgressBar: false,
  toastClassName: 'custom-toast', // Custom class
}
app.use(router);
app.use(Toast,toastOptions)
app.mount("#app");  