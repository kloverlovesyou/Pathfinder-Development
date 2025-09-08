<script setup>
import { useRoute } from "vue-router";
import Header from "./components/Layout/Header2.vue";
import Footer from "./components/Layout/Footer.vue";

const route = useRoute();
</script>

<template>
  <div class="min-h-screen flex flex-col justify-between">
    <!-- Header -->
    <Header
      v-if="
        route.path !== '/loginform' &&
        route.path !== '/typeofaccount' &&
        route.path !== '/a_registrationform' &&
        route.path !== '/o_registrationform' &&
        route.path == '/OrganizationHomePage' &&
        route.path !== '/OrgTrainings' &&
        route.path !== '/OrgCareers' &&
        route.path == '/updateprofile' 
      "
    />

    <!-- Main content -->
    <main
      class="flex-grow"
      v-if="
        route.path !== '/homepage' &&
        route.path !== '/trainingpage' &&
        route.path !== '/careerpage' &&
        route.path !== '/organizationpage' &&
        route.path !== '/bookmarkpage' &&
        route.path !== '/calendarpage'
      " 
    >
      <router-view></router-view>
    </main>

    <!-- Footer -->
    <Footer
      v-if="
        route.path !== '/loginform' &&
        route.path !== '/typeofaccount' &&
        route.path !== '/a_registrationform' &&
        route.path !== '/o_registrationform'
      "
    />
  </div>
</template>
