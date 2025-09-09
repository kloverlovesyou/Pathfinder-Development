<script setup>
import { useRouter } from "vue-router";
import { ref, onMounted } from "vue";
import axios from "axios";

const router = useRouter();
const trainings = ref([
  {
    id: 1,
    type: "training",
    title: "Professional development in emerging technologies",
    schedule: "November 24, 2025 | 1:30 PM - 5:00 PM",
  },
  {
    id: 2,
    type: "training",
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "September 20, 2025 | 7:30 PM - 12:00 PM",
  },
]);

onMounted(async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/trainings");
    trainings.value = response.data;
  } catch (error) {
    console.error("Error fetching trainings:", error);
  }
});
</script>

<template>
  <main>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-4">
      <h2 class="text-2xl font-bold">Training</h2>
    </div>

    <!-- Training Cards -->
    <div class="space-y-4">
      <div
        v-for="training in trainings"
        :key="training.id"
        class="p-4 bg-blue-gray rounded-lg relative hover:bg-gray-300 transition"
      >
        <!-- Dropdown absolute top-right -->
        <div class="absolute top-2 right-2 z-50">
          <div class="dropdown dropdown-end">
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
              class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-md"
            >
              <li><a>Bookmark</a></li>
              <li><a>Register</a></li>
            </ul>
          </div>
        </div>

        <!-- Router link only around the content -->
        <router-link
          :to="{ name: 'TrainingDetails', params: { id: training.id } }"
          class="block"
        >
          <h3 class="font-semibold text-lg">{{ training.title }}</h3>
          <p class="text-gray-600 text-sm">Schedule: {{ training.schedule }}</p>
        </router-link>
      </div>
    </div>
  </main>
</template>
