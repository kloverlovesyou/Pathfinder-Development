import Homepage from "@/components/Home/Homepage.vue";
import ARegistrationForms from "@/components/Home/A_RegistrationForm.vue";
import ORegistrationForms from "@/components/Home/O_RegistrationForm.vue";
import LoginForm from "@/components/Home/LoginForm.vue";
import ProfilePage from "@/components/Home/Profilepage.vue";
import TypeOfAccount from "@/components/Home/TypeOfAccount.vue";
import Trainingpage from "@/components/Home/Trainingpage.vue";
import Careerpage from "@/components/Home/Careerpage.vue";
import Organizationpage from "@/components/Home/Organizationpage.vue";
import Calendarpage from "@/components/Home/Calendarpage.vue";
import Bookmarkpage from "@/components/Home/Bookmarkpage.vue";

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
    {
      path: "/trainingpage",
      component: Trainingpage,
    },
    {
      path: "/careerpage",
      component: Careerpage,
    },
    {
      path: "/organizationpage",
      component: Organizationpage,
    },
    {
      path: "/calendarpage",
      component: Calendarpage,
    },
    {
      path: "/bookmarkpage",
      component: Bookmarkpage,
    },
  ],
});

export default router;
