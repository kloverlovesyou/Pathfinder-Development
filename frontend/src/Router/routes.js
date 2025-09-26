import Homepage from "@/components/Home/Homepage.vue";
import ProfilePage from "@/components/Home/Profilepage.vue";
import ARegistrationForms from "@/components/Home/A_RegistrationForm.vue";
import ORegistrationForms from "@/components/Home/O_RegistrationForm.vue";
import LoginForm from "@/components/Home/LoginForm.vue";
import Trainingpage from "@/components/Home/Trainingpage.vue";
import Careerpage from "@/components/Home/Careerpage.vue";
import Organizationpage from "@/components/Home/Organizationpage.vue";
import Calendarpage from "@/components/Home/Calendarpage.vue";
import Bookmarkpage from "@/components/Home/Bookmarkpage.vue";
import Certificatespage from "@/components/Home/Certificatespage.vue";
import UpdateDeletepage from "@/components/Home/UpdateDeletepage.vue";
import ResumeEditorpage from "@/components/Home/ResumeEditorpage.vue";
import TypeOfAccount from "@/components/Home/TypeOfAccount.vue";
import OrgHomePage from "@/components/Organization/OrganizationHomepage.vue";
import OrgTraining from "@/components/Organization/OrganizationTrainings.vue";
import OrgCareer from "@/components/Organization/OrganizationCareers.vue";
import MainLayout from "@/components/Layout/MainLayout.vue";
import AuthLayout from "@/components/Layout/AuthLayout.vue";
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // Always redirect root to login
    {
      path: "/",
      redirect: "/auth/login",
    },

      // Auth pages
    {
      path: "/auth",
      component: AuthLayout,
      children: [
        { path: "login", name: "Login", component: LoginForm },
        { 
          path: "register", 
          alias: "/typeofaccount",   // 👈 Now /typeofaccount works too
          name: "Register", 
          component: TypeOfAccount 
        },
        {
          path: "aregistration",
          name: "ARegistrationForms",
          component: ARegistrationForms,
        },
        {
          path: "oregistration",
          name: "ORegistrationForms",
          component: ORegistrationForms,
        },
      ],
    },

    // Main app pages (with sidebar + header)
    {
      path: "/app",
      component: MainLayout,
      meta: { requiresAuth: true },
      children: [
        { path: "", name: "Homepage", component: Homepage },
        { path: "profile", name: "Profile", component: ProfilePage },
        { path: "training", name: "Trainingpage", component: Trainingpage },
        { path: "career", name: "Careerpage", component: Careerpage },
        {
          path: "organization",
          name: "Organizationpage",
          component: Organizationpage,
        },
        { path: "calendar", name: "Calendarpage", component: Calendarpage },
        { path: "bookmarks", name: "Bookmarkpage", component: Bookmarkpage },
        {
          path: "certificates",
          name: "Certificatespage",
          component: Certificatespage,
        },
        {
          path: "update-delete",
          name: "UpdateDeletepage",
          component: UpdateDeletepage,
        },
        {
          path: "resume-editor",
          name: "ResumeEditorpage",
          component: ResumeEditorpage,
        },
      ],
    },

    // Organization-specific pages
{
  path: "/organization",
  component: OrgHomePage,
  meta: { requiresAuth: true, role: "organization" },
  children: [
    {
      path: "",
      name: "OrgHome",
      component: OrgHomePage,
    },
    {
      path: "org-trainings",
      name: "OrgTrainings",
      component: OrgTraining,
    },
    {
      path: "org-careers",
      name: "OrgCareers",
      component: OrgCareer,
    },
  ],
},


    
    // 🚨 Catch-all must always be last
    {
      path: "/:pathMatch(.*)*",
      redirect: "/auth/login", // or use a NotFound component
    },
  ],
});

router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem("user"));

  // 1. Block access if route requires auth and no user
  if (to.meta.requiresAuth && !user) {
    return next({ name: "Login" });
  }

  // 2. Block access if role does not match
  if (to.meta.role && (!user || user.role !== to.meta.role)) {
    return next({ name: "Login" });
  }


  // 4. Otherwise, continue
  next();
});

export default router;