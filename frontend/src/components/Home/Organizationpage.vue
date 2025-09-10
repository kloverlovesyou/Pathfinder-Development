<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const organizations = ref([]);

onMounted(async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/organization");
    organizations.value = res.data;
  } catch (error) {
    console.error("Error fetching organizations:", error);
  }
});
</script>

<template>
  <main>
<<<<<<< HEAD
    <div class="flex items-center gap-4 mb-4">
      <h2 class="text-2xl font-bold -m-2">Organizations</h2>
    </div>
    <div class="flex flex-col gap-8">
      <div class="flex flex-wrap -mx-2">
        <div class="sm:w-1/2 p-2">
          <div
            class="flex flex-col items-center p-4 bg-white rounded-lg shadow-md"
          >
            <div class="profile-picture">
              <img
                v-if="profileImageUrl"
                :src="profileImageUrl"
                alt="User profile picture"
                class="rounded-full w-20 h-20 object-cover"
=======
    <h2 class="text-2xl font-bold mb-4">Organizations</h2>

    <div class="flex flex-wrap -mx-2">
      <div
        v-for="org in organizations"
        :key="org.organizationID"
        class="sm:w-1/2 p-2"
      >
        <div
          class="flex flex-col items-center p-4 bg-white rounded-lg shadow-md"
        >
          <!-- Profile Picture / Logo -->
          <div class="profile-picture">
            <img
              v-if="org.profileImage"
              :src="org.profileImage"
              alt="Organization Logo"
              class="rounded-full w-20 h-20 object-cover"
            />
            <svg
              v-else
              class="rounded-full w-20 h-20 text-gray-400"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M9 2C6.38 2 4.25 4.13 4.25 6.75C4.25 9.32 6.26 11.4 8.88 11.49C8.96 11.48 9.04 11.48 9.1 11.49C9.12 11.49 9.13 11.49 9.15 11.49C9.16 11.49 9.16 11.49 9.17 11.49C11.73 11.4 13.74 9.32 13.75 6.75C13.75 4.13 11.62 2 9 2Z"
                fill="#292D32"
>>>>>>> b2a439d34175e3ec43a02f1942dd83fc24700835
              />
              <path
                d="M14.08 14.15C11.29 12.29 6.73996 12.29 3.92996 14.15C2.65996 15 1.95996 16.15 1.95996 17.38C1.95996 18.61 2.65996 19.75 3.91996 20.59C5.31996 21.53 7.15996 22 8.99996 22C10.84 22 12.68 21.53 14.08 20.59C15.34 19.74 16.04 18.6 16.04 17.36C16.03 16.13 15.34 14.99 14.08 14.15Z"
                fill="#292D32"
              />
            </svg>
          </div>

          <!-- Org Name -->
          <span class="text-lg font-semibold text-gray-800">
            {{ org.name }}
          </span>
        </div>
      </div>
    </div>
  </main>
</template>