import Homepage from "@/components/Home/Homepage.vue";
import a_registrationform from "@/components/Home/A_RegistrationForm.vue";
import LoginForm from "@/components/Home/LoginForm.vue";
import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      component: Homepage,
    },
    {
      path: "/loginform",
      component: LoginForm,
    },
    {
      path: "/a_registrationform",
      component: a_registrationform,
    },
  ],
});

export default router;
