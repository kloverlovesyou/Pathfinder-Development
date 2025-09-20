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
import Certificatespage from "@/components/Home/Certificatespage.vue";
import UpdateDeletepage from "@/components/Home/UpdateDeletepage.vue";
import ResumeEditorpage from "@/components/Home/ResumeEditorpage.vue";
import CareerDetails from "@/components/Home/CareerDetails.vue";
import TrainingDetails from "@/components/Home/TrainingDetails.vue";
import OrgHomePage from "@/components/Organization/OrganizationHomepage.vue";
import OrgTraining from "@/components/Organization/OrganizationTrainings.vue";
import OrgCareer from "@/components/Organization/OrganizationCareers.vue";
import OrgCalendar from "@/components/Organization/OrganizationCalendar.vue";

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
      meta: { requiresAuth: true, role: "applicant" },
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
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/trainingpage",
      component: Trainingpage,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/careerpage",
      component: Careerpage,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/organizationpage",
      component: Organizationpage,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/calendarpage",
      component: Calendarpage,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/bookmarkpage",
      component: Bookmarkpage,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/certificatespage",
      component: Certificatespage,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/updatedeletepage",
      component: UpdateDeletepage,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/resumepage",
      component: ResumeEditorpage,
    },
    {
      path: "/career/:id",
      name: "CareerDetails",
      component: CareerDetails,
      props: true,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/training/:id",
      name: "TrainingDetails",
      component: TrainingDetails,
      props: true,
      meta: { requiresAuth: true, role: "applicant" },
    },
    {
      path: "/OrganizationHomePage",
      component: OrgHomePage,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/OrgTrainings",
      component: OrgTraining,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/OrgCareers",
      component: OrgCareer,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/OrgCalendar",
      component: OrgCalendar,
      meta: { requiresAuth: true, role: "organization" },
    },

    // 🚨 Catch-all must always be last
    {
      path: "/:pathMatch(.*)*",
      redirect: "/loginform",
      // OR use a component like NotFound.vue instead:
      // component: () => import("@/components/NotFound.vue"),
    },
  ],
});

// ✅ Global navigation guard (MUST be after router is created)
router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem("user"));

  if (to.meta.requiresAuth && !user) {
    return next("/loginform");
  }

  if (to.meta.role && user?.role !== to.meta.role) {
    return next("/loginform"); // or redirect to a "403" page if you like
  }

  next();
});

export default router;