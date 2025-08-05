import Homepage from "@/components/Home/Homepage.vue";

import ARegistrationForms from "@/components/Home/A_RegistrationForm.vue";
import ORegistrationForms from "@/components/Home/O_RegistrationForm.vue";
import LoginForm from "@/components/Home/LoginForm.vue";
import ProfilePage from "@/components/Home/Profilepage.vue";
import TypeOfAccount from "@/components/Home/TypeOfAccount.vue";
import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      redirect: "/loginform",
    },
    {
      path: "/homepage",
      component: Homepage,
    },
    {
      path: "/loginform",
      component: LoginForm,
    },
    {
      path: "/a_registrationform",
      component: ARegistrationForms,
    },
    {
      path: "/o_registrationform",
      component: ORegistrationForms,
    },
    {
      path: "/typeofaccount",
      component: TypeOfAccount,
    },
    {
      path: "/profilepage",
      component: ProfilePage,
    },
  ],
});

export default router;
