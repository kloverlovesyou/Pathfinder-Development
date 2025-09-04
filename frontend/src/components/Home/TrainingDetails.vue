<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();
const training = ref({
  id: 2,
  title: "Mind Over Machine: Navigating AI in Everyday Life",
  schedule: "2025-09-20 19:30:00",
  description:
    "This training helps participants understand AI in daily life...",
  organization: {
    id: 1,
    name: "Tech Academy",
  },
});
const loading = ref(true);

onMounted(async () => {
  try {
    const id = route.params.id;
    const response = await axios.get(
      `http://127.0.0.1:8000/api/trainings/${id}`
    );
    training.value = response.data;
  } catch (error) {
    console.error("Error fetching training:", error);
  } finally {
    loading.value = false;
  }
});

const goBack = () => {
  router.push("/trainingpage"); // or the combined page if you want
};

// sample function for register button
const registerTraining = () => {
  alert(`Registered for: ${training.value.title}`);
};
</script>

<template>
  <main class="p-6 font-poppins">
    <button class="group" @click="goBack">
      <!-- Back Arrow -->
      <svg
        class="size-6"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M9.57 5.93005L3.5 12.0001L9.57 18.0701"
          stroke="#6682A3"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M20.5 12H3.66998"
          stroke="#6682A3"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </button>

    <div v-if="loading">Loading...</div>

    <div
      v-else-if="training"
      class="bg-white rounded-xl shadow-lg p-6 relative"
    >
      <!-- Top Row: Org Image, Name & Dropdown -->
      <div class="flex justify-between items-start mb-4">
        <div class="flex items-center gap-3">
          <img
            :src="
              training.organization?.image || 'https://via.placeholder.com/50'
            "
            alt="Org Logo"
            class="w-10 h-10 rounded-full object-cover"
          />
          <p class="font-medium text-gray-800">
            {{ training.organization?.name || "Unknown Organization" }}
          </p>
        </div>

        <!-- Ellipsis Dropdown -->
        <div class="dropdown dropdown-left lg-hidden block">
          <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path
                d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
              />
            </svg>
          </div>
          <ul
            tabindex="0"
            class="dropdown-content menu bg-base-100 rounded-box z-50 w-40 p-2 shadow-md mt-0"
          >
            <li><a>Bookmark</a></li>
            <a>Apply</a>
          </ul>
        </div>
      </div>

      <!-- Training Title -->
      <h1 class="text-2xl font-bold mb-2">{{ training.title }}</h1>

      <!-- Schedule -->
      <p class="text-gray-600 mb-2">📅 {{ training.schedule }}</p>

      <!-- Register Button -->
      <button @click="registerTraining" class="btn btn-primary mb-4">
        Register
      </button>

      <!-- Description -->
      <p class="text-gray-700 leading-relaxed mb-4">
        {{ training.description }}
      </p>
    </div>

    <div v-else>
      <p class="text-red-500">Training not found.</p>
    </div>
  </main>
</template>
