<script setup>
import { useRoute } from "vue-router";
import Header from "./components/Layout/Header2.vue";
import Footer from "./components/Layout/Footer.vue";

const route = useRoute();

const noHeaderRoutes = [
  "/loginform",
  "/typeofaccount",
  "/a_registrationform",
  "/o_registrationform",
  "/OrganizationHomePage",
  "/OrgTrainings",
  "/OrgCareers",
  "/updateprofile",
  "/OrgCalendar",
];
</script>

<template>
  <div class="min-h-screen flex flex-col justify-between">
    <Header v-if="!noHeaderRoutes.includes(route.path)" />
    <main
      class="flex-grow"
      v-if="
        route.path !== '/homepage' &&
        route.path !== '/careerpage' &&
        route.path !== '/trainingpage' &&
        route.path !== '/organizationpage' &&
        route.path !== '/calendarpage' &&
        route.path !== '/bookmarkpage'
      "
    >
      <router-view></router-view>
    </main>
    <Footer />
  </div>
</template>
