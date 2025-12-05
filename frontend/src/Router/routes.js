import Homepage from "@/components/Home/Homepage.vue";
import ProfilePage from "@/components/Home/Profilepage.vue";
import ARegistrationForms from "@/components/Home/A_RegistrationForm.vue";
import ORegistrationForms from "@/components/Home/O_RegistrationForm.vue";
import LoginForm from "@/components/Home/LoginForm.vue";
import Trainingpage from "@/components/Home/Trainingpage.vue";
import Careerpage from "@/components/Home/Careerpage.vue";
import Organizationpage from "@/components/Home/Organizationpage.vue";
import Certificatespage from "@/components/Home/Certificatespage.vue";
import UpdateDeletepage from "@/components/Home/UpdateDeletepage.vue";
import ResumeEditorpage from "@/components/Home/ResumeEditorpage.vue";
import TypeOfAccount from "@/components/Home/TypeOfAccount.vue";
import OrgHomePage from "@/components/Organization/OrganizationHomepage.vue";
import OrgTraining from "@/components/Organization/OrganizationTrainings.vue";
import OrgCareer from "@/components/Organization/OrganizationCareers.vue";
import MainLayout from "@/components/Layout/MainLayout.vue";
import AuthLayout from "@/components/Layout/AuthLayout.vue";
import OrgCalendar from "@/components/Organization/OrganizationCalendar.vue";
import OrgProfile from "@/components/Organization/Profile.vue";
import OrgUpdateProfile from "@/components/Organization/OrganizationUpdateProfile.vue";
import OrgChangePassword from "@/components/Organization/OrganizationChangePassword.vue";
import AdminHomePage from "@/components/Admin/AdminHomePage.vue";
import AttendanceCheckin from "@/components/Home/AttendanceChecking.vue";
import AdminUpdateDelete from "@/components/Admin/AdminUpdateDelete.vue";
import AdminLayout from "@/components/Layout/AdminLayout.vue";
import AdminApplicantsPage from "@/components/Admin/AdminApplicantsPage.vue";
import AdminRejected from "@/components/Admin/AdminRejected.vue";
import AdminLogin from "@/components/Admin/AdminLogin.vue";
import ResetPassword from "@/components/Home/ResetPassword.vue";
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
    // ✅ Attendance scan route
    {
      path: "/attendance/checkin",
      name: "AttendanceCheckin",
      component: AttendanceCheckin,
    },

    // Auth pages
    {
      path: "/auth",
      component: AuthLayout,
      children: [
        { path: "login", name: "Login", component: LoginForm },
        {
          path: "register",
          alias: "/typeofaccount", // 👈 Now /typeofaccount works too
          name: "Register",
          component: TypeOfAccount,
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
    // Password Reset (outside auth layout)
    {
      path: "/reset-password",
      name: "ResetPassword",
      component: ResetPassword,
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

    {
      path: "/organization",
      name: "OrgHome",
      component: OrgHomePage,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/organization/org-trainings",
      name: "OrgTrainings",
      component: OrgTraining,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/organization/org-careers",
      name: "OrgCareers",
      component: OrgCareer,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/organization/org-calendar",
      name: "OrgCalendar",
      component: OrgCalendar,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/organization/org-profile",
      name: "OrgProfile",
      component: OrgProfile,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/organization/org-updateprofile",
      name: "OrgUpdateProfile",
      component: OrgUpdateProfile,
      meta: { requiresAuth: true, role: "organization" },
    },
    {
      path: "/organization/org-changepassword",
      name: "OrgChangePassword",
      component: OrgChangePassword,
      meta: { requiresAuth: true, role: "organization" },
    },

    // Admin Home Page - Public Access
    {
      path: "/admin",
      component: AdminLayout,
      children: [
        {
          path: "", // /admin
          name: "AdminLogin",
          component: AdminLogin, // always show login first
          meta: { requiresAuth: false }, // no auth needed here
        },
        {
          path: "dashboard",
          name: "AdminHomePage",
          component: AdminHomePage,
          meta: { requiresAuth: true, role: "admin" }, // only for logged-in admin
        },
        {
          path: "adminupdatedelete",
          name: "AdminUpdateDelete",
          component: AdminUpdateDelete,
          meta: { requiresAuth: true, role: "admin" },
        },
        {
          path: "applicantslist",
          name: "AdminApplicantsPage",
          component: AdminApplicantsPage,
          meta: { requiresAuth: true, role: "admin" },
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

  // Block access if route requires auth and no user
  if (to.meta.requiresAuth && (!user || (to.meta.role && user.role !== to.meta.role))) {
    if (to.meta.role === "admin") {
      return next({ name: "AdminLogin" }); // redirect to admin login
    } else {
      return next({ name: "Login" }); // normal user login
    }
  }

  // Prevent logged-in users from accessing login/register routes
  if (user && to.path.startsWith("/auth")) {
    if (user.role === "organization") return next({ name: "OrgHome" });
    if (user.role === "admin") return next({ name: "AdminHomePage" });
    return next({ name: "Homepage" });
  }

  // Prevent logged-in admin from accessing /admin (login) again
  if (user && user.role === "admin" && to.name === "AdminLogin") {
    return next({ name: "AdminHomePage" });
  }

  next();
});

export default router;
